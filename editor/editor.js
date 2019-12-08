"use strict";


(function() {

/**
 *  Helpers
 */
// Get element by id
function byId( name ) {
	return document.getElementById( name );
}
	
// Create a new element
function create( name ) {
	return document.createElement( name );
}
	
// Set element attribute
function attr( e, name, value, remove ) {
	remove = remove || false;
	if ( remove ) {
		e.removeAttribute( name );
		return;
	}
	e.setAttribute( name, value );
}

// Get element attribute with optional default value
function getAttr( e, n, v ) {
	return e.getAttribute( n ) || v || '';
}

// Query the DOM or a specific parent node for elements
function find( s, e, n ) {
	e = e || document;
	const q = e.querySelectorAll(s);
	if ( n && q.length ) {
		return q[0];
	}
	return q;
}

// Attach event listener
function listen( target, events, func, capture ) {
	const
	val	= events.split( ',' ).map( e => e.trim() ),
	len	= val.length;
	
	capture = capture || false;
	
	for ( let i = 0; i < len; i++ ) {
		target.addEventListener( val[i], func, capture );
	}
}

// Element is of input type
function isA( target, types ) {
	const
	typ = types.split( ',' ).map( e => e.trim() ),
	len = typ.length;
	
	for ( let i = 0; i < len; i++ ) {
		if ( typ[i].toUpperCase() == target.nodeName ) {
			return true;
		}
	}
	
	return false;
}

// Get text until last occurence of a character
function textUntil( txt, ch ) {
	if ( txt.lastIndexOf( ch ) == ( txt.length - 1 ) ) {
		return '';
	}
	
	return txt.substring( 
		txt.lastIndexOf( ch ), ( txt.length - 1 )
	).trim();
}

// Get text from the first occurence of a character
function textFrom( txt, ch ) {
	if ( ( txt.indexOf( ch ) + 1 ) > ( txt.length - 1 ) ) {
		return ''
	}
	return txt.substring( 
		( txt.indexOf( ch ) + 1 ), 
		( txt.length - 1 ) 
	);
}

function cutUntil( txt, ch ) {
	if ( txt.lastIndexOf( ch ) == ( txt.length - 1 ) ) {
		return '';
	}
	
	return txt.substring( 0, txt.lastIndexOf( ch ) ).trim();
}

// Remove last n chracters from string
function cut( str, n ) {
	return str.substring( 0, str.length - n );
}

// Trim text to given length
function trunc( str, n ) {
	return ( str.length > n ) ? 
		str.substring( 0, n ) : str;
}

// Check if text had been selected
function checkSelect( box ) {
	const sel = selection( box );
	if ( '' != sel.range ) {
		box.selected = true;
		return;
	}
	box.selected = false;
}

// Apply classes separated by spaces
function applyClasses( box, cl ) {
	cl.split( ' ' ).map( c => {
		box.classList.add( c );
	} );
}

// Update cursor position
function updateLastPos( box ) {
	box.last_start	= box.selectionStart;
	box.last_end	= box.selectionEnd;
	
	// Check for selection info
	checkSelect( box );
}

// Selected text range
function selection( box ) {
	const v = ( box.value ) ? box.value : box.innerHTML;
	return {
		"start"		: box.selectionStart,
		"end"		: box.selectionEnd,
		"range"		: ( v == '' ) ? '' : v.substring( 
					box.selectionStart, 
					box.selectionEnd 
				).trim()
	};
}

// Computed style with important properties
function style( box ) {
	const 
	st	= window.getComputedStyle( box ),
	rc	= box.getBoundingClientRect();
	
	return { 
		"x"			: rc.x,  
		"y"			: rc.y, 
		"top"			: rc.top, 
		"left"			: rc.left, 
		"right"			: rc.right, 
		"bottom"		: rc.bottom, 
		"width"			: rc.width,  
		"height"		: rc.height,
		
		"display"		: st.display,
		
		"lineHeight"		: cut( st.lineHeight, 2 ),
		"fontSize"		: cut( st.fontSize, 2 ),
		"origin"		: st.transformOrigin,
		"insize"		: cut( st.inlineSize, 2 ),
		"block"			: cut( st.blockSize, 2 ),
		
		"marginTop"		: cut( st.marginTop, 2 ),
		"marginBottom"		: cut( st.marginBottom, 2 ),
		
		"paddingTop"		: cut( st.paddingTop, 2 ),
		"paddingBottom"		: cut( st.paddingBottom, 2 ),
		"paddingLeft"		: cut( st.paddingLeft, 2 ),
		"paddingRight"		: cut( st.paddingRight, 2 ),
		
		"paddingInlineStart"	: cut( st.paddingInlineStart, 2 ),
		"paddingInlineEnd"	: cut( st.paddingInlineEnd, 2 )
	};
}

// Check if given key is a printable character
function printable( e ) {
	const key	=  e.keyCode || e.charCode || e.which;
	return ( key > 47 && key < 58 )		|| 
		( key == 32 || key == 13 )	||
		( key > 64 && key < 91 )	|| 
		( key > 95 && key < 122 )	|| 
		( key > 185 && key < 193 )	|| 
		( key > 218 && key < 223 ); 
}

// Get passed parameter options
function getOptions( opts ) {
	opts		= opts || '';
	const params	= {};
	
	// Nothing set
	if ( !opts.length ) {
		return params;
	}
	
	// Find parameter values
	opts.split( '&' ).filter( function( c ) {
		const p = c.split( '=' ).map( e => e.trim() );
		params[decodeURIComponent(p[0])] = 
			decodeURIComponent( 
				( p[1] || '' ).replace( /\+/g, '%20' )
			);	
	} );
	return params;
}

// Separate multiple named items in a single parameter
function getNames( opts ) {
	// No names?
	if ( !opts ) { return {}; }
	
	return opts.split( ';' ).map( e => e.trim() );
}

// Get start and end positions between delimiter
function getExcerpt( box, ch ) {
	const
	sel	= selection( box ),
	st	= box.value.lastIndexOf( ch, sel.start ),
	ed	= box.value.indexOf( ch, sel.end );
	
	return { 
		"start"	: st < 0 ? 0 : st, 
		"end"	: ed < 0 ? 0 : ed
	};
}

// Return unique items with empty items removed
function uniqueWords( words, ch1, ch2, max ) {
	// Use split character to make array. Remove empty/whitespace
	const ar	= 
	words.split( ch1 )
		.map( e => e.trim() )
		.filter( e => /\S/.test( e ) )
		.filter( e => function( v ) {
			return 
		} );
	
	// Remove duplicates and use join character to combine words
	return ar.filter( function( v, i, s ) {
		return i === s.indexOf( v );
	} ).join( ch2 );
}

function getClipboard( e ) {
	if ( e.clipboardData ) {
		return e.clipboardData.getData( 'text/plain' );
	}
	
	if ( window.clipboardData ) {
		return window.clipboardData.getData( 'Text' );
	}
	return '';
}




/**
 *  Feature helpers
 */

// Convert title text to slug
function makeSlug( sx, v, m ) {
	v		= 
	v.toLowerCase()
		// Remove non-letters
		// Firefox fallback (to be removed in the future)
		.replace( /[\u0300-\u036F]/g, '' ) 
		.replace( /[\u2000-\u206F\u2E00-\u2E7F]/g, '' )
		
		// Normalize and remove punctuation. Firefox fallback
		.normalize( 'NFKD' )
		.replace( /[\'〝〟‘’“”『』「」〈〉《》【】（）]/g, '' )
		.replace( /[\.,\/\\#!$%\^&\*;\:{}=\_\"`~()\[\]\+\|<>\?]/g, ' ' )
		
		//.replace( /^\p{L}+/u, '' ) (Chrome, Safari)
		.replace( /^\s+|\s+$/g, '' )
		.replace( / +/g,'-' )
		.replace( /-+/g,'-' )
		.replace( /-+$/, '' )
		.replace( /^-+/, '' );
	
	sx.value	= trunc( v, m ).replace( /^\-+|-\-+$/g, '' );
}

// Auto-adjust textarea height
function resize( txt ) {
	txt.style.resize	= 'none';
	
	// Reset
	txt.style.height	= 'auto';
	txt.style.height	= txt.scrollHeight + 3 + 'px';
}

// Insert or replace currently clicked tag
function insertTag( box, tag ) {
	var c;
	
	const
	v = box.value,
	t = getExcerpt( box, ',' );
	
	// There's a current tag clicked? 
	if ( t.end ) {
		var
		ts	= v.substring( 0, t.start ),
		te	= v.substring( t.end );
		
		// Replace current tag with new word
		c	= ts.trim() + ',' + tag + ',' + te.trim();
		
	// Or append new tag to end
	} else {
		c = cutUntil( v, ',' ) + ',' + tag.trim();
	}
	
	// Remove any extra commas, extra spaces, duplicates etc...
	box.value	= uniqueWords( c, ',', ', ' );
}

// Generate a request token hash based on the current browser
// Enables simple early XSRF rejection before more in-depth checks
function requestHash( token ) {
	var sh	= 0;
	
	// This property should match User-Agent sent in headers
	const 
	ua	= ( navigator.userAgent || '' ) + token;
	
	// This should match the server-side algorithm
	for ( let i = 0; i < ua.length; i++ ) {
		sh = ( sh * 31 ) + ua.charCodeAt(i);
		sh |= 0;
	}
	
	return btoa( '' + sh );
}

// Find or create the xhr hidden input
function xhrField( f ) {
	const a = find( 'input[name="xhr"]', f );
	if ( a.length ) {
		 return a[0];
	}
	const x = create( 'input' );
	attr( x, 'type', 'hidden' );
	attr( x, 'name', 'xhr' );
	f.appendChild( x );
	
	return x;
}

// xhr compabtible form field with nonce and token
function coreFields( f ) {
	// Get parent form with token and nonce fields
	const
	fields	= {
		"token" : find( 'input[name="token"]', f )[0],
		"nonce" : find( 'input[name="nonce"]', f )[0],
		"xhr"	: xhrField( f )
	};
	
	// Set xhr code based on current token
	attr( fields.xhr, 'value', requestHash( fields.token.value ) );
	
	return fields;
}

// Apply new token and nonce values with response
function resetCoreFields( fields, response ) {
	fields.token.value	= response.token;
	fields.nonce.value	= response.nonce;
}

// Create AJAX request
function request( form, path ) {
	const
	xhr	= new XMLHttpRequest(),
	method	= getAttr( form, 'method', 'POST' ),
	accept	= getAttr( form, 'accept-charset', 'UTF-8' );
	enctype	= getAttr( form, 'enctype', 
		'application/x-www-form-urlencoded' );
	
	
	path	= path || getAttr( form, 'action', '/' );
	xhr.open( method.toUpperCase(), path, true );
	
	// Change headers
	xhr.setRequestHeader( 'Content-Type', enctype );
	xhr.setRequestHeader( 'Accept-Charset', 'charset=' + accept );
	xhr.setRequestHeader( 'X-Requested-With', 'XMLHttpRequest' );
	
	return xhr;
}

// Change current page url
function changeUrl( u, t, d ) {
	d = d || {};
	window.history.pushState( d, t, u );
}

function previewUploads( box, files ) {
	// TODO: Preview files to be uploaded
}

function cleanDrop( box, sign ) {
	find( '.' + sign, box ).forEach( 
		e => e.parentNode.removeChild( e )
	);
	box.classList.remove( 'over' );
}

function dropOver( box, sign ) {
	if ( box.is_txt ) {
		return;
	}
	
	var dv = find ( '.' + sign, box );
	if ( !dv.length ) {
		var dr = create( 'div' );
		attr( dr, 'class', sign );
		box.appendChild( dr );
	}
	box.classList.add( 'over' );
}


// File drop processing
function canDrop( box, opts ) {
	var wf		= false;
	
	// Prerequisites
	if (
		window.File		&& 
		window.FileReader	&& 
		window.FileList		&& 
		window.Blob 
	) {
		wf = true;
	}
	
	// Skip these when changing droppable styling
	box.is_txt	= isA( box, 'input, textarea, span' );
	
	listen( box, 'dragover', function( e ) {
		e.stopPropagation();
		e.preventDefault();
		dropOver( this, opts.sign );
	}, false );
	
	listen( box, 'dragleave, drop', function( e ) {
		cleanDrop( this, opts.sign );
	}, false );
	
	listen( box, 'drop', function( e ) {
		e.stopPropagation();
		e.preventDefault();
		if ( wf ) {
			previewUploads( this, e.dataTransfer.files );
			// TODO: Send to given URL
		}
	}, false );
}

/**
 *  Feature handlers
 */

// Auto-adjust height
function makeResizable( box ) {
	box.style.resize	= 'none';
	resize( box );
	
	listen( box, 'input, change, paste', function( e ) {
		resize( this );
	}, false );
}

// Tie title and slug input fields
function makeTitleSlug( s_box, params ) {
	const opts		= getOptions( params );
	if ( !opts.name ) {
		return;
	}
	opts.max		= parseInt( opts.max || 180 );
	
	const t_box		= byId( opts.name );
	s_box.slugchange	= true;
	
	// Slug has been manually edited
	listen( s_box, 'input, change', function( e ) {
		s_box.slugchange = false;
	}, false );
	
	
	listen( s_box, 'blur', function( e ) {
		if ( s_box.slugchange ) {
			if ( !t_box ) { return; }
			makeSlug( s_box, t_box.value, opts.max );
		} else {
			makeSlug( s_box, this.value, opts.max );
		}
	}, false );
	
	// Title-to-slug change (if slug hasn't already been changed)
	listen( t_box, 'input, change', function( e ) {
		if ( s_box.slugchange ) {
			if ( !t_box ) { return; }
			makeSlug( s_box, t_box.value, opts.max );
		}
	}, false );
}

// Enable tags
function makeTagable( box, params ) {
	const
	opts		= getOptions( params );
	opts.max	= parseInt( opts.max || 20 );
	
	// Position and style properties
	box.css		= style( box );
	
	// Send update on change
	listen( box, 'blur', function( e ) {
		updateLastPos( this );
		this.value = uniqueWords( this.value, ',', ', ', opts.max );
	}, false );
}

// File drop enable
function makeDroppable( box, params ) {
	const opts	= getOptions( params );
	opts.prog	= opts.prog	|| '';
	opts.prev	= opts.prev	|| '';
	opts.url	= opts.url	|| '';
	opts.sign	= opts.sign	|| 'dropsign';
	opts.limit	= opts.limit	|| 20;
	
	canDrop( box, opts );
}


// Load activation features
function findFeatures( box ) {
	const 
	at	= getAttr( box, 'data-feature' ),
	ft	= at.split( ',' ).map( a => a.trim() );
	
	if ( !Array.isArray( ft ) || !ft.length ) {
		return;
	}
	
	ft.map( f => {
		// Optional parameters
		const p	= f.split( ':' ).map( i => i.trim() );
		
		// Empty if not set
		p[1]	= p[1] || '';
		
		switch( p[0] ) {
			
			case 'autoheight':
				makeResizable( box );
				break;
			
			case 'slug':
				makeTitleSlug( box, p[1] );
				break;
				
			case 'tags':
				makeTagable( box, p[1] );
				break;
				
			case 'droppable':
				makeDroppable( box, p[1] );
				break;
		}
	} );
}


// Setup environment
listen( window, 'load', function() {
	const
	ft	= find( '[data-feature]' );
	
	Array.from( ft ).map( w => {
		findFeatures( w );
	});
}, false );

} )(); // End
