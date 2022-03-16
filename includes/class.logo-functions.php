<?php

// Enqueue Scripts and Styles
function logo_script_register() {
	wp_enqueue_style( 'logo-app-css', UAMS_LOGO_ROOT_URL . 'assets/css/app.css', array(), UAMS_LOGO_VERSION, 'all');                  
}
add_action( 'wp_enqueue_scripts', 'logo_script_register' );