<?php declare( strict_types = 1 );
if ( !defined( 'PATH' ) ) { die(); }
/**
 *  Bare Render: Templating and rendering plugin for Bare
 *  This plugin enables template customization, multi-lingual lables, 
 *  and form input rendering
 *  
 *  This file hould be required first if any others depend on it
 */


// Static resource relative path for JS, CSS, static images etc...
define( 'SHARED_ASSETS',		'/' );

// List of stylesheets to load from SHARED_ASSETS (one per line)
define( 'DEFAULT_STYLESHEETS',		<<<LINES
{shared_assets}style.css
LINES
);

// Default JavaScript files
define( 'DEFAULT_SCRIPTS',		<<<LINES

LINES
);

// Main navigation links
define( 'DEFAULT_MAIN_LINKS',		<<<JSON
{
	"links" : [
		{ "{url}" : "{home}about", "{text}" : "About" },
		{ "{url}" : "{home}archive", "{text}" : "Archive" },
		{ "{url}" : "{home}feed", "{text}" : "Feed" }
	]
}
JSON
);

// Footer links
define( 'DEFAULT_FOOTER_LINKS',		<<<JSON
{
	"links" : [
		{ "{url}" : "{home}about", "{text}" : "About" },
		{ "{url}" : "{home}archive", "{text}" : "Archive" },
		{ "{url}" : "{home}feed", "{text}" : "Feed" }
	]
}
JSON
);

// Maximum number of stylesheets to load, if set
define( 'STYLE_LIMIT',		20 );

// Maximum mumber of script files to load
define( 'SCRIPT_LIMIT',		10 );



/**
 *  Overridable CSS classes in this plugin
 */
define( 'DEFAULT_CLASSES', <<<JSON
{
	"body_classes"			: "",
	
	"heading_wrap_classes"		: "content", 
	"items_wrap_classes"		: "content", 
	
	"main_nav_classes"		: "main",
	"main_ul_classes"		: "", 
	
	"pagination_wrap_classes"	: "content", 
	"list_wrap_classes"		: "content", 
	
	
	"footer_wrap_classes"		: "content", 
	"footer_nav_classes"		: "",
	"footer_ul_classes"		: "",
	
	"crumb_classes"			: "",
	"crumb_wrap_classes"		: "",
	"crumb_sub_classes"		: "",
	"crumb_sub_wrap_classes"	: "",
	
	"crumb_item_classes"		: "",
	"crumb_link_classes"		: "",
	"crumb_current_classes"		: "",
	"crumb_current_item"		: "",
	"pagination_classes"		: "",
	"pagination_ul_classes"		: "",
	
	"nav_link_classes"		: "",
	"nav_link_a_classes"		: "",
	
	"list_classes"			: "related",
	"list_h_classes"		: "",
	
	"tag_classes"			: "tags",
	"tag_ul_classes"		: "",
	
	"nextprev_wrap_classes"		: "content", 
	"nextprev_classes"		: "siblings",
	"nextprev_ul_classes"		: "",
	
	"nav_current_classes"		: "",
	"nav_current_s_classes"		: "",
	"nav_prev_classes"		: "",
	"nav_prev_a_classes"		: "",
	"nav_noprev_classes"		: "",
	"nav_noprev_s_classes"		: "",
	"nav_next_classes"		: "",
	"nav_next_a_classes"		: "",
	"nav_nonext_classes"		: "",
	"nav_nonext_s_classes"		: "",
	
	"nav_first1_classes"		: "",
	"nav_first1_a_classes"		: "",
	"nav_first2_classes"		: "",
	"nav_first2_a_classes"		: "",
	"nav_first_s_classes"		: "",
	
	"nav_last_s_classes"		: "",
	"nav_last1_classes"		: "",
	"nav_last1_a_classes"		: "",
	"nav_last2_classes"		: "",
	"nav_last2_a_classes"		: "",
	
	"form_classes"			: "",
	"field_wrap"			: "",
	"button_wrap"			: "",
	"label_classes"			: "",
	"special_classes"		: "",
	"input_classes"			: "",
	"desc_classes"			: "",
	
	"submit_classes"		: "",
	"alt_classes"			: "",
	"warn_classes"			: "",
	"action_classes"		: ""
}
JSON
);


/**
 *  Plugin templates
 */


/**
 *  HTML Components
 */

// HTML Starting component
define( 'TPL_PAGE_HEAD',	<<<HTML
<!DOCTYPE html>
<html lang="{lang}">
<head>
<meta charset="UTF-8">
<title>{post_title}</title>
{after_title}
{stylesheets}
{meta_tags}
</head>
HTML
);


// HTML Ending component
define( 'TPL_PAGE_BODY',	<<<HTML
<body class="{body_classes}">
{body_before}
{body}
{body_after}
{body_before_lastjs}
{body_js}
{body_after_lastjs}
</body>
</html>
HTML
);

// HTML full page component
define( 'TPL_FULL_PAGE',	<<<HTML
<!DOCTYPE html>
<html lang="{lang}">
<head>
<meta charset="UTF-8">
<title>{post_title}</title>
{after_title}
{stylesheets}
{meta_tags}
</head>
<body class="{body_classes}">
{body_before}
{body}
{body_after}
{body_before_lastjs}
{body_js}
{body_after_lastjs}
</body>
</html>
HTML
);

define( 'TPL_PAGE_FOOTER',		<<<HTML
<footer>
<div class="{footer_wrap_classes}">
<nav class="{footer_nav_classes}">
<ul class="{footer_ul_classes}">{links}</ul>
</nav>
</div>
</footer>
HTML
);

define( 'TPL_PAGE_ITEMS_WRAP',		<<<HTML
<div class="{items_wrap_classes}">
{items}
</div>
HTML
);

// Meta, script, and stylesheet tag templates
define( 'TPL_META_TAG',	'<meta name="{name}" content="{content}">' );
define( 'TPL_SCRIPT_TAG', '<script src="{url}"></script>' );
define( 'TPL_STYLE_TAG', '<link rel="stylesheet" href="{url}">' );

// Feed tag template
define( 'TPL_FEED_TAG', 
'<link rel="alternate" type="application/xml" title="{title}" href="{url}">' );

define( 'TPL_PAGE_HEADING',	<<<HTML
<header>
<div class="{heading_wrap_classes}">
<h1><a href="{home}">{page_title}</a></h1><p>{tagline}</p>
{heading_after}
</div>
</header>
HTML
);

/**
 *  Navigation components
 */

// Breadcrumb path wrapper
define( 'TPL_BREADCRUMBS',	<<<HTML
<nav class="{crumb_classes}">
<ul class="{crumb_wrap_classes}">
{links}
</ul>
</nav>
HTML
);

// Breadcrumb within content
define( 'TPL_SUBBREADCRUMBS',	<<<HTML
<nav class="{crumb_sub_classes}">
<ul class="{crumb_sub_wrap_classes}">
{links}
</ul>
</nav>
HTML
);

// Breadcrumb link
define( 'TPL_CRUMB_LINK',	<<<HTML
<li class="{crumb_item_classes}">
<a href="{url}" class="{crumb_link_classes}">{label}</a>
</li>
HTML
);

// Breadcrumb current page
define( 'TPL_CRUMB_CURRENT',	<<<HTML
<li class="{crumb_current_classes}">
<span class="{crumb_current_item}" title="{url}">{label}</span>
</li>
HTML
);

// Main navigation wrapper
define( 'TPL_PAGE_MAIN',	<<<HTML
<nav class="{main_nav_classes}">
<ul class="{main_ul_classes}">
{links}
</ul>
</nav>
HTML
);

// Pagination wrapper
define( 'TPL_PAGINATION',	<<<HTML
<div class="{pagination_wrap_classes}">
<nav class="{pagination_classes}">
<ul class="{pagination_ul_classes}">
{links}
</ul>
</nav>
</div>
HTML
);

// Next/Previous pagination
define( 'TPL_PAGE_NEXTPREV',	<<<HTML
<div class="{nextprev_wrap_classes}">
	<nav class="{nextprev_classes}">
	<ul class="{nextprev_ul_classes}">{links}</ul></nav>
</div>
HTML
);

// Language placeholders
define( 'TPL_PAGE_PREVIOUS',		'{lang:nav:previous}' );
define( 'TPL_PAGE_NEXT',		'{lang:nav:next}' );
define( 'TPL_PAGE_HOME',		'{lang:nav:home}' );

// Tag listings
define( 'TPL_PAGE_TAGWRAP', <<<HTML
<nav class="{tag_classes}">{lang:headings:tags} 
	<ul class="{tag_ul_classes}">{text}</ul></nav>
HTML
);

// Pagination link
define( 'TPL_PAGE_NAV_LINK',	<<<HTML
<li class="{nav_link_classes}"><a href="{url}" class="{nav_link_a_classes}">{text}</a></li>
HTML
);

// Pagination current page
define( 'TPL_PAGE_CURRENT_LINK',<<<HTML
<li class="{nav_current_classes}"><span class="{nav_current_s_classes}" title="{url}">{text}</span></li>
HTML
);


// Pagination next page link
define( 'TPL_PAGE_PREV_LINK',	<<<HTML
<li class="{nav_prev_classes}"><a href="{url}" class="{nav_prev_a_classes}">Previous</a></li>
HTML
);

// No previous page
define( 'TPL_PAGE_NOPREV',	<<<HTML
<li class="{nav_noprev_classes}"><span class="{nav_noprev_s_classes}">Previous</span></li>
HTML
);

// Pagination previous page link
define( 'TPL_PAGE_NEXT_LINK',	<<<HTML
<li class="{nav_next_classes}"><a href="{url}" class="{nav_next_a_classes}">Next</a></li>
HTML
);

// No more pages
define( 'TPL_PAGE_NONEXT',	<<<HTML
<li class="{nav_nonext_classes}"><span class="{nav_nonext_s_classes}">Next</span></li>
HTML
);

// Pagination first two links before skipping next
define( 'TPL_PAGE_FIRST2',	<<<HTML
	<li class="{nav_first1_classes}"><a href="{url1}" class="{nav_first1_a_classes}">{text1}</a></li>
	<li class="{nav_first2_classes}"><a href="{url2}" class="{nav_first2_a_classes}">{text2}</a></li>
	<li class="{nav_first_s_classes}">...</li>
HTML
);


// Pagination last two links after skipping previous
define( 'TPL_PAGE_LAST2',	<<<HTML
	<li class="{nav_last_s_classes}">...</li>
	<li class="{nav_last1_classes}"><a href="{url1}" class="{nav_last1_a_classes}">{text1}</a></li>
	<li class="{nav_last2_classes}"><a href="{url2}" class="{nav_last2_a_classes}">{text2}</a></li>
HTML
);

// General list for E.G. "Related", "New", "More" etc...
define( 'TPL_PAGE_LIST',	<<<HTML
<div class="{list_wrap_classes}">
	<h3 class="list_h_classes">{heading}</h3>
	<nav class="{list_classes}"><ul>{links}</ul></nav>
</div>
HTML
);


/**
 *  User input form building blocks
 */

// Select box option
define( 'TPL_INPUT_SELECT_OPT',	<<<HTML
<option value="{value}" {selected}>{text}</option>
HTML
);

// Select dropdown
define( 'TPL_INPUT_SELECT',<<<HTML
<label for="{id}" class="{label_classes}">{label} 
	<span class="{special_classes}">{special}</span></label> 
<select id="{id}" name="{name}" aria-describedby="{id}-desc"
	class="{input_classes}" {extra}>
	<option value=""> - </option>{options}</select>
<small id="{id}-desc" class="{desc_classes}">{desc}</small>
HTML
);

// Text field input
define( 'TPL_INPUT_TEXT',	<<<HTML
<label for="{id}" class="{label_classes}">{label} 
	<span class="{special_classes}">{special}</span></label>
<input id="{id}" name="{name}" type="text" aria-describedby="{id}-desc"
	class="{input_classes}" value="{value}" {extra}>
<small id="{id}-desc" class="{desc_classes}">{desc}</small>
HTML
);


// Search field input
define( 'TPL_INPUT_SEARCH',	<<<HTML
<label for="{id}" class="{label_classes}">{label} 
	<span class="{special_classes}">{special}</span></label>
<input id="{id}" name="{name}" type="search" aria-describedby="{id}-desc"
	class="{input_classes}" value="{value}" {extra}>
<small id="{id}-desc" class="{desc_classes}">{desc}</small>
HTML
);


// Datetime field input
define( 'TPL_INPUT_DATETIME',	<<<HTML
<label for="{id}" class="{label_classes}">{label} 
	<span class="{special_classes}">{special}</span></label>
<input id="{id}" name="{name}" type="datetime-local" aria-describedby="{id}-desc"
	class="{input_classes}" value="{value}" {extra}>
<small id="{id}-desc" class="{desc_classes}">{desc}</small>
HTML
);


// Email field input
define( 'TPL_INPUT_EMAIL',<<<HTML
<label for="{id}" class="f6 b db mb2">{label} 
	<span class="{special_classes">{special}</span></label>
<input id="{id}" name="{name}" type="email" aria-describedby="{id}-desc"
	class="{input_classes}" value="{value}" {extra}>
<small id="{id}-desc" class="{desc_classes}">{desc}</small>
HTML
);


// Password field input
define( 'TPL_INPUT_PASS',	<<<HTML
<label for="{id}" class="{label_classes}">{label} 
	<span class="{special_classes}">{special}</span></label> 
<input id="{id}" name="{name}" type="password" aria-describedby="{id}-desc"
	class="{input_classes}" {extra}>
<small id="{id}-desc" class="{desc_classes}">{desc}</small>
HTML
);


// Multiline text block content input
define( 'TPL_INPUT_MULTILINE',	<<<HTML
<label for="{id}" class="{label_classes}">{label} 
	<span class="{special_classes}">{special}</span></label> 
<textarea id="{id}" name="{name}" aria-describedby="{id}-desc" 
	class="{input_classes}" {extra}>{value}</textarea>
<small id="{id}-desc" class="{desc_classes}">{desc}</small>
HTML
);

// Checkbox input
define( 'TPL_INPUT_CHECKBOX',	<<<HTML
<label for="{id}" class="{label_classes}">{label} 
	<span class="{special_classes}">{special}</span></label>
<input id="{id}" name="{name}" value="{value}" type="checkbox"
		class="{input_classes}" aria-describedby="{id}-desc">
	<small id="{id}-desc" class="{desc_classes}">{desc}</small>
HTML
);


/**
 *  Special inputs with label after input field
 */

// Text field input
define( 'TPL_INPUT_TEXT_SE',	<<<HTML
<input id="{id}" name="{name}" type="text" aria-describedby="{id}-desc"
	class="{input_classes}" value="{value}" {extra}>
<label for="{id}" class="{label_classes}">{label} 
	<span class="{special_classes}">{special}</span></label>
<small id="{id}-desc" class="{desc_classes}">{desc}</small>
HTML
);


// Password field input
define( 'TPL_INPUT_PASS_SE',	<<<HTML
<input id="{id}" name="{name}" type="password" aria-describedby="{id}-desc"
	class="{input_classes}" {extra}>
<label for="{id}" class="{label_classes}">{label} 
	<span class="{special_classes}">{special}</span></label> 
<small id="{id}-desc" class="{desc_classes}">{desc}</small>
HTML
);


// Email field input
define( 'TPL_INPUT_EMAIL_SE',<<<HTML
<input id="{id}" name="{name}" type="email" aria-describedby="{id}-desc"
	class="{input_classes}" value="{value}" {extra}>
<label for="{id}" class="f6 b db mb2">{label} 
	<span class="{special_classes">{special}</span></label>
<small id="{id}-desc" class="{desc_classes}">{desc}</small>
HTML
);



// Multiline text block content input
define( 'TPL_INPUT_MULTILINE_SE',	<<<HTML
<textarea id="{id}" name="{name}" aria-describedby="{id}-desc" 
	class="{input_classes}" {extra}>{value}</textarea>
<label for="{id}" class="{label_classes}">{label} 
	<span class="{special_classes}">{special}</span></label> 
<small id="{id}-desc" class="{desc_classes}">{desc}</small>
HTML
);

// Post button
define( 'TPL_INPUT_SUBMIT',
	'<input type="submit" id="{id}" name="{name}" value="{value}" class="{submit_classes}">' );

// Alternate submit (E.G. Save draft, search)
define( 'TPL_INPUT_SUBMIT_ALT',	
	'<input type="submit" id="{id}" name="{name}" value="{value}" class="{alt_classes}">' );

// Critical submit (E.G. Delete)
define( 'TPL_INPUT_SUBMIT_WARN', 
	'<input type="submit" name="{name}" value="{value}" class="{warn_classes}" {extras}>' );

// Action submit (E.G. Sort)
define( 'TPL_INPUT_SUBMIT_ACTION', 
	'<input type="submit" name="{name}" value="{value}" class="{action_classes}" {extras}>' );

// Generic block input form
define( 'TPL_FORM_BLOCK',	<<<HTML
<form id="{id}" action="{action}" method="{method}" enctype="{enctype}" 
	class="{form_classes}" {extra}>{fields}</form>
HTML
);

// Generic inline form
define( 'TPL_FORM',		<<<HTML
<form id="{form_classes}" method="{method}" action="{action}" 
	enctype="{enctype}" accept-charset="UTF-8" {extra}>{fields}</form>
HTML
);


// Default language placholders (these can be overridden in {lang}.json)
define( 'DEFAULT_LANGUAGE',	<<<JSON
{
	"headings": {
		"related"	: "Related:", 
		"tags"		: "Tags:"
	}, 
	"nav": {
		"previous"	: "Previous",
		"next"		: "Next",
		"home"		: "Home"
	}
}
JSON
);


/**********************************************************************
 *                      Caution editing below
 **********************************************************************/

/**
 *  Self identification
 */
define( 'RENDER_PLUGIN',	1 );


/**
 *  Rendering template definitions
 */


/**
 *  General term match pattern in {domain:etc...} format
 *  Test: https://regex101.com/r/mFHkWO/3
 */
define( 'RENDER_RX_MATCH',	<<<RX
/(:?\{)
	(
		([\w]+)
		(:?\(([\w=\"\'\:,]+):?\))?
	)
	{repeat}
(:?\})/igx
RX
);
	
/**
 *  Repeated matching sub pattern. E.G. {domain:sub:etc...}
 */
define( 'RENDER_RX_REPEAT',	<<<RX
(\:?
	([\w]+)
	(:?\(([\w=\"\'\:,]+):?\))?
)?
RX
);
	
/**
 *  Maximum number of sub matches (in addition to primary)
 */
define( 'RENDER_MAX_DEPTH',	6 );

/**
 *  Item start
 */
define( 'RENDER_IDX_ITEM',	3 );

/**
 *  Parameter start
 */
define( 'RENDER_IDX_PARAM',	5 );

/**
 *  Skip n items for next item/parameter
 */
define( 'RENDER_IDX_SKIP',	4 );


/**
 *  Helpers
 */


/**
 *  Flatten a multi-dimensional array into a path map
 *  
 *  @link https://stackoverflow.com/a/2703121
 *  
 *  @param array	$items		Raw item map (parsed JSON)
 *  @param string	$delim		Phrase separator in E.G. {lang:}
 *  @return array
 */ 
function flatten(
	array		$items, 
	string		$delim	= ':'
) : array {
	$it	= new \RecursiveIteratorIterator( 
			new \RecursiveArrayIterator( $items )
		);
	
	$out	= [];
	foreach ( $it as $leaf ) {
		$path = '';
		foreach ( \range( 0, $it->getDepth() ) as $depth ) {
			$path .= 
			\sprintf( 
				"$delim%s", 
				$it->getSubIterator( $depth )->key() 
			);
		}
		$out[$path] = $leaf;
	}
	
	return $out;
}



/**
 *  Language translation
 */

/**
 *  Load and process language file
 *  
 *  @return array
 */
function language() {
	static $data;
	
	if ( isset( $data ) ) {
		return $data;
	}
	
	$terms	= decode( \DEFAULT_LANGUAGE );
	$lang	= config( 'language', \LANGUAGE );
	$file	= loadFile( $lang . '.json' );
	if ( !empty( $file ) ) {
		$terms	= 
		\array_merge_recursive( $terms,  decode( $file ) );
	}
	
	$data	= empty( $terms ) ? [] : $terms;
	// Trigger language load hook
	hook( [ 'loadlanguage', [ 
		'lang'	=> \LANGUAGE, 
		'zone'	=> \TIMEZONE,
		'terms' => $data
	] ] );
	
	return $data;
}

/**
 *  Get language specific terms
 *  
 *  @param string	$name		Language substitution label
 *  @return string
 */
function langVar( string $name ) {
	$data = language();
	return $data[$name] ?? '';
}

/**
 *  Get translation file error message with fallback
 *  
 *  @param string	$name		Language substitution label
 *  @param string	$default	Fallback value if not available
 *  @return string
 */
function errorLang( string $name, string $default ) {
	$data = language();
	return $data['errors'][$name] ?? $default;
}

/**
 *  Term replacement helper
 *  Flattens multidimensional array into {$prefix:group:label...} format
 *  and replaces matching placeholders in content
 *  
 *  @param string	$prefix		Replacement prefix E.G. 'lang'
 *  @param array	$data		Multidimensional array
 *  @param string	$content	Placeholders to replace
 *  @return string
 */ 
function prefixReplace(
	string		$prefix, 
	array		$data, 
	string		$content
) : string {
	// Find placeholders with given prefix
	\preg_match_all( 
		'/\{' . $prefix . '(\:[\:a-z_]{1,100}+)\}/i', 
		$content, $m 
	);
	// Convert data to :group:label... format
	$terms	= flatten( $data );
	
	// Replacements list
	$rpl	= [];
	
	$c	= \count( $m );
	
	// Set {prefix:group:label... } replacements or empty string
	for( $i = 0; $i < $c; $i++ ) {
		if ( !isset( $m[1] ) ) {
			continue;
		}
		$rpl['{' . $prefix . $m[1][$i] . '}']	= 
			$terms[$m[1][$i]] ?? '';
	}
	
	return \strtr( $content, $rpl );
}

/**
 *  Scan template for language placeholders
 *  
 *  @param string	$tpl	Loaded template data
 *  @return string
 */
function parseLang( string $tpl ) : string {
	$tpl		= prefixReplace( 'lang', language(), $tpl );
	
	// Change variable placeholders
	return \preg_replace( '/^\s*__(\w+)__\s*/', '{\1}', $tpl );
}





/**
 *  Template and rendering
 */

/**
 *  Placeholder match pattern builder
 *  
 *  @example 
 *  Items 3, 7, 11, 15, 19, 23
 *  Item params 5, 9, 13, 17, 21, 25
 */
function getRenderRegex() {
	static $regex;
	if ( isset( $regex ) ) {
		return $regex;
	}
	
	$m	= \str_repeat( \RENDER_RX_REPEAT, \RENDER_MAX_DEPTH );
	$regex	= \strtr( \RENDER_RX_MATCH, [ '{repeat}' => $m ] );
	
	return $regex;
}

/**
 *  Process placeholder parameter clusters
 *  
 *  @param string	$tpl		Raw render template
 *  
 *  @example {Lang:label} {Workspace:Collection(id=:id)}
 */
function parseRender( string $tpl ) : array {
	static $parsed = [];
	
	$key	= hash( 'sha256', $tpl );
	
	if ( isset( $parsed[ $key ] ) ) {
		return $parsed[$key];
	}
	
	\preg_match_all( getRenderRegex(), $tpl, $matches );
	$groups	= [];
	
	foreach( \array_chunk( 
		$matches, \RENDER_IDX_ITEM + \RENDER_IDX_SKIP 
	) as $m ) {
		$groups[$m[0]] = $m[\RENDER_IDX_PARAM];
	}
	
	$parsed[$key] = $groups;
	
	return $groups;
}

/**
 *  TODO: Function discovery in templates
 */
/*
function processTemplate( string $tpl ) {
	$groups = parseRender( $tpl );
	
	
}
*/

/**
 *  Meta tag collection rendering helper
 */
function metaTags( array $tags ) : string {
	$out	= '';
	foreach( $tags as $t ) {
		$out	.= \strtr( \TPL_META_TAG ?? '', $t );
	}
	
	return $out;
}

/**
 *  Input form generation
 */


/**
 *  Create select input field from options
 */
function createSelect(
	string		$tpl, 
	array		$vars, 
	array		$opts 
) : string {
	$tpl	= \strtr( $tpl, $vars );
	$out	= '';
	foreach ( $opts as $o ) {
		$out	.= 
		\strtr( \TPL_INPUT_SELECT_OPT ?? '', [
			'{value}'	=> $o[0],
			'{text}'	=> $o[1],
			'{selected}'	=> $o[2] ? 'selected' : ''
		] );
	}
	
	return \strtr( $tpl, [ '{options}' => $out ] );
}

/**
 *  Wrap text template region in 'before' and 'after' event output
 *  
 *  @param string	$before		Before template parsing event
 *  @param string	$after		After template parsing event
 *  @param string	$tpl		Base component template
 *  @param string	$input		Raw component data
 *  
 *  @return string
 */
function hookTplWrap( 
	string		$before, 
	string		$after, 
	string		$tpl, 
	string		$input 
) {
	hook( [ $before, $input ] );
	$tpl = hook( [ $before, '' ] ) . \strtr( $tpl, $input );
	
	hook( [ $after, $tpl ] );
	return hook( [ $after, '' ] );		
}

/**
 *  Wrap multi-param field in 'before' and 'after' event output
 *  
 *  @param string	$before		Before template parsing event
 *  @param string	$after		After template parsing event
 *  @param string	$tpl		Base component template
 *  @param array	$input		Raw component array data
 *  
 *  @return string
 */
function hookArrayWrap( 
	string		$before, 
	string		$after, 
	string		$tpl, 
			$input 
) {
	hook( [ $before, $input ] );
	$tpl = hook( [ $before, '' ] ) . \strtr( $tpl, $input );
	
	hook( [ $after, $tpl ] );
	return hook( [ $after, '' ] );		
}

/**
 *  Create select box and wrap data in 'before' and 'after' event output
 *  
 *  @param string	$before		Before template parsing event
 *  @param string	$after		After template parsing event
 *  @param string	$tpl		Base component template
 *  @param array	$input		Raw select dropdown data
 *  @param array	$opts		Select dropdown options list
 *  
 *  @return string
 */
function hookSelectWrap( 
	string		$before, 
	string		$after, 
	string		$tpl, 
	array		$input, 
	array		$opts 
) {
	hook( [ $before, [ $input, $opts ] ] );
	$tpl = hook( [ $before, '' ] ) . 
		createSelect( $tpl, $input, $opts );
		
	hook( [ $after, $tpl ] );
	return hook( [ $after, '' ] );
}

/**
 *  Tag navigation links
 */
function tagLinks( array $tags ) {
	$out	= '';
	$r	= getRoot();
	foreach( $tags as $t ) {
		$out .= 
		\strtr( 
			\TPL_PAGE_NAV_LINK, 
			[
				'{url}' => $r . 'tags/' . $t['slug'],
				'{text}' => $t['term']
			] 
		);
	}
	
	return \strtr( \TPL_PAGE_TAGWRAP, [ '{text}' => $out ] );
}

/**
 *  Page number navigation link formatting
 */
function navLink( int $page, string $root, string $prefix, int $i ) {
	return 
	\strtr( ( $i == $page ) ? 
		\TPL_PAGE_CURRENT_LINK ?? '' : \TPL_PAGE_NAV_LINK ?? '', [
		'{url}'		=> $root . $prefix . $i,
		'{text}'	=> $i
	] );
}

/**
 *  Previous/Next page link formatting
 */
function npLink(
	string		$root,
	string		$prefix,
	int		$page,
	bool		$previous,
	bool		$none		= false
) {
	if ( $none ) {
		return 
		\strtr( $previous ? 
			\TPL_PAGE_NOPREV ?? '' : \TPL_PAGE_NONEXT ?? '', 
			[ '{url}' => $root . $prefix . $page ] 
		);
	}
	
	return 
	\strtr( $previous ? 
		\TPL_PAGE_PREV_LINK ?? '' : \TPL_PAGE_NEXT_LINK ?? '', 
		[ '{url}' => $root . $prefix . $page ] 
	);
}

/**
 *  First two and last two link display helper
 */
function edgeLink( 
	string		$root, 
	string		$prefix, 
	string		$page1, 
	string		$page2, 
	bool		$first 
) {
	return
	\strtr( $first ? 
		\TPL_PAGE_FIRST2 ?? '' : \TPL_PAGE_LAST2 ?? '', [ 
		'{url1}'	=> $root . $prefix . $page1,
		'{text1}'	=> $page1,
		'{url2}'	=> $root . $prefix . $page2,
		'{text2}'	=> $page2
	] );
}

/**
 *  Create pagination links based on current page and item total
 *  
 *  @param int		$page		Current page
 *  @param int		$total		Total number of items (not pages)
 *  @param int		$limit		Number of items per page
 *  @param string	$root		Page path root E.G. /archives/
 *  @param string	$prefix		E.G. Using "page" = "page2"
 *  @param int		$adj		Number of adacent pages to show,
 *  @param int		$buf		Buffer count before truncating
 */
function pagination(
	int		$page, 
	int		$total, 
	int		$limit, 
	string		$root		= "/",
	string		$prefix		= "-page",
	int		$adj		= 3,
	int		$buf		= 7
)  {
	$prev	= $page - 1;
	$next	= $page + 1;
	$lp	= \ceil( $total / $limit );
	$lpm1	= $lp - 1;
	$adj2	= $adj * 2;
	$adj3	= $adj * 3;
	
	$i	= 0;
	$out	= '';
	
	if ( $lp > 1 ) {
		if ( $page > 1 ) {
			$out .= npLink( $root, $prefix, $prev, true );
		} else {
			$out .= npLink( $root, $prefix, $prev, true, true );
		}
		
		// Number of pages too few, show as-is
		if ( $lp < $buf + $adj2 ) {	
			for ( $i = 1; $i <= $lp; $i++ ) {
				$out .= 
				navLink( $page, $root, $prefix, $i );
			}
		
		// Begin truncate
		} elseif ( $lp >= $buf + $adj2 )	{ 
			// Close to the beginning, only show first few
			if ( $page < 1 + $adj3 ) {
				$ad42	= 4 + $adj2;
				
				for ( $i = 1; $i < $ad42; $i++ ) {
					$out .= 
					navLink( $page, $root, $prefix, $i );
				}
				
				$out .= 
				edgeLink( $root, $prefix, $lpm1, $lp, false );
			
			// Middle, hide first and last group of pages
			} elseif ( $lp - $adj2 > $page && $page > $adj2 ) {
				
				$pma	= $page - $adj;
				$ppa	= $page + $adj;
				$out	.= 
				edgeLink( $root, $prefix, 1, 2, true );
				
				for ( $i = $pma; $i <= $ppa; $i++ ) {
					$out .= 
					navLink( $page, $root, $prefix, $i );
				}
				
				$out .= 
				edgeLink( $root, $prefix, $lpm1, $lp, false );
			
			// End of pagination, hide first pages
			} else {
				$lpma	= $lp - ( 1 + $adj3 );
				$out	.= 
				edgeLink( $root, $prefix, 1, 2, true );
				
				for ( $i = $lpma; $i <= $lp; $i++ ) {
					$out .= 
					navLink( $page, $root, $prefix, $i );
				}
			}
		}
		
		if ( $page < $i - 1 ) {
			$out .= npLink( $root, $prefix, $next, false );
		} else {
			$out .= npLink( $root, $prefix, $next, false, true );
		}
	}
	
	return \strtr( \TPL_PAGINATION ?? '', ['{links}' => $out ] );
}



/**
 *  Create breadcrumb path based on link array
 *  
 *  @param array	$links		Breacrumbs with url and link text
 *  @param array	$params		Optional placeholder replacements
 *  @param bool		$sub		This is a child breadcrumb
 */
function breadcrumbs(
	array		$links,
	array		$params		= [],
	bool		$sub		= false
) : string {
	$out	= '';
	foreach ( $links as $k => $v ) {
		// Last item is the current page in breadcrumbs
		if ( $k === \array_key_last( $links ) ) {
			$out	.= hookArrayWrap( 
					'crumbs_current_before', 
					'crumbs_current_after', 
					\TPL_CRUMB_CURRENT ?? '',
					$v
				);
			// Done
			break;
		}
		
		// Active breadcrumb link
		$out	.= hookArrayWrap( 
				'crumbs_link_before', 
				'crumbs_link_after', 
				\TPL_CRUMB_LINK ?? '',
				$v
			);
	}
	
	if ( $sub ) {
		$out	= hookArrayWrap( 
				'crumbs_sub_before', 
				'crumbs_sub_after', 
				\TPL_SUBBREADCRUMBS ?? '',
				[ '{links}' => $out ]
			);
	} else {
		$out	= hookArrayWrap( 
				'crumbs_before', 
				'crumbs_after', 
				\TPL_BREADCRUMBS ?? '',
				[ '{links}' => $out ]
			);
	}
	
	if ( empty( $params ) ) {
		return $out;
	}
	
	return \strtr( $out, $params );
}

/**
 *  Load and change each placeholder into a key
 */
function loadClasses() {
	$cls	= decode( \DEFAULT_CLASSES );
	$cv	= [];
	foreach( $cls as $k => $v ) {
		$cv['{' . $k . '}'] = bland( $v );
	}
	return $cv;
}

/**
 *  Get or override render store pairs
 */ 
function rsettings( string $area, array $modify = [] ) {
	static $store = [];
	
	if ( !isset( $store[$area] ) ) {
		switch( $area ) {
			case 'classes':
				$store['classes']	= loadClasses();
				break;
				
			case 'styles':
				$store['styles']	= 
				linePresets( 
					'stylesheets', 
					'style_limit', 
					\STYLE_LIMIT, 
					\DEFAULT_STYLESHEETS 
				);
				break;
				
			case 'scripts':
				$store['scripts']	= 
				linePresets( 
					'scripts', 
					'script_limit', 
					\SCRIPT_LIMIT,
					\DEFAULT_SCRIPTS
				);
				break;
			
			case 'meta':
				$store['meta']		= [];
				break;
			
			default:
				$store[$area]	= [];
		}
	}
	
	if ( empty( $modify ) ) {
		return $store[$area];
	}
	
	$store[$area] = 
	\array_unique( \array_merge( $store[$area], $modify ) );
	
	return $store[$area];
}

/**
 *  Get all the CSS classes of the given render segment
 */
function getClasses( string $name ) : array {
	$cls	= rsettings( 'classes' );
	$n	= '{' . bland( $name ) . '}';
	$va	= [];
	foreach( $cls as $k => $v ) {
		if ( 0 != \strcmp( $n , $k ) ) {
			continue;
		}
		$va	= uniqueTerms( $v );
		break;
	}
	
	return $va;
}

/**
 *  Overwrite the CSS class(es) of a render segment
 */
function setClass( string $name, string $value ) {
	rsettings( 
		'classes', 
		[ '{' . bland( $name ) . '}' => bland( $value ) ] 
	);
}

/**
 *  Add a CSS class to render segment
 */
function addClass( string $name, string $value ) {
	$cls	= getClasses( $name );
	$cls[]	= $value;
	
	setClass( $name, \implode( ' ', \array_unique( $cls ) ) );
}

/**
 *  Remove a CSS class from the segment's class list
 */
function removeClass( string $name, string $value ) {
	$cls	= getClasses( $name );
	$cls	= \array_diff( $cls, [ $value ] );
	setClass( $name, \implode( ' ', \array_unique( $cls ) ) );
}

/**
 *  Syndication feed render
 */
function renderFeedTag() {
	return 
	\strtr( \TPL_FEED_TAG, [
		'{title}'	=> '{page_title}',
		'{url}'		=> '{home}feed'
	] );
}

/**
 *  Special tag rendering helper (scripts, links etc...)
 */
function regionTags(
	string		$tpl,
	string		$label,
	string		$tag, 
	string		$region 
) {
	$rg	= rsettings( $region );
	$rgo	= '';
	foreach( $rg as $r ) {
		$rgo .= \strtr( $tag, [ '{url}' => $r ] );
	}
	return \strtr( $tpl, [ $label => $rgo ] );
}

/**
 *  Append values to placeholder terms used in templates
 *  
 *  @param array	$region		Placeholder > value pair
 */
function setRegion( array $region = [] ) {
	static $presets = [];
	
	if ( empty( $region ) ) {
		return $presets;
	}
	
	foreach( $region as $k => $v ) {
		$presets[$k] = ( $presets[$k] ?? '' ) . $v;
	}
}

/**
 *  Apply region preset content to placeolders in the given template
 *  
 *  @param string	$tpl	Page template
 *  @return string
 */
function renderRegions( string $tpl ) : string {	
	
	// Stylesheets, JavaScript, and Meta tags
	$tpl	= 
	regionTags( $tpl, '{stylesheets}', \TPL_STYLE_TAG, 'styles' );
	
	$tpl	= 
	regionTags( $tpl, '{body_js}', \TPL_SCRIPT_TAG, 'scripts' );
	
	$tpl	= 
	regionTags( $tpl, '{meta_tags}', \TPL_META_TAG, 'meta' );
	
	$sa	= config( 'shared_assets', \SHARED_ASSETS );
	$tpl	= \strtr( $tpl, [ '{shared_assets}' => $sa ] );
	
	// Render region content
	$tpl	= \strtr( $tpl, setRegion() );
	
	return strtr( $tpl, [ '{home}' => homeLink() ] );
}

/**
 *  Main navigation link formatter
 *  
 *  @param string	$label		Link region
 *  @param array	$modify		Add/modify URL and link text
 */
function renderNavLinks(
	string		$label,
	array		$modify		= [],
	string		$wrap		= ''
) {
	static $links = [];
	
	// Appending to links?
	if ( !empty( $modify ) && empty( $wrap ) ) {
		$links[$label] = 
			\array_merge( $links[$label], $modify );
		return;
	}
	
	if ( !isset( $links[$label] ) ) {
		switch( $label ) {
			case 'header':
				$wrap		= \TPL_PAGE_MAIN;
				$links[$label]	= 
					decode( \DEFAULT_MAIN_LINKS );
				break;
				
			case 'footer':
				$wrap		= \TPL_PAGE_FOOTER;
				$links[$label]	= 
					decode( \DEFAULT_FOOTER_LINKS );
				break;
			
			// Custom link set	
			default:
				if ( !empty( $modify ) ) {
					$links[$label]['links'] = 
					$modify;
				}
		}
	}
	
	$out = '';
	foreach ( $links[$label]['links'] ?? [] as $k => $v ) {
		$out	.= \strtr( \TPL_PAGE_NAV_LINK, $v );
	}
	
	return 
	\strtr( $wrap, [ '{links}' => $out, '{home}' => homeLink() ] );
}

/**
 *  Format template with classes, assets, and language parameters
 */
function render( string $tpl ) {
	setRegion( [
		'{lang}'		=> \LANGUAGE, 
		
		'{body_before}'		=> \TPL_PAGE_HEADING,
		'{body_after}'		=> renderNavLinks( 'footer' ),
		'{heading_after}'	=> 
			renderNavLinks( 'header' ) . searchForm(), 
		// Currently unused
		'{body_before_lastjs}'	=> '',
		'{body_after_lastjs}'	=> ''
	] );
	$tpl	= \strtr( $tpl, setRegion() );
	$tpl	= parseLang( renderRegions( $tpl ) );
	
	// Finally set classes
	return \strtr( $tpl, rsettings( 'classes' ) );
}


/**
 *  Render settings validator
 */
function checkRenderConfig( string $event, array $hook, array $params ) {
	$filter	= [
		'shared_assets'	=> [
			'filter'=> \FILTER_VALIDATE_URL,
			'options' => [
				'default' => \SHARED_ASSETS
			],
		],
		'style_limit'	=> [
			'filter'	=> \FILTER_VALIDATE_INT,
			'options'	=> [
				'min_range'	=> 1,
				'max_range'	=> 50,
				'default'	=> \STYLE_LIMIT
			]
		],
		'script_limit'	=> [
			'filter'	=> \FILTER_VALIDATE_INT,
			'options'	=> [
				'min_range'	=> 1,
				'max_range'	=> 50,
				'default'	=> \SCRIPT_LIMIT
			]
		]
	];
	
	$data			= 
	\filter_var_array( $params, $filter, false );
	
	return \array_merge( $hook, $data );
}


// Render events

// Check configuration
hook( [ 'checkconfig',	'checkRenderConfig' ] );


