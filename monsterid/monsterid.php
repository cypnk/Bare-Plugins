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

// Preset RGB background color
define( 'MONSTER_BG_COLOR',	'255, 255, 255' );

// Use a random background color (ignores above setting)
define( 'MONSTER_RANDOM_BG',	0 );

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
			$color = 
			randomMonsterColor( $monster, $rgbmin, $rgbmax );
			
			\imagefill( $monster, $pos, $pos, $color );
		}
	}

	// Reset seed
	\mt_srand();
	
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
	cleanOutput( true );
	\imagedestroy( $monster );
	
	// Cache the monster
	$fpath	= monsterPath( $seed, $size, true );
	\file_put_contents( $fpath, $img );
	
	sendFile( $fpath );
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
	
	// Defaults
	$size	= ( int ) $params['page'] ?? 0;
	$smin	= config( 'monster_id_min', \MONSTER_ID_MIN, 'int' );
	$smax	= config( 'monster_id_max', \MONSTER_ID_MAX, 'int' );
	$size	= intRange( $size, $smin, $smax );
	
	// Try to send cached monster if it exists
	sendFile( monsterPath( $seed, $size, false ) );
	
	// Send to builder
	buildMonster( $seed, $size );
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
		[ 'get', 'monsterid/:slug',		'showMonsterID' ],
		[ 'get', 'monsterid/:page/:slug',	'showMonsterID' ]
	] );
}


// Register events

// Initialize configuration
hook( [ 'pluginsLoaded',	'monsterGDCheck' ] );
hook( [ 'checkconfig',		'checkMonsterIDConfig' ] );
hook( [ 'initroutes',		'addMonsterIDRoutes' ] );


