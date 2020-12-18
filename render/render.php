<?php declare( strict_types = 1 );
if ( !defined( 'PATH' ) ) { die(); }
/**
 *  Bare Render: This plugin enables additional template customization, 
 *  multi-lingual lables, numeric pagination, and form input rendering
 *  
 *  Add this plugin to 'plugins_enabled' before the templates plugin if using both
 */


/**
 * Base templates for building a page from scratch
 */
$templates['tpl_skeleton']		= <<<HTML
<!DOCTYPE html>
<html lang="{lang}">
<head>
	{head}
</head>
<body class="{body_classes}" {extra}>
	{body}
</body>
</html>
HTML;

$templates['tpl_skeleton_title']	= <<<HTML
<title>{title}</title>
HTML;

$templates['tpl_rel_tag']		=<<<HTML
<link rel="{rel}" type="{type}" title="{title}" href="{url}">
HTML;

// Rel tag without type or titlte
$templates['tpl_rel_tag_nt']		='<link rel="{rel}" href="{url}">';

// These are also in index.php, but these can be modified by plugins
$templates['tpl_style_tag']		= '<link rel="stylesheet" href="{url}">';
$templates['tpl_meta_tag']		= '<meta name="{name}" content="{content}">';
$templates['tpl_script_tag']		= '<script src="{url}"></script>';

// Template fragments
$templates['tpl_anchor']		= '<a href="{url}">{text}</a>';
$templates['tpl_para']			= '<p {extra}>{html}</p>';
$templates['tpl_span']			= '<span {extra}>{html}</span>';
$templates['tpl_div']			= '<div {extra}>{html}</div>';
$templates['tpl_main']			= '<main {extra}>{html}</main>';
$templates['tpl_article']		= '<article {extra}>{html}</article>';
$templates['tpl_header']		= '<header {extra}>{html}</header>';
$templates['tpl_aside']			= '<aside {extra}>{html}</aside>';
$templates['tpl_footer']		= '<footer {extra}>{html}</footer>';

// data-reference HTML 5 attribute, E.G. <span data-rel="author">
$templates['tpl_data_pfx']		= 'data-{term}="{value}"';

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
{input_before}{input_select_before}
<label for="{id}" class="{label_classes}">{label} 
	<span class="{special_classes}">{special}</span></label> 
<select id="{id}" name="{name}" aria-describedby="{id}-desc"
	class="{input_classes}" {required}{extra}>
	{unselect_option}{options}</select>
<small id="{id}-desc" class="{desc_classes}" {desc_extra}>{desc}</small>
{input_input_after}{input_after}
HTML;

// Unselected dropdown option
$templates['tpl_form_unselect']	=<<<HTML
<option value="">--</option>
HTML;

// Text field input
$templates['tpl_input_text']		= <<<HTML
{input_before}{input_text_before}
<label for="{id}" class="{label_classes}">{label} 
	<span class="{special_classes}">{special}</span></label>
<input id="{id}" name="{name}" type="text" aria-describedby="{id}-desc"
	class="{input_classes}" value="{value}" {required}{extra}>
<small id="{id}-desc" class="{desc_classes}" {desc_extra}>{desc}</small>
{input_text_after}{input_after}
HTML;


// Search field input
$templates['tpl_input_search']		= <<<HTML
{input_before}{input_search_before}
<label for="{id}" class="{label_classes}">{label} 
	<span class="{special_classes}">{special}</span></label>
<input id="{id}" name="{name}" type="search" aria-describedby="{id}-desc"
	class="{input_classes}" value="{value}" {required}{extra}>
<small id="{id}-desc" class="{desc_classes}" {desc_extra}>{desc}</small>
{input_search_after}{input_after}
HTML;


// Datetime field input
$templates['tpl_input_datetime']	= <<<HTML
{input_before}{input_datetime_before}
<label for="{id}" class="{label_classes}">{label} 
	<span class="{special_classes}">{special}</span></label>
<input id="{id}" name="{name}" type="datetime-local" aria-describedby="{id}-desc"
	class="{input_classes}" value="{value}" {required}{extra}>
<small id="{id}-desc" class="{desc_classes}" {desc_extra}>{desc}</small>
{input_datetime_after}{input_after}
HTML;


// Email field input
$templates['tpl_input_email']		= <<<HTML
{input_before}{input_email_before}
<label for="{id}" class="f6 b db mb2">{label} 
	<span class="{special_classes">{special}</span></label>
<input id="{id}" name="{name}" type="email" aria-describedby="{id}-desc"
	class="{input_classes}" value="{value}" {required}{extra}>
<small id="{id}-desc" class="{desc_classes}" {desc_extra}>{desc}</small>
{input_email_after}{input_after}
HTML;


// Password field input
$templates['tpl_input_pass']		= <<<HTML
{input_before}{input_pass_before}
<label for="{id}" class="{label_classes}">{label} 
	<span class="{special_classes}">{special}</span></label> 
<input id="{id}" name="{name}" type="password" aria-describedby="{id}-desc"
	class="{input_classes}" {required}{extra}>
<small id="{id}-desc" class="{desc_classes}" {desc_extra}>{desc}</small>
{input_pass_after}{input_after}
HTML;


// Multiline text block content input
$templates['tpl_input_multiline']	= <<<HTML
{input_before}{input_multiline_before}
<label for="{id}" class="{label_classes}">{label} 
	<span class="{special_classes}">{special}</span></label> 
<textarea id="{id}" name="{name}" aria-describedby="{id}-desc" 
	class="{input_classes}" {required}{extra}>{value}</textarea>
<small id="{id}-desc" class="{desc_classes}" {desc_extra}>{desc}</small>
{input_multiline_after}{input_after}
HTML;

// Checkbox input
$templates['tpl_input_checkbox']	= <<<HTML
{input_before}{input_checkbox_before}
<label for="{id}" class="{label_classes}">{label} 
	<span class="{special_classes}">{special}</span></label>
<input id="{id}" name="{name}" value="{value}" type="checkbox"
		class="{input_classes}" aria-describedby="{id}-desc"
		{required}{extra}>
	<small id="{id}-desc" class="{desc_classes}" {desc_extra}>{desc}</small>
{input_checkbox_after}{input_after}
HTML;


// Upload input
$templates['tpl_input_upload']		= <<<HTML
{input_before}{input_upload_before}
<label for="{id}" class="{label_classes}">{label} 
	<span class="{special_classes}">{special}</span></label>
<input id="{id}" name="{name}" type="file" class="{input_classes}" 
	aria-describedby="{id}-desc" {required}{extra}>
	<small id="{id}-desc" class="{desc_classes}" {desc_extra}>{desc}</small>
{input_upload_after}{input_after}
HTML;

// Upload input no description
$templates['tpl_input_upload_nd']	= <<<HTML
{input_before}{input_upload_before}
<label for="{id}" class="{label_classes}">{label} 
	<span class="{special_classes}">{special}</span></label>
<input id="{id}" name="{name}" type="file" class="{input_classes}" 
	aria-describedby="{id}-desc" 
	{required}{extra}>{input_upload_after}{input_after}
HTML;


/**
 *  Special inputs with label after input field
 */

// Text field input
$templates['tpl_input_text_se']		= <<<HTML
{input_before}{input_text_before}
<input id="{id}" name="{name}" type="text" aria-describedby="{id}-desc"
	class="{input_classes}" value="{value}" {required}{extra}>
<label for="{id}" class="{label_classes}">{label} 
	<span class="{special_classes}">{special}</span></label>
<small id="{id}-desc" class="{desc_classes}" {desc_extra}>{desc}</small>
{input_text_after}{input_after}
HTML;


// Password field input
$templates['tpl_input_pass_se']		= <<<HTML
{input_before}{input_pass_before}
<input id="{id}" name="{name}" type="password" aria-describedby="{id}-desc"
	class="{input_classes}" {required}{extra}>
<label for="{id}" class="{label_classes}">{label} 
	<span class="{special_classes}">{special}</span></label> 
<small id="{id}-desc" class="{desc_classes}" {desc_extra}>{desc}</small>
{input_pass_after}{input_after}
HTML;


// Email field input
$templates['tpl_input_email_se']	= <<<HTML
{input_before}{input_email_before}
<input id="{id}" name="{name}" type="email" aria-describedby="{id}-desc"
	class="{input_classes}" value="{value}" {required}{extra}>
<label for="{id}" class="f6 b db mb2">{label} 
	<span class="{special_classes">{special}</span></label>
<small id="{id}-desc" class="{desc_classes}" {desc_extra}>{desc}</small>
{input_email_after}{input_after}
HTML;

// Multiline text block content input
$templates['tpl_input_multiline_se']	= <<<HTML
{input_before}{input_multiline_before}
<textarea id="{id}" name="{name}" aria-describedby="{id}-desc" 
	class="{input_classes}" {required}{extra}>{value}</textarea>
<label for="{id}" class="{label_classes}">{label} 
	<span class="{special_classes}">{special}</span></label> 
<small id="{id}-desc" class="{desc_classes}" {desc_extra}>{desc}</small>
{input_multiline_after}{input_after}
HTML;

// Upload input
$templates['tpl_input_upload_se']	= <<<HTML
{input_before}{input_upload_before}
<input id="{id}" name="{name}" type="file" class="{input_classes}" 
	aria-describedby="{id}-desc" {required}{extra}>
<label for="{id}" class="{label_classes}">{label} 
	<span class="{special_classes}">{special}</span></label>
	<small id="{id}-desc" class="{desc_classes}" {desc_extra}>{desc}</small>
{input_upload_after}{input_after}
HTML;

// Upload input no description
$templates['tpl_input_text_nd_se']	= <<<HTML
{input_before}{input_text_before}
<input id="{id}" name="{name}" type="file" class="{input_classes}" 
	aria-describedby="{id}-desc" {required}{extra}>
<label for="{id}" class="{label_classes}">{label} 
	<span class="{special_classes}">{special}</span></label>
{input_text_after}{input_after}
HTML;


$templates['tpl_id_field']	=<<<HTML
<input type="hidden" name="id" value="{id}">
HTML;


// Post button
$templates['tpl_input_submit']		= <<<HTML
{input_before}{input_submit_before}<input type="submit" id="{id}" 
	name="{name}" value="{value}" class="{submit_classes}" 
	{extra}>{input_submit_after}{input_after}
HTML;

// Alternate submit (E.G. Save draft, search)
$templates['tpl_input_submit_alt']	= <<<HTML
{input_before}{input_submit_before}{input_submit_alt_before}<input 
	type="submit" id="{id}" name="{name}" value="{value}" class="{alt_classes}" 
	{extra}>{input_submit_after}{input_submit_alt_after}{input_after}
HTML;

// Critical submit (E.G. Delete)
$templates['tpl_input_submit_warn']	= <<<HTML
{input_before}{input_warn_before}<input type="submit" name="{name}" 
	value="{value}" class="{warn_classes}" 
	{extra}>{input_warn_after}{input_after}
HTML;

// Action submit (E.G. Sort)
$templates['tpl_input_submit_action']	= <<<HTML
{input_before}{input_action_before}<input type="submit" name="{name}" 
	value="{value}" class="{action_classes}" 
	{extra}>{input_action_after}{input_after}
HTML;

// Generic block input form
$templates['tpl_form_block']		= <<<HTML
{form_before}{form_block_before}
<form id="{id}" action="{action}" method="{method}" enctype="{enctype}" 
	class="{form_classes}" 
	{extra}>{form_input_before}{fields}{form_input_after}</form>
{form_block_after}{form_after}
HTML;

// Generic inline form
$templates['tpl_form']			= <<<HTML
{form_before}{form_inline_before}
<form id="{form_classes}" method="{method}" action="{action}" 
	enctype="{enctype}" accept-charset="UTF-8" 
	{extra}>{form_input_before}{fields}{form_input_after}</form>
{form_inline_after}{form_after}
HTML;

// Form fieldset wrap
$templates['tpl_form_fieldset']		=<<<HTML
{input_fieldset_before}<fieldset 
	class="{fieldset_classes}">{input}</fieldset>{input_fieldset_after}
HTML;

// Form field input wrap
$templates['tpl_form_input_wrap']	=<<<HTML
{input_wrap_before}<p class="{input_wrap_classes}">{input}</p>{input_wrap_after}
HTML;


/**********************************************************************
 *                      Caution editing below
 **********************************************************************/


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
	
	$mxd	= config( 'render_max_dpth', \RENDER_MAX_DEPTH, 'int' );
	$m	= \str_repeat( \RENDER_RX_REPEAT, $mxd );
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
	
	$rii = config( 'render_idx_item', \RENDER_IDX_ITEM, 'int' );
	$ris = config( 'render_idx_skip', \RENDER_IDX_SKIP, 'int' );
	$rip = config( 'render_idx_param', \RENDER_IDX_PARAM, 'int' );
	$mrc = \array_chunk( $matches, $rii + $ris );
	foreach ( $mrc as $m ) {
		$groups[$m[0]] = $m[$rip];
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
	string		$prefix		= "page",
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




/**
 *  Render settings validator
 */
function checkRenderConfig( string $event, array $hook, array $params ) {
	$filter	= [
		'render_max_dpth'	=> [
			'filter'	=> \FILTER_VALIDATE_INT,
			'options'	=> [
				'min_range'	=> 1,
				'max_range'	=> 50,
				'default'	=> \RENDER_MAX_DEPTH
			]
		],
		'render_idx_item'	=> [
			'filter'	=> \FILTER_VALIDATE_INT,
			'options'	=> [
				'min_range'	=> 1,
				'max_range'	=> 20,
				'default'	=> \RENDER_IDX_ITEM
			]
		],
		'render_idx_param'	=> [
			'filter'	=> \FILTER_VALIDATE_INT,
			'options'	=> [
				'min_range'	=> 1,
				'max_range'	=> 20,
				'default'	=> \RENDER_IDX_PARAM
			]
		],
		'render_idx_skip'	=> [
			'filter'	=> \FILTER_VALIDATE_INT,
			'options'	=> [
				'min_range'	=> 1,
				'max_range'	=> 20,
				'default'	=> \RENDER_IDX_SKIP
			]
		]
	];
	
	return 
	\array_merge( $hook, \filter_var_array( $params, $filter ) );
}


// Render events

// Check configuration
hook( [ 'checkconfig',	'checkRenderConfig' ] );
