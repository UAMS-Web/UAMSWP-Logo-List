<?php

/**
 * The stylesheet registration in includes/class.logo-functions.php, with WordPress mocked.
 *
 * @see includes/class.logo-functions.php
 */

declare(strict_types=1);

use Brain\Monkey\Actions;
use Brain\Monkey\Functions;

it('hooks the stylesheet on wp_enqueue_scripts and enqueues assets/css/app.css with the plugin version', function (): void {
    defined('UAMS_LOGO_ROOT_URL') || define('UAMS_LOGO_ROOT_URL', 'https://example.test/wp-content/plugins/uamswp-logo-list/');
    defined('UAMS_LOGO_VERSION') || define('UAMS_LOGO_VERSION', '9.9.9');

    Actions\expectAdded('wp_enqueue_scripts')
        ->once()
        ->with('logo_script_register');

    require_once dirname(__DIR__, 2).'/includes/class.logo-functions.php';

    Functions\expect('wp_enqueue_style')
        ->once()
        ->with('logo-app-css', UAMS_LOGO_ROOT_URL.'assets/css/app.css', [], UAMS_LOGO_VERSION, 'all');

    logo_script_register();
});
