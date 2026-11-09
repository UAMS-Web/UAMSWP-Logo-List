<?php

declare(strict_types=1);

namespace UamswpLogoList\Tests\Unit;

use Mockery;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

use function Brain\Monkey\Functions\expect;
use function Brain\Monkey\Functions\when;
use function Brain\Monkey\setUp;
use function Brain\Monkey\tearDown;
use function UAMS\Logo_List\bootstrap;

/**
 * Pin file-load behavior without leaking plugin constants between tests.
 *
 * @command  vendor/bin/pest tests/Unit/LogoStartupTest.php
 */
#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
final class LogoStartupTest extends TestCase
{
    /**
     * Start a clean WordPress function boundary for each isolated process.
     */
    protected function setUp(): void
    {
        parent::setUp();
        setUp();
    }

    /**
     * Verify boundary expectations before releasing the isolated process.
     */
    protected function tearDown(): void
    {
        try {
            $this->addToAssertionCount(Mockery::getContainer()->mockery_getExpectationCount());
            tearDown();
        } finally {
            parent::tearDown();
        }
    }

    /**
     * Keep direct access from executing any WordPress-dependent initialization.
     */
    public function test_direct_access_exits_before_wordpress_initialization(): void
    {
        // Keep Composer's mutation override but omit the WordPress test bootstrap.
        $root = dirname(__DIR__, 2);
        $code = 'require '.var_export($root.'/vendor/autoload.php', true).';'
            .'require '.var_export($root.'/uamswp-logo-list.php', true).';'
            .'echo "Initialization continued";';
        $process = new Process([PHP_BINARY, '-r', $code], timeout: 15);
        $process->run();

        // A fatal error is not equivalent to the deliberate, silent early exit.
        self::assertSame(0, $process->getExitCode(), $process->getErrorOutput());
        self::assertSame('', $process->getOutput());
        self::assertSame('', $process->getErrorOutput());
    }

    /**
     * Load metadata and registrations from the real root, then dispatch startup.
     */
    public function test_loading_the_root_initializes_metadata_dependencies_and_the_loader(): void
    {
        // Provide only TGMPA's parent class, not a fake implementation of TGMPA.
        require __DIR__.'/../Support/WordPressListTable.php';
        $root = dirname(__DIR__, 2);
        when('plugin_dir_path')->justReturn($root.'/');
        when('plugin_dir_url')->justReturn('https://example.test/logo-list/');
        expect('get_file_data')->once()->with($root.'/uamswp-logo-list.php', ['version' => 'Version'])
            ->andReturn(['version' => '7.3.9']);

        // Include the actual entry point, including its dependency configuration.
        require $root.'/uamswp-logo-list.php';
        self::assertSame($root.'/', UAMS_LOGO_PATH);
        self::assertSame('https://example.test/logo-list/', UAMS_LOGO_ROOT_URL);
        self::assertSame('7.3.9', UAMS_LOGO_VERSION);
        self::assertSame(10, has_action('tgmpa_register', 'uamswp_logo_register_required_plugins'));
        self::assertSame(10, has_action('plugins_loaded', 'UAMS\\Logo_List\\bootstrap'));

        // Exercise the registered loader; real hook dispatch is integration-tested.
        bootstrap();
        self::assertSame(0, has_action('init', 'logo_list_items_cpt'));
        self::assertSame(0, has_action('init', 'create_logo_list_category'));
        self::assertSame(0, has_action('init', 'create_logo_list_label_tag'));
        self::assertSame(10, has_action('init', 'uamswp_logo_list_role_caps'));
        self::assertSame(10, has_action('admin_menu', 'remove_logo_taxonomy_meta'));
        self::assertSame(10, has_action('wp_enqueue_scripts', 'logo_script_register'));
        self::assertSame(10, has_filter('acf/settings/load_json', 'uamswp_logo_json_load_point'));
        self::assertSame(10, has_filter('acf/fields/wysiwyg/toolbars', 'uamswp_logo_toolbars'));
        self::assertSame(10, has_action('acf/init', 'uams_logo_register_blocks'));
    }

    /**
     * Keep the complete first-party dependency configuration in the population.
     */
    public function test_required_plugins_supply_the_complete_tgmpa_configuration(): void
    {
        // Keep TGMPA outside the mutation population while observing its boundary.
        require __DIR__.'/../Support/WordPressListTable.php';
        $root = dirname(__DIR__, 2);
        expect('tgmpa')->once()->with([
            [
                'name' => 'Advanced Custom Fields PRO',
                'slug' => 'advanced-custom-fields-pro',
                'source' => $root.'/plugins/advanced-custom-fields-pro.zip',
                'required' => true,
            ],
            [
                'name' => 'Advanced Custom Fields: Mask Field',
                'slug' => 'acf-maskfield',
                'source' => $root.'/plugins/acf-maskfield.zip',
                'required' => true,
            ],
            [
                'name' => 'Advanced Custom Fields: Extended',
                'slug' => 'acf-extended',
                'required' => true,
            ],
        ], [
            'id' => 'uamswp',
            'default_path' => '',
            'menu' => 'tgmpa-install-plugins',
            'parent_slug' => 'plugins.php',
            'capability' => 'manage_options',
            'has_notices' => true,
            'dismissable' => true,
            'dismiss_msg' => '',
            'is_automatic' => false,
            'message' => '',
        ]);

        // Run the configuration callback without installing any declared package.
        require $root.'/required-plugins.php';
        self::assertSame(10, has_action('tgmpa_register', 'uamswp_logo_register_required_plugins'));
        uamswp_logo_register_required_plugins();
    }
}
