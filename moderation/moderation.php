<?php declare( strict_types = 1 );
if ( !defined( 'PATH' ) ) { die(); }

// WORK IN PROGRESS - Do not use

/**
 *  Bare Moderation: This is a user submitted content filter plugin
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
	duration INTEGER DEFAULT 0
);-- --

CREATE UNIQUE INDEX idx_filter_term ON filters( term, label );-- --
CREATE INDEX idx_filter_label ON filters( label );-- --
CREATE INDEX idx_filter_response ON filters( response );-- --
CREATE INDEX idx_filter_duration ON filters( duration );-- --

-- Filter searching
CREATE VIRTUAL TABLE filter_search 
	USING fts4( term, tokenize=unicode61 );-- --


CREATE TRIGGER filter_insert AFTER INSERT ON filters FOR EACH ROW 
BEGIN
	-- Create search data
	INSERT INTO filter_search( docid, term ) 
		VALUES ( NEW.id, NEW.term );
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





