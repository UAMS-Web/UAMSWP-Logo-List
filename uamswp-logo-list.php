<?php
/*
Plugin Name: UAMSWP Logo List
Plugin URI: - 
Version: 1.0.0
Requires PHP: 5.3
Description: Logo list plugin for uams.edu & uamshealth.com
Author: UAMS, Josh Daugherty
Author URI: http://www.uams.edu/
*/

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// This plugin uses namespaces and requires PHP 5.3 or greater.
if ( version_compare( PHP_VERSION, '5.3', '<' ) ) {
	add_action( 'admin_notices', create_function( '', // phpcs:ignore WordPress.PHP.RestrictedPHPFunctions.create_function_create_function
	"echo '<div class=\"error\"><p>" . __( 'UAMSWP Logo List requires PHP 5.3 to function properly. Please upgrade PHP or deactivate the plugin.', 'uamswp-logo-list' ) . "</p></div>';" ) );
	return;
} else {
	define('UAMS_LOGO_ROOT_URL', plugin_dir_url(__FILE__));
	define('UAMS_LOGO_PATH', plugin_dir_path(__FILE__));

	$plugin_header  = get_file_data(
		__FILE__,
		array(
			'version' => 'Version',
		)
	);
	$plugin_version = $plugin_header['version'];

	define('UAMS_LOGO_VERSION', $plugin_version);
	
	require_once __DIR__ . '/required-plugins.php';
	include_once __DIR__ . '/includes/logo-list.php';
}
