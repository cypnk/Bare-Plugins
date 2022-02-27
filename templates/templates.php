<?php declare( strict_types = 1 );
if ( !defined( 'PATH' ) ) { die(); }
/**
 *  Bare Templates: This plugin enables overriding the default templates with 
 *  custom files stored in the TEMPLATES configuration location
 *  
 *  E.G. 
 *  To replace 'tpl_full_page', create 'files/tpl_full_page.tpl' with your HTML
 *  
 *  Then add 'tpl_full_page' to a 'templates' array in config.json
 */

// Template files directory (defaults to /files/ in the templates plugin folder)
define( 'TEMPLATES',	\PLUGINS . 'templates/files/' );


/**
 *  Load configuration defined templates and override current defaults
 */
function loadTemplates( string $event, array $hook, array $params ) {
	$tpl = config( 'templates', [] );
	if ( empty( $tpl ) || !\is_array( $tpl ) ) {
		return \array_merge( $hook, $params );
	}
	
	$loaded	= [];
	$err	= [];
	foreach( $tpl as $t ) {
		if ( !\is_string( $t ) ) {
			continue;
		}
		
		// Load new template file from cache folder
		$fname = \TEMPLATES . $t . '.tpl';
		if ( empty( filterDir( $fname, \TEMPLATES ) ) ) {
			// Invalid template location
			$err[] = $t;
			continue;
		}
		
		// Only load existing templates
		if ( \file_exists( $fname ) ) {
			$data = \file_get_contents( $fname );
			
			// Nothing loaded?
			if ( false === $data ) {
				$err[] = $t;
				continue;
			}
			$loaded[$t]	= pacify( $data );
		}
	}
	
	// Re-register new templates, if any
	if ( !empty( $loaded ) ) {
		template( '', $loaded );
	}
	
	// Log any template loading errors on shutdown
	if ( !empty( $err ) ) {
		shutdown( 
			'logError', 
			'Error loading: ' . implode( ' ' . $err ) 
		);
	}
	return \array_merge( $hook, $params );
}

// Load template configuration
function checkTemplateConfig( string $event, array $hook, array $params ) {
	$filter	= [
		'templates'	=>[
			'filter'	=> \FILTER_CALLBACK,
			'flags'		=> \FILTER_REQUIRE_ARRAY,
			'options'	=> 'pacify'
		]
	];
	
	return 
	\array_merge( $hook, \filter_var_array( $params, $filter ) );
}

// Initialize configuration
hook( [ 'checkconfig',	'checkTemplateConfig' ] );

// Load new templates after plugin load
hook( [ 'pluginsloaded', 'loadTemplates' ] );




