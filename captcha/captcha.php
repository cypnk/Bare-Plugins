<?php declare( strict_types = 1 );
/**
 *  Bare Captcha: Image verification generator for forms
 *  This is not a standalone plugin
 */

// Captcha image height
define( 'CAPTCHA_HEIGHT',	35 );

// Captcha font file name (in the same folder)
define( 'CAPTCHA_FONT',		'VeraMono.ttf' );

// Captcha string length
define( 'CAPTCHA_LENGTH',	5 );

// Captcha image mime type (currently, jpg, png, or bmp)
define( 'CAPTCHA_MIME',		'image/png' );

// Captcha image file name (extension should match mime)
define( 'CAPTCHA_NAME',		'captcha.png' );

// Default hashing algorithm
define( 'CAPTCHA_HASH',		'tiger160,4' );

// Generate session via URL if true (mostly used for testing)
define( 'CAPTCHA_GEN_URL',	0 );

// Captcha field render template
$templates['tpl_captcha']	= <<<HTML
	<input type="hidden" name="cap_a" value="{cap_a}">
	<input type="text" name="captcha" 
		placeholder="{lang:forms:captcha:placeholder}" 
		class="{input_classes}" required> 
	<img src="{captcha}" alt="{lang:forms:captcha:alt}" 
		class="{captcha_classes}">
HTML;


define( 'CAPTCHA_LANG',		<<<JSON
{
	"forms"		: {
		"captcha"	: {
			"placeholder"	: "Captcha",
			"alt"		: "captcha"
		}
	}
}
JSON
);

/**
 *  GD Color allocation helper
 *  
 *  @param GdImage	$img	Captcha source image
 *  @param int		$min	Minimum RGB range
 *  @param int		$max	Maximum RGB range
 *  @return mixed		Int on success or false on failure
 */
function captchaColors( &$img, int $min, int $max ) {
	return 
	\imagecolorallocate( 
		$img, 
		\mt_rand( $min, $max ), 
		\mt_rand( $min, $max ), 
		\mt_rand( $min, $max ) 
	);
}

/**
 *  Draw random lines
 *  
 *  @param GdImage	$img	Captcha source image
 *  @param int		$sizex	Source image width
 *  @param int		$sizey	Source image height
 */
function captchaLines( &$img, int $sizex, int $sizey ) {
	$e = ( $sizex * $sizey ) / 250;
	
	for ( $i = 0; $i < $e; $i++ ) {
		// Random line thickness
		\imagesetthickness( $img, \mt_rand( 2, 4 ) );
		
		// Select colors in a comfortable range
		$t = captchaColors( $img, 150, 200 );
		
		\imageline( 
			$img, 
			\mt_rand( 0, $sizex ), 
			\mt_rand( 0, $sizey ), 
			\mt_rand( 0, $sizex ), 
			\mt_rand( 0, $sizey ), 
			$t 
		);
	}
	
	// Reset thickness
	\imagesetthickness( $img, 1 );
}

/**
 *  Send generated captcha image to visitor with headers
 *  
 *  @param GdImage	$img	Captcha source image
 */
function captchaSend( &$img ) {
	// Image meta info
	$mime	= config( 'captcha_mime', \CAPTCHA_MIME );
	$name	= config( 'captcha_name', \CAPTCHA_NAME );
	
	// Prepare headers
	sendGenFilePrep( $mime, $name, 200, false );
	
	// Send generated image and end execution
	switch( $mime ) {
		case 'image/png':
			\imagepng( $img );
			break;
			
		case 'image/jpg':
		case 'image/jpeg':
			\imagejpeg( $img );
			break;
			
		case 'image/bmp':
			\imagebmp( $img );
			break;
	}
}

/**
 *  Generate captcha image
 */
function captcha( string $txt ) {
	// Check for GD
	if ( missing( 'imagecreatetruecolor' ) ) {
		logError( 'CAPTCHA: Check GD function availability' );
		sendNotFound();
	}
	
	$pr = slashPath( \PLUGINS, true ) . 'captcha';
	
	// Font file (not part of assets and isn't served to visitor directly)
	$ffile	= config( 'captcha_font', \CAPTCHA_FONT );
	$font	= $pr . slashPath( $ffile );
	if ( empty( filterDir( $font, $pr ) ) ) {
		shutdown( 'logError', 'CAPTCHA: Invalid font file location' );
		sendNotFound();
	}
	if ( !\file_exists( $font ) ) {
		shutdown( 'logError', 'CAPTCHA: Font file not found' );
		sendNotFound();
	}
	
	// Height of image
 	$sizey	= config( 'captcha_height', \CAPTCHA_HEIGHT, 'int' );
	
	// Character length
 	$cl	= strsize( $txt );
	
	// Expand the image with the number of characters
 	$sizex	= ( $cl * 19 ) + 10;
	
	// Seed random with given text
	$ha	= hashAlgo( 'captcha_hash', \CAPTCHA_HASH );
	$h	= \hexdec( \substr( \hash( $ha, $txt ), 0, 6 ) );
	\mt_srand( $h, \MT_RAND_MT19937 );
	
	$img	= \imagecreatetruecolor( $sizex, $sizey );
	
	// Fill background color
	$bg	= captchaColors( $img, 230, 255 );
	\imagefilledrectangle( $img, 0, 0, $sizex, $sizey, $bg );
	
	// Random lines
	captchaLines( $img, $sizex, $sizey );
	
	// Font point sizes
	$fpt	= [ 18, 24, 30 ];
	
	// Some initial padding
	$fp	= floor( $sizex / $cl ) - 13;
	
	// Insert the text (with random colors and placement)
	for ( $i = $cl; $i >= 0; $i--) {
		
		\imagettftext( 
			$img, 
			$fpt[\array_rand( $fpt, 1 )],		// Font size
			\mt_rand( -10, 10 ),			// Text angleH
			$fp + ( $i * \mt_rand( 18, 19 ) ),	// X position (padding + space for added text)
			\mt_rand( $sizey - 5, $sizey + 5 ),	// Y position (relative to image height)
			captchaColors( $img, 10, 150 ),		// Line color (maybe pastels)
			$font,					// Font file
			truncate( $txt, $i, 1 )			// Single character from text
		);
	}
	
	// Reset
	\mt_srand();
	captchaSend( $img );
	
	\imagedestroy( $img );
	shutdown();
}

/**
 *  Generate a captcha set
 */
function genCaptcha() : array {
	$clen	= config( 'captcha_length', \CAPTCHA_LENGTH, 'int' );
	$sh	= hashAlgo( 'captcha_hash', \CAPTCHA_HASH );
	
	// Text sent as an image to user
	$usr	= genCodeKey( $clen );
	
	// Random nonce
	$cap_a	= genAlphaNum();
	
	// Combined hash sent as URL and stored in session
	$cap_b	= \hash_hmac( $sh, $cap_a, $usr );
	
	sessionCheck();
	$_SESSION[$cap_b] = $usr;
	
	// Send $cap_a to user as an image
	return [ $cap_a, $cap_b, $usr ];
}

/**
 *  Verify captcha and nonce pair
 */
function verifyCaptcha( $cap_a, $cap_b, $usr ) {
	$sh	= hashAlgo( 'captcha_hash', \CAPTCHA_HASH );
	return 
	\hash_equals( $cap_b, \hash_hmac( $sh, $cap_a, $usr ) );
}

/**
 *  Generate and show captcha image if the hash name is already stored
 */
function showCaptcha( string $event, array $hook, array $params ) {
	$cap_b	=  $params['slug'] ?? '';
	
	sessionCheck();
	if ( config( 'captcha_gen_url', \CAPTCHA_GEN_URL, 'bool' ) ) {
		$gen = genCaptcha();
		captcha( $gen[0] );
	} else {
		if ( empty( $cap_b ) || empty( $_SESSION[$cap_b] ) ) {
			sendNotFound();
		}
	}
	
	captcha( $_SESSION[$cap_b] );
}

/**
 *  Render captcha input form
 */
function renderCaptcha() : string {
	$c = genCaptcha();
	$p = cutSlug( eventRoutePrefix( 'showcaptcha', 'captcha' ) ) . '/';
	
	return 
	render( template( 'tpl_captcha' ), [
		'cap_a'		=> $c[0],
		'captcha'	=> homeLink() . $p . $c[1]
	] );
}


/**
 *  Disable throttling of captcha route
 */
function captchaThrottle() {
	// Important: Only disable with trailing slash as there's always a slug
	throttleDisabled( '/captcha/' );	
}

/**
 *  Append captcha route
 */
function addCaptchaRoutes( string $event, array $hook, array $params ) {
	return 
	\array_merge( $hook, [
		[ 'get', 'captcha/:slug',	'showcaptcha' ]
	] );
}

/**
 *  Form field classes
 */
function addCaptchaClasses( string $event, array $hook, array $params ) {
	return 
	\array_merge_recursive( 
		$hook, [ 'classes' => [ 'captcha_classes' => '' ] ] 
	);
}

/**
 *  Append language placeholders
 */
function captchaLang( string $event, array $hook, array $params ) {
	return 
	\array_merge_recursive( 
		$hook, [ 'terms' => decode( \CAPTCHA_LANG ) ] 
	);
}

/**
 *  Configuration check
 */
function checkCaptchaConfig( string $event, array $hook, array $params ) {
	$filter	= [
		'captcha_font'	=> [
			'filter'=> \FILTER_VALIDATE_URL,
			'options' => [
				'default' => \CAPTCHA_FONT
			],
		],
		'captcha_name'	=> [
			'filter'	=> \FILTER_SANITIZE_STRING,
			'options' => [
				'default' => \CAPTCHA_NAME
			]
		],
		'captcha_mime'	=> [
			'filter'	=> \FILTER_SANITIZE_STRING,
			'options' => [
				'default' => \CAPTCHA_MIME
			]
		],
		'captcha_height'=> [
			'filter'	=> \FILTER_VALIDATE_INT,
			'options'	=> [
				'min_range'	=> 10,
				'max_range'	=> 50,
				'default'	=> \CAPTCHA_HEIGHT
			]
		],
		'captcha_length'=> [
			'filter'	=> \FILTER_VALIDATE_INT,
			'options'	=> [
				'min_range'	=> 3,
				'max_range'	=> 24,
				'default'	=> \CAPTCHA_LENGTH
			]
		],
		'captcha_hash'	=> [
			'filter'	=> 
				\FILTER_SANITIZE_SPECIAL_CHARS,
			'flags'	=> 
				\FILTER_FLAG_STRIP_LOW	| 
				\FILTER_FLAG_STRIP_HIGH	| 
				\FILTER_FLAG_STRIP_BACKTICK 
		],
		'captcha_gen_url'=> [
			'filter'	=> \FILTER_VALIDATE_INT,
			'options'	=> [
				'min_range'	=> 0,
				'max_range'	=> 1,
				'default'	=> \CAPTCHA_GEN_URL
			]
		]
	];
	
	$mime	= [
		'png'	=> 'image/png',
		'jpg'	=> 'image/jpg',
		'jpeg'	=> 'image/jpg',
		'bmp'	=> 'image/bmp'
	];
	$data	= \filter_var_array( $params, $filter, false );
	
	// Check file name matches mime
	$ext	= 
	\pathinfo( $data['captcha_name'], \PATHINFO_EXTENSION ) ?? '';
	
	if ( 
		empty( $ext ) || 
		!\in_array( $data['captcha_mime'], $mime, true ) 
	) {
		$data['captcha_mime'] = \CAPTCHA_MIME;
		$data['captcha_name'] = \CAPTCHA_NAME;
		
	} elseif ( $data['captcha_mime'] != $mime[$ext] ) {
		$data['captcha_mime'] = \CAPTCHA_MIME;
		$data['captcha_name'] = \CAPTCHA_NAME;
	}
	
	if ( isset( $data['captcha_hash'] ) ) {
		$data['captcha_hash']	= 
		hashAlgo( ( string ) $data['captcha_hash'], \CAPTCHA_HASH );
	}
	
	return \array_merge( $hook, $data );
}

// Register events
hook( [ 'pluginsLoaded', 'captchaThrottle' ] );
hook( [ 'checkconfig',	'checkCaptchaConfig' ] );
hook( [ 'initroutes',	'addCaptchaRoutes' ] );
hook( [ 'loadlanguage',	'captchaLang' ] );
hook( [ 'showcaptcha',	'showCaptcha' ] );
hook( [ 'loadcssclasses', 'addCaptchaClasses' ] );


