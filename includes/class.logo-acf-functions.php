<?php
/*
 * Advanced Custom Fields Functions
 */

add_filter('acf/settings/load_json', 'uamswp_logo_json_load_point');
function uamswp_logo_json_load_point( $paths ) {
    
    // remove original path (optional)
    // unset($paths[0]);
    
    
    // append path
    $paths[] = UAMS_LOGO_PATH . 'assets/json/acf-json';
    
    
    // return
    return $paths;
    
}

// Add a new wysiwyg toolbar called "Very Simple" with value "very_simple"
add_filter('acf/fields/wysiwyg/toolbars', 'uamswp_logo_toolbars');
function uamswp_logo_toolbars( $toolbars )
{
	// [1] formatselect. bold, italic, bullist, numlist, blockquote, alignleft, aligncenter, alignright, link, wp_more, spellchecker, fullscreen, wp_adv
	// [2] strikethrough, hr, forecolor, pastetext, removeformat, charmap, outdent, indent, undo, redo, wp_help
	$toolbars['Very Simple'] = array();
	$toolbars['Very Simple'][1] = [ 
		'bold', 
		'italic',
		'bullist',
		'numlist',
		'removeformat'
	];

	// Uncomment to view format of $toolbars
	// print("<pre>".print_r($toolbars,true)."</pre>");
	// die;

	return $toolbars;
}