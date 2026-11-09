<?php

declare(strict_types=1);

namespace UamswpLogoList\Tests\Unit;

use Brain\Monkey\Functions;
use Mockery;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\TestCase;

use function Brain\Monkey\Functions\expect;
use function Brain\Monkey\Functions\when;
use function Brain\Monkey\setUp;
use function Brain\Monkey\tearDown;

/**
 * ACF is optional. Separate processes keep its mocked functions genuinely absent
 * in the missing-plugin case and exercise registration at file load each time.
 *
 * @command  vendor/bin/pest tests/Unit/AcfBlocksTest.php
 */
#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
final class AcfBlocksTest extends TestCase
{
    /**
     * Initialize the function boundary before loading the registration file.
     */
    protected function setUp(): void
    {
        parent::setUp();
        setUp();
        define('UAMS_LOGO_PATH', '/example/plugins/logo-list/');
    }

    /**
     * Verify mock expectations and release each isolated function boundary.
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
     * Keep optional ACF APIs absent while loading and invoking registration.
     */
    public function test_loading_without_acf_keeps_the_init_callback_available(): void
    {
        self::assertFalse(function_exists('acf_register_block_type'));
        self::assertFalse(function_exists('acf_add_local_field_group'));

        require dirname(__DIR__, 2).'/includes/class.logo-acf-blocks.php';

        self::assertSame(10, has_action('acf/init', 'uams_logo_register_blocks'));
        uams_logo_register_blocks();
    }

    /**
     * Pin the complete editor-facing block registration, not only its name.
     */
    public function test_acf_init_registers_the_renderable_logo_block(): void
    {
        when('__')->returnArg();
        expect('acf_register_block_type')->once()->with([
            'name' => 'logo-list',
            'title' => 'UAMS Logo List',
            'description' => 'Presenting a list of organization logos',
            'category' => 'common',
            'icon' => 'images-alt',
            'keywords' => ['uams', 'logos', 'organizations', 'images', 'graphics'],
            'acf_block_version' => 3,
            'supports' => ['anchor' => true],
            'align' => 'full',
            'render_template' => '/example/plugins/logo-list//templates/blocks/logo-list.php',
        ]);

        require dirname(__DIR__, 2).'/includes/class.logo-acf-blocks.php';

        self::assertSame(10, has_action('acf/init', 'uams_logo_register_blocks'));
        uams_logo_register_blocks();
    }

    /**
     * Pin the current complete field schema before any production changes.
     * The fixture deliberately captures legacy configuration, not ACF internals.
     */
    public function test_the_editor_receives_the_complete_field_schema(): void
    {
        $group = null;
        expect('acf_add_local_field_group')->once()->andReturnUsing(
            static function (array $configuration) use (&$group): void {
                $group = $configuration;
            }
        );

        require dirname(__DIR__, 2).'/includes/class.logo-acf-blocks.php';

        self::assertSame(
            json_decode(file_get_contents(__DIR__.'/../Fixtures/logo-block-fields.json'), true, flags: JSON_THROW_ON_ERROR),
            $group
        );
    }
}
