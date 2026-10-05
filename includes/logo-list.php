<?php

declare(strict_types=1);

namespace UAMS\Logo_List;

add_action('plugins_loaded', 'UAMS\Logo_List\bootstrap');

/**
 * Loads the UAMSWP Logo List
 *
 * @since 1.0.0
 */
function bootstrap(): void
{
    include_once __DIR__.'/class.logo-custom-post.php';
    include_once __DIR__.'/class.logo-acf-functions.php'; // ACF specific functions
    include_once __DIR__.'/class.logo-functions.php';
    include_once __DIR__.'/class.logo-acf-blocks.php';
}
