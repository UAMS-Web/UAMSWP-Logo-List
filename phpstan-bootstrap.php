<?php

declare(strict_types=1);

/**
 * Stand-ins for the constants the plugin file defines from function calls, which PHPStan
 * does not evaluate when it discovers constants. Used by PHPStan only.
 */
define('UAMS_LOGO_ROOT_URL', 'https://example.test/wp-content/plugins/uamswp-logo-list/');
define('UAMS_LOGO_PATH', __DIR__.'/');
define('UAMS_LOGO_VERSION', '1.0.0');
