<?php declare( strict_types = 1 );
if ( !defined( 'PATH' ) ) { die(); }

// WORK IN PROGRESS - Do not use

/**
 *  Bare Moderation: This is a user submitted content filter plugin
 *  
 *  Used for commenting, registration, and other user input siutations 
 *  
 *  This is not a standalone plugin
 */

define( 'MODERATION_DATA',		CACHE . 'moderation.db' );


define( 'MODERATION_SQL',		<<<SQL

-- Content and access filters
CREATE TABLE filters(
	id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
	term TEXT NOT NULL COLLATE NOCASE,
	label TEXT NOT NULL COLLATE NOCASE,
	
	-- Filter action
	response INTEGER NOT NULL DEFAULT 0,
	created TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	duration INTEGER DEFAULT 0,
	expires DATETIME DEFAULT NULL
);-- --

CREATE UNIQUE INDEX idx_filter_term ON filters( term, label );-- --
CREATE INDEX idx_filter_label ON filters( label );-- --
CREATE INDEX idx_filter_response ON filters( response );-- --
CREATE INDEX idx_filter_expires ON filters( expires );-- --

-- Filter searching
CREATE VIRTUAL TABLE filter_search 
	USING fts4( term, tokenize=unicode61 );-- --

CREATE TRIGGER filter_insert AFTER INSERT ON filters FOR EACH ROW 
BEGIN
	-- Create search data
	INSERT INTO filter_search( docid, term ) 
		VALUES ( NEW.id, NEW.term );
	
END;-- --

-- Calculate expiration if duration was set
CREATE TRIGGER filter_expiration AFTER INSERT ON filters FOR EACH ROW
WHEN NEW.duration > 0 
BEGIN 
	-- Generate expiration
	UPDATE filters SET 
		expires = datetime( 
			( strftime( '%s','now' ) + NEW.duration ), 
			'unixepoch' 
		) WHERE rowid = NEW.rowid;
	
	-- Clear expired filters
	DELETE FROM filters WHERE 
		strftime( '%s', expires ) < 
		strftime( '%s', created );
END;-- --

-- Change expiration period when duration was modified
CREATE TRIGGER filter_duration AFTER UPDATE ON filters FOR EACH ROW 
WHEN NEW.duration <> 0
BEGIN
	-- Change expiration
	UPDATE filters 
		SET expires = datetime( 
			( strftime( '%s','now' ) + NEW.duration ), 
			'unixepoch' 
		) WHERE rowid = NEW.rowid;
END;-- --

CREATE TRIGGER filter_update AFTER UPDATE ON users FOR EACH ROW
BEGIN
	UPDATE filter_search SET term = NEW.term WHERE docid = OLD.id;
END;-- --

CREATE TRIGGER filter_delete BEFORE DELETE ON filters FOR EACH ROW 
BEGIN
	DELETE FROM filter_search WHERE rowid = OLD.rowid;
END;

SQL
);


/**
 *  Filter behaviors
 *  Note: "user" in this context can be author names, login usernames or similar
 */
define( 'FILTER_HOLD',			0 );	// Hold for review
define( 'FILTER_DELETE',		1 );	// Delete outright					
define( 'FILTER_HOLDSUSP',		2 );	// Hold, suspend user
define( 'FILTER_DELSUSP',		3 );	// Delete, suspend user
define( 'FILTER_HOLDSUSPIP',		4 );	// Hold, suspend IP
define( 'FILTER_DELSUSPIP',		5 );	// Delete, suspend IP
define( 'FILTER_HOLDSUSPUIP',		6 );	// Hold, suspend user, suspend IP
define( 'FILTER_DELSUSPUIP',		7 );	// Delete, suspend user, suspend IP
define( 'FILTER_HOLDBLOCK',		8 );	// Hold, block user
define( 'FILTER_DELBLOCK',		9 );	// Delete, block user
define( 'FILTER_HOLDBLOCKIP',		10 );	// Hold, block IP
define( 'FILTER_DELBLOCKIP',		11 );	// Delete, block IP
define( 'FILTER_HOLDBLOCKUIP',		12 );	// Hold, block user, block IP
define( 'FILTER_DELBLOCKUIP',		13 );	// Delete, block user, block IP


// Measurement precision (E.G. for quality checking );
define( 'MOD_Q_PRECISION',			4 );


/**
 *  See if the response calls for a block
 *  
 *  @param int		$response	Filter reseponse as given
 *  @return bool Defaults to false
 */
function blockedResponse( int $response ) : bool {
	switch ( $response ) {
		// Hold, suspend user
		case \FILTER_HOLDSUSP:
		// Delete, suspend user
		case \FILTER_DELSUSP:
		// Hold, suspend IP
		case \FILTER_HOLDSUSPIP:
		// Delete, suspend IP
		case \FILTER_DELSUSPIP:
		// Hold, suspend user, suspend IP
		case \FILTER_HOLDSUSPUIP:
		// Delete, suspend user, suspend IP
		case \FILTER_DELSUSPUIP:
		// Hold, block user
		case \FILTER_HOLDBLOCK:
		// Delete, block user
		case \FILTER_DELBLOCK:
		// Hold, block IP
		case \FILTER_HOLDBLOCKIP:
		// Delete, block IP
		case \FILTER_DELBLOCKIP:
		// Hold, block user, block IP
		case \FILTER_HOLDBLOCKUIP:
		// Delete, block user, block IP
		case \FILTER_DELBLOCKUIP:
			return true;
	}
	
	return false;
}

/**
 *  IP address
 */

/**
 *  Address to bit conversion helper
 */
function inetbits( $ip ) {
	$ip	= \inet_pton( $ip );
	$up	= \unpack( 'A16', $ip );
	$up	= \str_split( $up[1] );
	$bn	= '';
	
	foreach ( $up as $char ) {
		$bn .= 
		\str_pad( 
			\decbin( \ord( $char ) ), 8, '0', \STR_PAD_LEFT 
		);
	}
	return $bn;
}

/**
 *  Check if an IPv4 address exists in range
 */
function ip4cidr( $ip, $range ) : bool {
	list ( $subnet, $bits )	= \explode( '/', $range );
	$ip			= \ip2long($ip);
	$subnet			= \ip2long($subnet);
	$mask			= -1 << ( 32 - $bits );
	
	$subnet			&= $mask;
	return	( $ip & $mask )	== $subnet;
}

/**
 *  Check if an IPv6 address exists in range
 */
function ip6cidr( $ip, $range ) : bool {
	list ( $subnet, $bits )	= \explode( '/', $range );
	$bn	= inetbits( $ip );
	$net	= inetbits( $subnet );
	
	$ib	= \substr( $bn, 0, $bits );
	$nb	= \substr( $net, 0, $bits );
	
	return ( $ib === $nb );
}

/**
 *  Simple text evaluation for small blocks of text using only syntax and not substance or language
 *  
 *  @param string	$text		Raw text input
 *  @return array
 */
function modEvaluate( string $text ) : array {
	if ( empty( trim( $text ) ) ) {
		return [];
	}
	
	// Filtered text
	$bn = bland( $text );
	
	// Filtered text without special unicode characters
	$ns = bland( $text, true );
	
	// Total word count
	$wc = wordcount( $bn );
	
	// Total unique terms
	$uq = uniqueTerms( $bn );
	
	// Total text size
	$bns = strsize( $bn );
	
	// Total characters
	$tsc = \preg_split( '//', $bn, -1, \PREG_SPLIT_NO_EMPTY );
	$tcc = count( $tsc );
	
	// Uppercase character count
	$uss = count( 
		// Uppercase chars only
		\array_diff( 
			// Total
			$tsc,
			
			// Lowercase characters
			\preg_split( '//', lowercase( $bn ), -1, \PREG_SPLIT_NO_EMPTY )
		) 
	);
	
	// Punctuation
	$pnn = \preg_match_all( '/[[:punct:]]/', $bn );
	
	return [
		// Total word count
		'wordcount'	=> $wc,
		
		// Contains only ASCII?
		'isascii'	=> isASCII( $bn ),
		
		// Original text to bland text size ratio
		'texttobland'	=> division( strsize( $text ), $bns ),
		
		// Text size to text without tags ratio
		'texttonotags'	=> division( $bns, strsize( \strip_tags( $text ) ) ),
		
		// Text to text without special characters or diacritics ratio
		'texttotextns'	=> division( $bns, strsize( $ns ) ),
		
		// Total character count to uppercase character count ratio
		'totaltoupper'	=> division( $tcc, $uss ),
		
		// Total word count to unique terms ratio
		'termstounique'	=> division( $wc, count( $uq ) ),
		
		// Total character count to punctuation ratio
		'totaltopunct'	=> $pnn ? division( $tcc, $pnn ) : 0
		
	];
}

/**
 *  Add a filter action if it doesn't already exist
 *  
 *  @param string	$term		Filter search term
 *  @param string	$label		Filter type
 *  @param int		$response	Action response to filter match
 *  @param int		$duration	Duration in seconds or 0 for no limit
 *  @return int
 */
function addFilter(
	string		$term,
	string		$label,
	int		$response,
	int		$duration	= -1
) : int {
	return
	setInsert( 
		"INSERT OR IGNORE INTO filters ( 
			term, label, response, duration
		) VALUES ( :term, :label, :response, :duration );",
		[ 
			':term'		=> $term, 
			':label'	=> $label,
			
			// Limit to available responses
			':response'	=> intRange( $response, 0, 13 ),
			':duration'	=> $duration
		], 
		\MODERATION_DATA 
	);
}

/**
 *  @brief Brief description
 *  
 *  @param int		$id		Filter identifier
 *  @param string	$term		Filter search term
 *  @param string	$label		Filter type
 *  @param int		$response	Action response to filter match
 *  @param int		$duration	Duration in seconds or 0 for no limit
 *  @return bool True on success
 */
function updateFilter(
	int		$id,
	string		$term,
	string		$label,
	int		$response,
	int		$duration	= -1
) : bool {
	return 
	setUpdate(
		"UPDATE filters SET 
			term = :term, label = :label, response = :response, 
			duration = :duration WHERE id = :id;"
		[ 
			':term'		=> $term, 
			':label'	=> $label,
			':response'	=> intRange( $response, 0, 13 ),
			':duration'	=> $duration,
			':id'		=> $id
		], \MODERATION_DATA );
	);
}

/**
 *  Find a relative matching text within a larger body such as paragraphs
 *  
 *  @param string	$term		Search phrase
 *  @param string	$label		Filter type
 *  @return array
 */
function containsFilter( string $term, string $label ) : array {
	$sql		= 
	"SELECT f.id AS id, 
		f.term AS term, 
		f.response AS response, 
		f.duration AS duration, 
		f.created AS created 
			FROM filters f 
			JOIN filter_search s ON f.id = s.rowid 
				WHERE s.term MATCH :term AND f.label = :label;";
	return 
	getResults( $sql, [ 
		':term'		=> $term,
		':label'	=> $label
	], \MODERATION_DATA );
}

/**
 *  Find exact matching text such as for usernames or IP addresses
 *  
 *  @param string	$term		Search phrase
 *  @param string	$label		Filter type
 *  @return array
 */
function exactFilter( string $term, string $label ) : array {
	$sql		= 
	"SELECT f.id AS id, 
		f.term AS term, 
		f.response AS response, 
		f.duration AS duration, 
		f.created AS created 
			FROM filters WHERE term = :term AND label = :label;";
	
	return 
	getResults( $sql, [ 
		':term'		=> $term,
		':label'	=> $label
	], \MODERATION_DATA );
}



