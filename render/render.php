<?php declare( strict_types = 1 );
if ( !defined( 'PATH' ) ) { die(); }
/**
 *  Bare Render: Templating and rendering plugin for Bare
 *  This plugin enables template customization, multi-lingual lables, 
 *  and form input rendering
 *  
 *  This file hould be required first if any others depend on it
 */

/**
 *  Navigation components
 */

// Breadcrumb path wrapper
$templates['tpl_breadcrumbs']	= <<<HTML
<nav class="{crumb_classes}">
<ul class="{crumb_wrap_classes}">{links}</ul>
</nav>
HTML;

// Breadcrumb within content
$templates['tpl_sub_breadcrumbs']	= <<<HTML
<nav class="{crumb_sub_classes}">
<ul class="{crumb_sub_wrap_classes}">{links}</ul>
</nav>
HTML;

// Breadcrumb link
$templates['tpl_crumb_link']		= <<<HTML
<li class="{crumb_item_classes}">
<a href="{url}" class="{crumb_link_classes}">{label}</a>
</li>
HTML;

// Breadcrumb current page
$templates['tpl_crumb_current']		= <<<HTML
<li class="{crumb_current_classes}">
<span class="{crumb_current_item}" title="{url}">{label}</span>
</li>
HTML;

// Pagination current page
$templates['tpl_page_current_link']	= <<<HTML
<li class="{nav_current_classes}"><span class="{nav_current_s_classes}" title="{url}">{text}</span></li>
HTML;


// Pagination next page link
$templates['tpl_page_prev_link']	= <<<HTML
<li class="{nav_prev_classes}"><a href="{url}" class="{nav_prev_a_classes}">Previous</a></li>
HTML;

// No previous page
$templates['tpl_page_noprev']		= <<<HTML
<li class="{nav_noprev_classes}"><span class="{nav_noprev_s_classes}">Previous</span></li>
HTML;

// Pagination previous page link
$templates['tpl_page_next_link']	= <<<HTML
<li class="{nav_next_classes}"><a href="{url}" class="{nav_next_a_classes}">Next</a></li>
HTML;

// No more pages
$templates['tpl_page_nonext']		= <<<HTML
<li class="{nav_nonext_classes}"><span class="{nav_nonext_s_classes}">Next</span></li>
HTML;

// Pagination first two links before skipping next
$templates['tpl_page_first2']		= <<<HTML
	<li class="{nav_first1_classes}"><a href="{url1}" class="{nav_first1_a_classes}">{text1}</a></li>
	<li class="{nav_first2_classes}"><a href="{url2}" class="{nav_first2_a_classes}">{text2}</a></li>
	<li class="{nav_first_s_classes}">...</li>
HTML;


// Pagination last two links after skipping previous
$templates['tpl_page_last2']		= <<<HTML
	<li class="{nav_last_s_classes}">...</li>
	<li class="{nav_last1_classes}"><a href="{url1}" class="{nav_last1_a_classes}">{text1}</a></li>
	<li class="{nav_last2_classes}"><a href="{url2}" class="{nav_last2_a_classes}">{text2}</a></li>
HTML;



// General list for E.G. "Related", "New", "More" etc...
$templates['tpl_page_list']		= <<<HTML
<div class="{list_wrap_classes}">
	<h3 class="list_h_classes">{heading}</h3>
	<nav class="{list_classes}"><ul>{links}</ul></nav>
</div>
HTML;


// Pagination wrapper
$templates['tpl_pagination']		= <<<HTML
<div class="{pagination_wrap_classes}">
<nav class="{pagination_classes}">
<ul class="{pagination_ul_classes}">{links}</ul>
</nav>
</div>
HTML;


/**
 *  User input form building blocks
 */

// Select box option
$templates['tpl_input_select_opt']	= <<<HTML
<option value="{value}" {selected}>{text}</option>
HTML;

// Select dropdown
$templates['tpl_input_select']		= <<<HTML
<label for="{id}" class="{label_classes}">{label} 
	<span class="{special_classes}">{special}</span></label> 
<select id="{id}" name="{name}" aria-describedby="{id}-desc"
	class="{input_classes}" {extra}>
	<option value=""> - </option>{options}</select>
<small id="{id}-desc" class="{desc_classes}" {desc_extra}>{desc}</small>
HTML;

// Text field input
$templates['tpl_input_text']		= <<<HTML
<label for="{id}" class="{label_classes}">{label} 
	<span class="{special_classes}">{special}</span></label>
<input id="{id}" name="{name}" type="text" aria-describedby="{id}-desc"
	class="{input_classes}" value="{value}" {extra}>
<small id="{id}-desc" class="{desc_classes}" {desc_extra}>{desc}</small>
HTML;


// Search field input
$templates['tpl_input_search']		= <<<HTML
<label for="{id}" class="{label_classes}">{label} 
	<span class="{special_classes}">{special}</span></label>
<input id="{id}" name="{name}" type="search" aria-describedby="{id}-desc"
	class="{input_classes}" value="{value}" {extra}>
<small id="{id}-desc" class="{desc_classes}" {desc_extra}>{desc}</small>
HTML;


// Datetime field input
$templates['tpl_input_datetime']	= <<<HTML
<label for="{id}" class="{label_classes}">{label} 
	<span class="{special_classes}">{special}</span></label>
<input id="{id}" name="{name}" type="datetime-local" aria-describedby="{id}-desc"
	class="{input_classes}" value="{value}" {extra}>
<small id="{id}-desc" class="{desc_classes}" {desc_extra}>{desc}</small>
HTML;


// Email field input
$templates['tpl_input_email']		= <<<HTML
<label for="{id}" class="f6 b db mb2">{label} 
	<span class="{special_classes">{special}</span></label>
<input id="{id}" name="{name}" type="email" aria-describedby="{id}-desc"
	class="{input_classes}" value="{value}" {extra}>
<small id="{id}-desc" class="{desc_classes}" {desc_extra}>{desc}</small>
HTML;


// Password field input
$templates['tpl_input_pass']		= <<<HTML
<label for="{id}" class="{label_classes}">{label} 
	<span class="{special_classes}">{special}</span></label> 
<input id="{id}" name="{name}" type="password" aria-describedby="{id}-desc"
	class="{input_classes}" {extra}>
<small id="{id}-desc" class="{desc_classes}" {desc_extra}>{desc}</small>
HTML;


// Multiline text block content input
$templates['tpl_input_multiline']	= <<<HTML
<label for="{id}" class="{label_classes}">{label} 
	<span class="{special_classes}">{special}</span></label> 
<textarea id="{id}" name="{name}" aria-describedby="{id}-desc" 
	class="{input_classes}" {extra}>{value}</textarea>
<small id="{id}-desc" class="{desc_classes}" {desc_extra}>{desc}</small>
HTML;

// Checkbox input
$templates['tpl_input_checkbox']	= <<<HTML
<label for="{id}" class="{label_classes}">{label} 
	<span class="{special_classes}">{special}</span></label>
<input id="{id}" name="{name}" value="{value}" type="checkbox"
		class="{input_classes}" aria-describedby="{id}-desc">
	<small id="{id}-desc" class="{desc_classes}" {desc_extra}>{desc}</small>
HTML;


// Upload input
$templates['tpl_input_upload']		= <<<HTML
<label for="{id}" class="{label_classes}">{label} 
	<span class="{special_classes}">{special}</span></label>
<input id="{id}" name="{name}" type="file" class="{input_classes}" 
	aria-describedby="{id}-desc" {extra}>
	<small id="{id}-desc" class="{desc_classes}" {desc_extra}>{desc}</small>
HTML;

// Upload input no description
$templates['tpl_input_upload_nd']	= <<<HTML
<label for="{id}" class="{label_classes}">{label} 
	<span class="{special_classes}">{special}</span></label>
<input id="{id}" name="{name}" type="file" class="{input_classes}" 
	aria-describedby="{id}-desc" {extra}>
HTML;


/**
 *  Special inputs with label after input field
 */

// Text field input
$templates['tpl_input_text_se']		= <<<HTML
<input id="{id}" name="{name}" type="text" aria-describedby="{id}-desc"
	class="{input_classes}" value="{value}" {extra}>
<label for="{id}" class="{label_classes}">{label} 
	<span class="{special_classes}">{special}</span></label>
<small id="{id}-desc" class="{desc_classes}" {desc_extra}>{desc}</small>
HTML;


// Password field input
$templates['tpl_input_pass_se']		= <<<HTML
<input id="{id}" name="{name}" type="password" aria-describedby="{id}-desc"
	class="{input_classes}" {extra}>
<label for="{id}" class="{label_classes}">{label} 
	<span class="{special_classes}">{special}</span></label> 
<small id="{id}-desc" class="{desc_classes}" {desc_extra}>{desc}</small>
HTML;


// Email field input
$templates['tpl_input_email_se']	= <<<HTML
<input id="{id}" name="{name}" type="email" aria-describedby="{id}-desc"
	class="{input_classes}" value="{value}" {extra}>
<label for="{id}" class="f6 b db mb2">{label} 
	<span class="{special_classes">{special}</span></label>
<small id="{id}-desc" class="{desc_classes}" {desc_extra}>{desc}</small>
HTML;

// Multiline text block content input
$templates['tpl_input_multiline_se']	= <<<HTML
<textarea id="{id}" name="{name}" aria-describedby="{id}-desc" 
	class="{input_classes}" {extra}>{value}</textarea>
<label for="{id}" class="{label_classes}">{label} 
	<span class="{special_classes}">{special}</span></label> 
<small id="{id}-desc" class="{desc_classes}" {desc_extra}>{desc}</small>
HTML;

// Upload input
$templates['tpl_input_upload_se']	= <<<HTML
<input id="{id}" name="{name}" type="file" class="{input_classes}" 
	aria-describedby="{id}-desc" {extra}>
<label for="{id}" class="{label_classes}">{label} 
	<span class="{special_classes}">{special}</span></label>
	<small id="{id}-desc" class="{desc_classes}" {desc_extra}>{desc}</small>
HTML;

// Upload input no description
$templates['tpl_input_text_nd_se']	= <<<HTML
<input id="{id}" name="{name}" type="file" class="{input_classes}" 
	aria-describedby="{id}-desc" {extra}>
<label for="{id}" class="{label_classes}">{label} 
	<span class="{special_classes}">{special}</span></label>
HTML;



// Post button
$templates['tpl_input_submit']		= <<<HTML
<input type="submit" id="{id}" name="{name}" value="{value}" 
	class="{submit_classes}" {extra}>
HTML;

// Alternate submit (E.G. Save draft, search)
$templates['tpl_input_submit_alt']	= <<<HTML
<input type="submit" id="{id}" name="{name}" value="{value}" 
	class="{alt_classes}" {extra}>
HTML;

// Critical submit (E.G. Delete)
$templates['tpl_input_submit_warn']	= <<<HTML
<input type="submit" name="{name}" value="{value}" 
	class="{warn_classes}" {extra}>
HTML;

// Action submit (E.G. Sort)
$templates['tpl_input_submit_action']	= <<<HTML
<input type="submit" name="{name}" value="{value}" 
	class="{action_classes}" {extra}>
HTML;

// Generic block input form
$templates['tpl_form_block']		= <<<HTML
<form id="{id}" action="{action}" method="{method}" enctype="{enctype}" 
	class="{form_classes}" {extra}>{fields}</form>
HTML;

// Generic inline form
$templates['tpl_form']			= <<<HTML
<form id="{form_classes}" method="{method}" action="{action}" 
	enctype="{enctype}" accept-charset="UTF-8" {extra}>{fields}</form>
HTML;


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
	static $parsed	= [];
	$key		= \hash( 'sha1', $tpl );
	
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
	$tpl	= render( $tpl, $vars );
	$out	= '';
	foreach ( $opts as $o ) {
		$out	.= 
		render( template( 'tpl_input_select_opt' ), 
		[
			'value'		=> $o[0],
			'text'		=> $o[1],
			'selected'	=> $o[2] ? 'selected' : ''
		] );
	}
	
	return render( $tpl, [ 'options' => $out ] );
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
	return 
	hookWrap( 
		$before, 
		$after, 
		createSelect( $tpl, $input, $opts ),
		$input	
	);
}


/**
 *  Page number navigation link formatting
 */
function navLink( int $page, string $root, string $prefix, int $i ) {
	$tpl = ( $i == $page ) ? 
	template( 'tpl_page_current_link' ) : 
	template( 'tpl_page_nav_link' );
	
	return 
	render( $tpl, [
		'url'	=> $root . $prefix . $i,
		'text'	=> $i
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
		render( $previous ? 
			template( 'tpl_page_noprev' ) : 
			template( 'tpl_page_nonext' ), 
			[ 'url' => $root . $prefix . $page ] 
		);
	}
	
	return 
	render( $previous ? 
		template( 'tpl_page_prev_link' ) : 
		template( 'tpl_page_next_link' ), 
		[ 'url' => $root . $prefix . $page ] 
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
	render( $first ? 
		template( 'tpl_page_first2' ) : 
		template( 'tpl_page_last2' ), [ 
		'url1'	=> $root . $prefix . $page1,
		'text1'	=> $page1,
		'url2'	=> $root . $prefix . $page2,
		'text2'	=> $page2
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
	
	return 
	render( template( 'tpl_pagination' ), ['links' => $out ] );
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
			$out	.= 
			hookWrap( 
				'crumbscurrentbefore', 
				'crumbscurrentafter', 
				template( 'tpl_crumb_current' ),
				$v
			);
			// Done
			break;
		}
		
		// Active breadcrumb link
		$out	.= 
		hookWrap( 
			'crumbslinkbefore', 
			'crumbslinkafter', 
			template( 'tpl_crumb_link' ),
			$v
		);
	}
	
	if ( $sub ) {
		$out	= 
		hookWrap( 
			'crumbssubbefore', 
			'crumbssubafter', 
			template( 'tpl_sub_breadcrumbs' ),
			[ 'links' => $out ]
		);
	} else {
		$out	= 
		hookWrap( 
			'crumbsbefore', 
			'crumbsafter', 
			template( 'tpl_breadcrumbs' ),
			[ 'links' => $out ]
		);
	}
	
	if ( empty( $params ) ) {
		return $out;
	}
	
	return render( $out, $params );
}
