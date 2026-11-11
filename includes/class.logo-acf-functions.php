<?php

declare(strict_types=1);
/*
 * Advanced Custom Fields Functions
 */

add_filter('acf/settings/load_json', 'uamswp_logo_json_load_point');
/**
 * Add the plugin's ACF JSON directory to the paths ACF loads field groups from.
 *
 * @param  array<int, string>  $paths  The directories ACF loads from.
 * @return array<int, string> The directories, with the plugin's appended.
 */
function uamswp_logo_json_load_point($paths)
{

    // remove original path (optional)
    // unset($paths[0]);

    // append path
    $paths[] = UAMS_LOGO_PATH.'assets/json/acf-json';

    // return
    return $paths;

}

// Add a new wysiwyg toolbar called "Very Simple" with value "very_simple"
add_filter('acf/fields/wysiwyg/toolbars', 'uamswp_logo_toolbars');
/**
 * Add the "Very Simple" toolbar to the WYSIWYG toolbars ACF offers.
 *
 * @param  array<string, array<int, array<int, string>>>  $toolbars  The toolbars ACF offers.
 * @return array<string, array<int, array<int, string>>> The toolbars, with "Very Simple" added.
 */
function uamswp_logo_toolbars($toolbars)
{
    // [1] formatselect. bold, italic, bullist, numlist, blockquote, alignleft, aligncenter, alignright, link, wp_more, spellchecker, fullscreen, wp_adv
    // [2] strikethrough, hr, forecolor, pastetext, removeformat, charmap, outdent, indent, undo, redo, wp_help
    $toolbars['Very Simple'] = [];
    $toolbars['Very Simple'][1] = [
        'bold',
        'italic',
        'bullist',
        'numlist',
        'removeformat',
    ];

    // Uncomment to view format of $toolbars
    // print("<pre>".print_r($toolbars,true)."</pre>");
    // die;

    return $toolbars;
}
