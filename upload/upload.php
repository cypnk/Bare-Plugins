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

// Enable generating thumbnails for image types
define( 'THUMBNAIL_GEN',	1 );

// Image types to generate thumbnail
define( 'THUMBNAIL_TYPES',	'image/jpeg, image/png, image/gif, image/bmp' );

// Default thumbnail size
define( 'THUMBNAIL_WIDTH',	100 );

// Prefix added to thumbnail filenames
define( 'THUMBNAIL_PREFIX',	'tn_' );

/**
 *  Create image thumbnails from file path and given mime type
 *  
 *  @param string 	$src	Original image path
 *  @param string	$mime	Image mime type
 */
function createThumbnail( 
	string	$src,
	string	$mime 
) : string {
	
	// Get size and set proportions
	$imgsize	= \getimagesize( $src );
	if ( false === $imgsize ) {
		return '';
	}
	
	if ( empty( $imgsize[0] ) || empty( $imgsize[1] ) ) {
		return '';
	}
	$width		= $imgsize[0];
	$height		= $imgsize[1];
	
	$t_width	= 
	config( 'thumbnail_width', \THUMBNAIL_WIDTH, 'int' );
	
	// Width too small to generate thumbnail
	if ( $t_width > $width ) {
		return '';
	}
	
	$t_height	= ( $t_width / $width ) * $height;
	
	// New thumbnail
	$thumb		= \imagecreatetruecolor( $t_width, $t_height );
	
	// Create new image
	switch( $mime ) {
		case 'image/png':
			// Set transparent background
			\imagesavealpha( $thumb, true );
			$source	= \imagecreatefrompng( $src );
			break;
			
		case 'image/gif':
			$source	= \imagecreatefromgif( $src );
			break;
		
		case 'image/bmp':
			$source	= \imagecreatefrombmp( $src );
			break;
			
		default:
			$source	= \imagecreatefromjpeg( $src );
	}
	
	// Resize to new resources
	\imagecopyresized( $thumb, $source, 0, 0, 0, 0, 
		$t_width, $t_height, $width, $height );
	
	// Thunbnail destination
	$tnp	= config( 'thumbnail_prefix', \THUMBNAIL_PREFIX );
	$dest	= dupRename( prefixPath( $src, labelName( $tnp ) ) );
	
	// Create thumbnail at destination
	switch( $mime ) {
		case 'image/png':
			$tn = imagepng( $thumb, $dest, 100 );
			break;
		
		case 'image/gif':
			$tn = imagegif( $thumb, $dest, 100 );
			break;
		
		case 'image/bmp':
			$tn = imagebmp( $thumb, $dest, 100 );
			break;
		
		default:
			$tn = imagejpeg( $thumb, $dest, 100 );
	}
	
	// Did anything go wrong?
	if ( false === $tn ) {
		return '';
	}
	
	// Cleanup
	\imagedestroy( $thumb );
	
	return $dest;
}

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
 *  Format uploaded file info
 *  
 *  @param string	$src	Complete file path
 *  @param array	$img	Allowed thumbnail image types
 *  @param bool		$tn	Create a thumbnail for allowed types, if true
 */
function processUpload( string $src, array $img, bool $tn = false ) {
	$mime	= detectMime( $src );
	
	return [
		'src'		=> $src,
		'mime'		=> $mime,
		'filename'	=> \basename( $src ),
		'filesize'	=> \filesize( $src ),
		'description'	=> '',
		
		// Process thumbnail if needed
		'thumbnail'	=> $tn ? ( 
			\in_array( $mime, $img ) ? 
				createThumbnail( $src, $mime ) : '' 
		) : ''
	];
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
	
	$saved	= [];
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
			if ( \move_uploaded_file( $tn, $up ) ) {
				$saved[] = $up;
			}
		}
	}
	$tn		= 
	config( 'thumbnail_gen', \THUMBNAIL_GEN, 'bool' );
	
	$img		= 
	trimmedList( config( 'thumbnail_types', \THUMBNAIL_TYPES ) );
	
	// Once uploaded and moved, format info
	$processed	= [];
	foreach( $saved as $k => $v ) {
		$processed[] = processUpload( $v, $img, $tn );	
	}
	return $processed;
}


/**
 *  Configuration check
 */
function checkUploadConfig( string $event, array $hook, array $params ) {
	$filter = [
		'thumbnail_gen'	=> [
			'filter'	=> \FILTER_VALIDATE_INT,
			'options'	=> [
				'min_range'	=> 0,
				'max_range'	=> 1,
				'default'	=> \THUMBNAIL_GEN
			]
		],
		'thumbnail_width'	=> [
			'filter'	=> \FILTER_VALIDATE_INT,
			'options'	=> [
				'min_range'	=> 20,
				'max_range'	=> 1024,
				'default'	=> \THUMBNAIL_WIDTH
			]
		],
		'thumbnail_prefix'	=> [
			'filter'	=> \FILTER_CALLBACK,
			'options'	=> 'labelName'
		],
		'thumbnail_types'	=> [
			'filter'	=> \FILTER_CALLBACK,
			'options'	=> 'trimmedList'
		]
	];
	$data	= \filter_var_array( $params, $filter, false );
	
	if ( empty( $data['thumbnail_types' ) ) {
		$data['thumbnail_types'] = 
			trimmedList( \THUMBNAIL_TYPES );
	}
	
	if ( empty( $data['thumbnail_prefix'] ) ) {
		$data['thumbnail_prefix'] = 
			labelName( \THUMBNAIL_PREFIX );
	}
	return \array_merge( $hook, $data );
}

	
hook( [ 'checkconfig',	'checkUploadConfig' ] );
