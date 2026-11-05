<?php

/**
 * Unit bootstrap.
 *
 * The Unit suite runs with no WordPress: WordPress functions are mocked per
 * test with Brain Monkey. The Integration suite does not come through here; it
 * has its own Composer setup and bootstrap under tests/Integration.
 */

declare(strict_types=1);

require_once __DIR__.'/../vendor/autoload.php';

/*
 * Patchwork first: it can only redefine functions declared in files loaded
 * after it, so loading it before the plugin is what lets a test mock one of the
 * plugin's own functions, not only WordPress'.
 */
require_once __DIR__.'/../vendor/antecedent/patchwork/Patchwork.php';

/*
 * Plugin files open with a WPINC or ABSPATH guard that exits silently when it is
 * missing. Define both so they load, pointing ABSPATH at a directory that is not
 * a WordPress install: code that requires a core file through ABSPATH fails
 * loudly here, which is the sign it belongs in the Integration suite.
 */
if (! defined('WPINC')) {
    define('WPINC', 'wp-includes');
}

if (! defined('ABSPATH')) {
    define('ABSPATH', sys_get_temp_dir().'/uamswp_logo_list-tests-no-wordpress/');
}

/*
 * The main plugin file is never loaded here: it calls WordPress functions
 * (get_file_data(), plugin_dir_url(), add_action()) at file scope, and Brain
 * Monkey's stubs exist only inside a test. A test that needs a file requires it
 * after stubbing what that file calls; hook registration is tested in the
 * Integration suite.
 */

// cspell:ignore ABSPATH WPINC
