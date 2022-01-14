<?php declare( strict_types = 1 );
if ( !defined( 'PATH' ) ) { die(); }
/**
 *  Bare MonsterID: This is an avatar generator plugin based on the 
 *  original monsterID avatar generator and an updated version.
 *  This is not a standalone plugin
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

// Preset RGB background color
define( 'MONSTER_BG_COLOR',	'255, 255, 255' );

// Use a random background color (ignores above setting)
define( 'MONSTER_RANDOM_BG',	0 );

/**
 *  Enabling the following has performance implications
 */

// Enable building Monster ID
define( 'MONSTER_URL_GEN',	0 );

// Enable session based generation
define( 'MONSTER_SESSION_GEN',	0 );

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
 *  Generate the monsterID filename based on seed and size
 *  
 *  @param mixed	$seed		Random initialization data
 *  @param int		$size		Square avatar size
 *  @param bool		$create		Create source folder if true
 */
function monsterPath( $seed, int $size, bool $create = false ) : string {
	$ha	= hashAlgo( 'monster_algo', \MONSTER_ALGO );
	$fname	= \hash( $ha, ( string ) $size . $seed ) . '.png';
	
	return 
	pluginWritePath( 'monsterid', $fname, 'm_', $create, false );
}

/**
 *  Create a random color
 *  
 *  @param GDImage	$img	True color image
 *  @param int		$minC	Minimum single color value
 *  @param int		$maxC	Maximum single color value
 */
function randomMonsterColor( &$img, $minC, $maxC ) {
	return
	\imagecolorallocate( 
		$img, 
		\mt_rand( $minC, $maxC ), 
		\mt_rand( $minC, $maxC ), 
		\mt_rand( $minC, $maxC ) 
	);
}

/**
 *  Create list of monster part files
 *  
 *  @param mixed	$seed	Random initialization data
 *  @return array
 */
function monsterParts( $seed ) : array {
	// Seed the random number generator
	$ha	= hashAlgo( 'monster_algo', \MONSTER_ALGO );
	$h	= \hexdec( \substr( \hash( $ha, $seed ), 0, 6 ) );
	\mt_srand( $h, \MT_RAND_MT19937 );
	
	// Parts directory
	$pdir	= 
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
	
	// Part files check
	$files	= [];
	foreach ( $parts as $part => $num ) {
		$f = $pdir . $part . '_' . $num . '.png';
		if ( !\file_exists( $f ) ) {
			logError( 'Missing monster part: ' . $f );
			continue;
		}
		$files[$part] = $f;
	}
	
	// Reset seed
	\mt_srand();
	return $files;
}

/**
 *  Create MonsterID
 *  
 *  @param mixed	$seed	Random initialization data
 *  @param int		$size	Square avatar size
 *  @param bool		$send	Send to visitor after generating
 */
function buildMonster( $seed, int $size, bool $send = false ) {
	
	// Find part files
	$parts = monsterParts( $seed );
	
	// Defaults
	$smax	= config( 'monster_id_max', \MONSTER_ID_MAX, 'int' );
	$smin	= config( 'monster_id_min', \MONSTER_ID_MIN, 'int' );
	$size	= intRange( $size, $smin, $smax );
	
	// RGB selection range
	$rgbmin	= config( 'monster_rgb_min', \MONSTER_RGB_MIN, 'int' );
	$rgbmax	= config( 'monster_rgb_max', \MONSTER_RGB_MAX, 'int' );
	
	// Center
	$pos	= intRange( ceil( $smax / 2 ), 1, MONSTER_ID_MAX );
	
	// Monster background
	$monster	= \imagecreatetruecolor( $smax, $smax );
	
	// Set background to preset value or random color
	if ( config( 'monster_random_bg', \MONSTER_RANDOM_BG, 'bool' ) ) {
		$bg = randomMonsterColor( $monster, $rgbmin, $rgbmax );
	} else {
		$br = trimmedList( config( 'monster_bg_color', \MONSTER_BG_COLOR ) );
		$bg = \imagecolorallocate( $monster, $bgr[0], $bgr[1], $bgr[2] );
	}
	
	// Blank monster with set background
	\imagefill( $monster, 0, 0, $bg );
	
	// Add monster parts
	foreach ( $parts as $part => $file ) {
		$im	=  \imagecreatefrompng( $file );
		if ( !$im ) {
			logError( 'Failed to load ' . $file );
			\imagedestroy( $monster );
			cleanOutput( true );
			if ( $send ) {
				sendNotFound();
			}
		};
		
		\imageSaveAlpha( $im, true );
		\imagecopy( $monster, $im, 0, 0, 0, 0, $smax, $smax );
		\imagedestroy( $im );

		// Special case: body colors
		if ( 0 == \strcmp( $part, 'body' ) ){
			$color = 
			randomMonsterColor( $monster, $rgbmin, $rgbmax );
			
			\imagefill( $monster, $pos, $pos, $color );
		}
	}
	
	// Generated monster path
	$fpath	= monsterPath( $seed, $size, true );
	
	// Clear buffer and start fresh
	cleanOutput( true );
	\ob_start();
	
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
	
	// Save
	$img	= \ob_get_contents();
	
	if ( false === $img ) {
		cleanOutput( true );
		\imagedestroy( $monster );
		logError( 'Error creating Monster ID' );
		sendNotFound();
	} else {
		// Cache the monster
		\file_put_contents( $fpath, $img );
	}
	
	cleanOutput( true );
	\imagedestroy( $monster );
	if ( $send ) {
		if ( sendFile( $fpath ) ) {
			shutdown( 'cleanup' );
			shutdown();
		}
		sendNotFound();
	}
}

/**
 *  Create a session based random seed
 *  
 *  @return string
 */
function monsterSession() : string {
	sessionCheck();
	if ( empty( $_SESSION['monsterid'] ) ) {
		$_SESSION['monsterid'] = genId();
	}
	
	internalState( 'monsterSession', true );
	return $_SESSION['monsterid'];
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
	$murl	= config( 'monster_url_gen', \MONSTER_URL_GEN, 'bool' );
	$mses	= config( 'monster_session_gen', \MONSTER_SESSION_GEN, 'bool' );
	
	if  ( empty( $seed ) ) {
		// No session based generation?
		if ( !$mses ) {
			sendNotFound();
		}
		$seed = monsterSession();
	}
	
	// Defaults
	$size	= ( int ) $params['page'] ?? 0;
	$smin	= config( 'monster_id_min', \MONSTER_ID_MIN, 'int' );
	$smax	= config( 'monster_id_max', \MONSTER_ID_MAX, 'int' );
	$size	= intRange( $size, $smin, $smax );
	
	// Try to send cached monster if it exists
	if ( sendFile( monsterPath( $seed, $size, false ) ) ) {
		shutdown( 'cleanup' );
		shutdown();
	}
	
	// No URL generation enabled?
	if ( !$murl ) {
		sendNotFound();
	}
	
	// Send to builder
	buildMonster( $seed, $size, true );
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
		],
		'monster_bg_color'	=> [
			'filter'	=> 
				\FILTER_SANITIZE_SPECIAL_CHARS,
			'flags'	=> 
				\FILTER_FLAG_STRIP_LOW	| 
				\FILTER_FLAG_STRIP_HIGH	| 
				\FILTER_FLAG_STRIP_BACKTICK 
		],
		'monster_random_bg'	=> [
			'filter'	=> \FILTER_VALIDATE_INT,
			'options'	=> [
				'min_range'	=> 0,
				'max_range'	=> 1,
				'default'	=> \MONSTER_RANDOM_BG
			]
		],
		'monster_url_gen'	=> [
			'filter'	=> \FILTER_VALIDATE_INT,
			'options'	=> [
				'min_range'	=> 0,
				'max_range'	=> 1,
				'default'	=> \MONSTER_URL_GEN
			]
		],
		'monster_session_gen'	=> [
			'filter'	=> \FILTER_VALIDATE_INT,
			'options'	=> [
				'min_range'	=> 0,
				'max_range'	=> 1,
				'default'	=> \MONSTER_SESSION_GEN
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
	
	// Set bg RGB values
	if ( isset( $data['monster_bg_color'] ) ) {
		$bg = trimmedList( $data['monster_bg_color'] );
		if ( count( $bg ) != 3 ) {
			$bg = trimmedList( \MONSTER_BG_COLOR );
		}
		
		$s = [];
		foreach ( $bg as $c ) {
			$s[] = intRange( $c, 0, 255 );
		}
		
		$data['monster_bg_color'] = $s;
	}
	
	return \array_merge( $hook, $data );
}

/**
 *  Append MonsterID route
 */
function addMonsterIDRoutes( string $event, array $hook, array $params ) {
	return 
	\array_merge( $hook, [
		[ 'get', 'monsterid',			'showMonsterID' ],
		[ 'get', 'monsterid/:slug',		'showMonsterID' ],
		[ 'get', 'monsterid/:slug/:page',	'showMonsterID' ]
	] );
}


// Register events

// Initialize configuration
hook( [ 'pluginsLoaded',	'monsterGDCheck' ] );
hook( [ 'checkconfig',		'checkMonsterIDConfig' ] );
hook( [ 'initroutes',		'addMonsterIDRoutes' ] );


