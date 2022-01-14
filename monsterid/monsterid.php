<?php declare( strict_types = 1 );
if ( !defined( 'PATH' ) ) { die(); }
/**
 *  Bare MonsterID: This is an avatar generator plugin based on the original
 *  monsterID avatar generator and an updated version
 *  
 *  @link https://github.com/splitbrain/monsterID
 *  @link https://github.com/cypnk/monsterID
*/

/**
 *  The following settings are also configurable in config.json
 */

// Monster body parts location relative to the 'monsterid' plugin directory
define( 'MONSTER_PARTS',	'parts/' );

// Minimum avatar size
define( 'MONSTER_ID_MIN',	16 );

// Maximum avatar size
define( 'MONSTER_ID_MAX',	120 );

// Seed hash algorithm
define( 'MONSTER_ALGO',		'sha256' );

// Darkest random value for RGB color selector
define( 'MONSTER_RGB_MIN',	20 );

// Lightest random value
define( 'MONSTER_RGB_MAX',	235 );

/**
 *  Caution editing below
 */

/**
 *  Check GD environment
 */
function monsterGDCheck( string $event, array $hook, array $params ) {
	// Check for access to required functions
	$req	= [
		'imagecreatetruecolor',
		'imagecopyresampled',
		'imagecolorallocate',
		'imagecreatefrompng',
		'imageSaveAlpha',
		'imagedestroy',
		'imagecopy',
		'imagefill',
		'imagepng',
	];
	
	$miss	= [];
	foreach ( $req as $f => $name ) {
		if ( missing( $name ) ) {
			$miss[] = $name;
		}
	}
	
	// GD requirements failed?
	if ( !empty( $miss ) ) {
		logError( 
			'Following GD function(s) required: ' . 
			\implode( ', ', $miss ) 
		);
		internalState( 'monsterGDfail', true );
	}
}

/**
 *  Create MonsterID
 *  
 *  @param mixed	$seed		Random initialization data
 *  @param int		$size		Square avatar size
 */
function buildMonster( $seed, int $size ) {
	// Seed the random number generator
	$ha	= hashAlgo( 'monster_algo', \MONSTER_ALGO );
	$h	= \hexdec( \substr( \hash( $ha, $seed ), 0, 6 ) );
	\mt_srand( $h, \MT_RAND_MT19937 );
	
	// Parts directory
	$pdir		= 
		slashPath( \PLUGINS, true ) . 'monsterid/' . 
		slashPath( \MONSTER_PARTS, true );
	
	// Random monster body parts
	$parts = [
		'legs'	=> \mt_rand( 1, 5 ),
		'hair'	=> \mt_rand( 1, 5 ),
		'arms'	=> \mt_rand( 1, 5 ),
		'body'	=> \mt_rand( 1, 15 ),
		'eyes'	=> \mt_rand( 1, 15 ),
		'mouth'	=> \mt_rand( 1, 10 )
	];
	
	cleanOutput( true );
	
	// Max monster ID size
	$smax	= config( 'monster_id_max', \MONSTER_ID_MAX, 'int' );
	
	// RGB selection range
	$rgbmin	= config( 'monster_rgb_min', \MONSTER_RGB_MIN, 'int' );
	$rgbmax	= config( 'monster_rgb_max', \MONSTER_RGB_MAX, 'int' );
	
	// Center
	$pos	= intRange( ceil( $smax / 2 ), 1, MONSTER_ID_MAX );
	
	// Monster backgound
	$monster	= \imagecreatetruecolor( $smax, $smax );
	$bg		= \imagecolorallocate( $monster, 255, 255, 255 );
	
	// Blank monster
	\imagefill( $monster, 0, 0, $bg );
	
	foreach( $parts as $part => $num ) {
		$file	= $pdir . $part . '_' . $num . '.png';
		$im	=  \imagecreatefrompng( $file );
		if( !$im ) {
			logError( 'Failed to load ' . $file );
			cleanOutput( true );
			sendNotFound();
		};
		
		\imageSaveAlpha( $im, true );
		\imagecopy( $monster, $im, 0, 0, 0, 0, $smax, $smax );
		\imagedestroy( $im );

		// Special case: body colors
		if ( $part == 'body' ){
			$color	= 
			\imagecolorallocate( 
				$monster, 
				\mt_rand( $rgbmin, $rgbmax ), 
				\mt_rand( $rgbmin, $rgbmax ), 
				\mt_rand( $rgbmin, $rgbmax ) 
			);
			\imagefill( $monster, $pos, $pos, $color );
		}
	}

	// Reset seed
	\mt_srand();
	
	// Prepare to send MonsterID
	shutdown( 'cleanup' );
	scrubOutput();
	httpCode( 200 );
	preamble( '', false, false );
	
	\header( "Content-type: image/png" );
	\header( "Content-Security-Policy: default-src 'self'", true );	
	
	// Adjust monster to given size if less than max
	if ( $size < $smax ){
		$out = \imagecreatetruecolor( $size, $size );
		\imagecopyresampled(
			$out,
			$monster, 0, 0, 0, 0, 
			$size, $size, $smax, $smax 
		);
		
		\imagepng( $out );
		\imagedestroy( $out );
	} else {
		\imagepng( $monster );
	}
	
	// Done
	flushOutput( true );
	\imagedestroy( $monster );
	shutdown();
}


/**
 *  Generate and show MonsterID
 */
function showMonsterID( string $event, array $hook, array $params ) {
	// Can't generate MonsterID without GD stuff
	if ( internalState( 'monsterGDfail' ) ) {
		sendNotFound();
	}
	
	$seed	= $params['slug'] ?? '';
	
	// Use current session canay if seed is empty
	if  ( empty( $seed ) ) {
		sessionCheck();
		$seed	= $_SESSION['canary']['visit'];
	}
	
	$size	= ( int ) $params['page'] ?? 0;
	$smin	= config( 'monster_id_min', \MONSTER_ID_MIN, 'int' );
	$smax	= config( 'monster_id_max', \MONSTER_ID_MAX, 'int' );
	
	// Send to builder
	buildMonster( $seed, intRange( $size, $smin, $smax ) );
}

/**
 *  Configuration check
 */
function checkMonsterIDConfig( string $event, array $hook, array $params ) {
	$filter = [
		'monster_id_min'	=> [
			'filter'	=> \FILTER_VALIDATE_INT,
			'options'	=> [
				'min_range'	=> MONSTER_ID_MIN,
				'max_range'	=> MONSTER_ID_MAX,
				'default'	=> MONSTER_ID_MIN
			]
		],
		'monster_id_max'	=> [
			'filter'	=> \FILTER_VALIDATE_INT,
			'options'	=> [
				'min_range'	=> MONSTER_ID_MIN,
				'max_range'	=> MONSTER_ID_MAX,
				'default'	=> MONSTER_ID_MAX
			]
		],
		'monster_algo'	=> [
			'filter'	=> 
				\FILTER_SANITIZE_SPECIAL_CHARS,
			'flags'	=> 
				\FILTER_FLAG_STRIP_LOW	| 
				\FILTER_FLAG_STRIP_HIGH	| 
				\FILTER_FLAG_STRIP_BACKTICK 
		],
		'monster_rgb_min'=> [
			'filter'	=> \FILTER_VALIDATE_INT,
			'options'	=> [
				'min_range'	=> 0,
				'max_range'	=> 255,
				'default'	=> 0
			]
		],
		'monster_rgb_max'=> [
			'filter'	=> \FILTER_VALIDATE_INT,
			'options'	=> [
				'min_range'	=> 0,
				'max_range'	=> 255,
				'default'	=> 255
			]
		]
	];
	
	$data	= \filter_var_array( $params, $filter, false );
	
	// Check available hash algos
	if ( isset( $data['monster_algo'] ) ) {
		$data['monster_algo']	= 
		hashAlgo( 
			( string ) $data['monster_algo'], 
			\MONSTER_ALGO 
		);
	}
	return \array_merge( $hook, $data );
}

/**
 *  Append MonsterID route
 */
function addMonsterIDRoutes( string $event, array $hook, array $params ) {
	return 
	\array_merge( $hook, [
		[ 'get', 'monsterid/:slug',		'showMonsterID' ],
		[ 'get', 'monsterid/:page/:slug',	'showMonsterID' ]
	] );
}


// Register events

// Initialize configuration
hook( [ 'pluginsLoaded',	'monsterGDCheck' ] );
hook( [ 'checkconfig',		'checkMonsterIDConfig' ] );
hook( [ 'initroutes',		'addMonsterIDRoutes' ] );


