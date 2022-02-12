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