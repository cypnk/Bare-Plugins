<?php declare( strict_types = 1 );
/**
 *  Bare Captcha: Image verification generator for forms
 *  
 *  This is a work in progress
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


// Captcha field render template
$templates['tpl_captcha']	= <<<HTML
	<input type="hidden" name="capA" value="{capA}">
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
 *  Generate captcha image
 */
function captcha( string $txt ) {
	// Check for GD
	if ( missing( 'imagecreatetruecolor' ) ) {
		logError( 'CAPTCHA: Check GD function availability' );
		sendError( 404, errorLang( "notfound", MSG_NOTFOUND ) );
	}
	
	// Font file (not part of assets and isn't served to visitor directly)
	$ffile	= config( 'captcha_font', \CAPTCHA_FONT );
	$font	= \rtrim( \PLUGINS, '/' ) . '/captcha/' . $ffile; 
	if ( empty( filterDir( $font, \PLUGINS ) ) ) {
		logError( 'CAPTCHA: Invalid font file location' );
		sendError( 404, errorLang( "notfound", MSG_NOTFOUND ) );
	}
	if ( !file_exists( $font ) ) {
		logError( 'CAPTCHA: Font file not found' );
		sendError( 404, errorLang( "notfound", MSG_NOTFOUND ) );
	}
	
	// Height of image
 	$sizey	= config( 'captcha_height', \CAPTCHA_HEIGHT, 'int' );
	
	// Image meta info
	$mime	= config( 'captcha_mime', \CAPTCHA_MIME );
	$name	= config( 'captcha_name', \CAPTCHA_NAME );
	
	// Character length
 	$cl	= strsize( $txt );
	
	// Expand the image with the number of characters
 	$sizex	= ( $cl * 19 ) + 10;
	
	// Some initial padding
	$w	= floor( $sizex / $cl ) - 13;
	
	$img = \imagecreatetruecolor( $sizex, $sizey );
	$bg = \imagecolorallocate( $img, 255, 255, 255 );
	
	\imagefilledrectangle( $img, 0, 0, $sizex, $sizey, $bg );
	
	// Line thickness
	\imagesetthickness( $img, 3 );
	
	// Random lines
	for( $i = 0; $i < ( $sizex * $sizey ) / 250; $i++ ) {
		// Select colors in a comfortable range
		$t = 
		\imagecolorallocate( 
			$img, 
			\rand( 150, 200 ), 
			\rand( 150, 200 ), 
			\rand( 150, 200 ) 
		);
		
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
	
	// Insert the text (with random colors and placement)
	for ( $i = $cl; $i >= 0; $i--) {
		
		$l	= truncate( $txt, $i, 1 );
		
		// Maybe pastels
		$tc	= 
		\imagecolorallocate( 
			$img, 
			\rand( 0, 150 ), 
			\rand( 10, 150 ), 
			\rand( 10, 150 ) 
		);
		
		\imagettftext( 
			$img, 
			30, 
			\rand( -10, 10 ), 
			$w + ( $i * \rand( 18, 19 ) ), 
			\rand( 30, 40 ), 
			$tc, 
			$font, 
			$l 
		);
	}
	
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
	
	\imagedestroy( $img );
	shutdown();
}

/**
 *  Generate a captcha set
 */
function genCaptcha() : array {
	$clen	= config( 'captcha_length', \CAPTCHA_LENGTH, 'int' );
	$sh	= config( 'captcha_hash', \CAPTCHA_HASH );
	
	// Text sent as an image to user
	$usr	= genCodeKey( $clen );
	
	// Random nonce
	$capA	= genAlphaNum();
	
	// Combined hash sent as URL and stored in session
	$capB	= \hash_hmac( $sh, $capA, $usr );
	
	sessionCheck();
	$_SESSION[$capB] = $usr;
	
	// Send $capA to user as an image
	return [ $capA, $capB, $usr ];
}

/**
 *  Verify captcha and nonce pair
 */
function verifyCaptcha( $capA, $capB, $usr ) {
	$sh	= config( 'captcha_hash', \CAPTCHA_HASH );
	return 
	\hash_equals( $capB, \hash_hmac( $sh, $capA, $usr ) );
}

/**
 *  Generate and show captcha image if the hash name is already stored
 */
function showCaptcha( string $event, array $hook, array $params ) {
	$capB	=  $params['slug'] ?? '';
	
	sessionCheck();
	if ( empty( $capB ) || empty( $_SESSION[$capB] ) ) {
		visitorError( 404, 'NotFound' );
		sendError( 404, errorLang( "notfound", MSG_NOTFOUND ) );
	}
	
	captcha( $_SESSION[$capB] );
}

/**
 *  Render captcha input form
 */
function renderCaptcha() : string {
	$c = genCaptcha();
	$p = cutSlug( eventRoutePrefix( 'showcaptcha', 'captcha' ) ) . '/';
	
	return 
	render( template( 'tpl_captcha' ), [
		'capA'		=> $c[0],
		'captcha'	=> homeLink() . $p . $c[1]
	] );
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
hook( [ 'checkconfig',	'checkCaptchaConfig' ] );
hook( [ 'initroutes',	'addCaptchaRoutes' ] );
hook( [ 'loadlanguage',	'captchaLang' ] );
hook( [ 'showcaptcha',	'showCaptcha' ] );
hook( [ 'loadcssclasses', 'addCaptchaClasses' ] );


