<?php

namespace UAMS\Logo_List;

add_action( 'plugins_loaded', 'UAMS\Logo_List\bootstrap' );

/**
 * Loads the UAMSWP Logo List
 *
 * @since 1.0.0
 */
function bootstrap() {
    include_once (__DIR__ . '/class.logo-functions.php' );
    include_once (__DIR__ . '/class.logo-acf-blocks.php' );
}