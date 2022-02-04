<?php declare( strict_types = 1 );
if ( !defined( 'PATH' ) ) { die(); }
/**
 *  Bare Upload: This plugin adds a set of functions to help Bare handle file uploads
 *  
 *  Important: Remember to set ALLOW_POST in index.php to 1 or set 'allow_post' config.json to 1 
 *  before using this plugin
 *  
 *  Add this plugin to 'plugins_enabled' before the templates plugin if using both
 */


/** 
 *  Return uploaded $_FILES array into a more sane format
 * 
 *  @return array
 */
function parseUploads() : array {
	$files = [];
	
	foreach ( $_FILES as $name => $file ) {
		if ( \is_array($file['name']) ) {
			foreach ( $file['name'] as $n => $f ) {
				$files[$name][$n] = [];
				
				foreach ( $file as $k => $v ) {
					$files[$name][$n][$k] = 
						$file[$k][$n];
				}
			}
			continue;
		}
		
        	$files[$name][] = $file;
	}
	return $files;
}

/**
 *  Filter upload file name into a safe format
 *  
 *  @param string	$name		Original raw filename
 *  @return string
 */
function filterUpName( ?string $name ) : string {
	if ( empty( $name ) ) {
		return '_';
	}
	
	$name	= \preg_replace('/[^\pL_\-\d\.\s]', ' ' );
	return \preg_replace( '/\s+/', '-', \trim( $name ) );
}

/**
 * Move uploaded files to the same directory as the post
 */
function saveUploads( 
	string	$path, 
	string	$root 
) {
	$files	= parseUploads();
	$store	= 
		slashPath( $root, true ) . 
		slashPath( $path, true );
	
	foreach ( $files as $name ) {
		foreach( $name as $file ) {
			// If errors were found, skip
			if ( $file['error'] != \UPLOAD_ERR_OK ) {
				continue;
			}
			
			$tn	= $file['tmp_name'];
			$n	= filterUpName( $file['name'] );
			
			// Check for duplicates and rename 
			$up	= dupRename( $store . $n );
			\move_uploaded_file( $tn, $up );
		}
	}
}


	
