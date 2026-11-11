<?php

declare(strict_types=1);
/*
Plugin Name: UAMSWP Logo List
Plugin URI: https://github.com/UAMS-Web/UAMSWP-Logo-List
Version: 2.0.2
Requires PHP: 8.2
Description: Logo list plugin for uams.edu & uamshealth.com
Author: UAMS Web Services
Author URI: https://communications.uams.edu/web/
*/

// If this file is called directly, abort.
if (! defined('WPINC')) {
    exit;
}

// This plugin uses namespaces and requires PHP 8.2 or greater.
define('UAMS_LOGO_ROOT_URL', plugin_dir_url(__FILE__));
define('UAMS_LOGO_PATH', plugin_dir_path(__FILE__));
$plugin_header = get_file_data(
    __FILE__,
    [
        'version' => 'Version',
    ]
);
$plugin_version = $plugin_header['version'];
define('UAMS_LOGO_VERSION', $plugin_version);
require_once __DIR__.'/required-plugins.php';
include_once __DIR__.'/includes/logo-list.php';
