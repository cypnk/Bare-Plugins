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
	if ( !file_exists( $font ) ) {
		logError( 'CAPTCHA: Font file not found' );
		sendError( 404, errorLang( "notfound", MSG_NOTFOUND ) );
	}
	
	// Height of image
 	$sizey	= config( 'captcha_height', \CAPTCHA_HEIGHT, 'int' );
	
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
	sendGenFilePrep( 'image/png', 'captcha.png', 200, false );
	
	// Send generated image and end execution
	\imagepng( $img );
	\imagedestroy( $img );
	shutdown();
}

/**
 *  Generate a captcha set
 */
function genCaptcha() : array {
	// Text sent as an image to user
	$usr	= genCodeKey( 4 );
	
	// Random nonce
	$capA	= genAlphaNum();
	
	// Combined hash sent as URL and stored in session
	$capB	= \hash_hmac( 'tiger160,4', $capA, $usr );
	
	sessionCheck();
	$_SESSION[$capB] = $usr;
	
	// Send $capA to user as an image
	return [ $capA, $capB, $usr ];
}

/**
 *  Verify captcha and nonce pair
 */
function verifyCaptcha( $capA, $capB, $usr ) {
	return 
	\hash_equals( $capB, \hash_hmac( 'tiger160,4', $capA, $usr ) );
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
 *  Append captcha route
 */
function addCaptchaRoutes( string $event, array $hook, array $params ) {
	return 
	\array_merge( $hook, [
		[ 'get', 'captcha/:slug',	'showcaptcha' ]
	] );
}


// Register events
hook( [ 'initroutes',	'addCaptchaRoutes' ] );
hook( [ 'showcaptcha',	'showCaptcha' ] );


