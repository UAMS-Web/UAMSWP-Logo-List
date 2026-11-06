<?php

declare(strict_types=1);

use UamswpLogoList\Tests\Support\IntegrationTestCase;

/**
 * Capture the ACF API boundary, without pretending to implement ACF itself.
 * WordPress' real action dispatcher must reach the plugin's registered callback.
 */
function acf_register_block_type(array $block): void
{
    $GLOBALS['logo_test_registered_blocks'][] = $block;
}

/**
 * Verify the real WordPress action dispatcher reaches block registration.
 *
 * @command  composer test:integration -- --filter AcfBlocksTest
 */
final class AcfBlocksTest extends IntegrationTestCase
{
    /**
     * Dispatch acf/init without replacing WordPress' hook implementation.
     */
    public function test_wordpress_dispatches_acf_init_to_the_logo_block(): void
    {
        $GLOBALS['logo_test_registered_blocks'] = [];

        try {
            self::assertSame(10, has_action('acf/init', 'uams_logo_register_blocks'));

            do_action('acf/init');

            self::assertCount(1, $GLOBALS['logo_test_registered_blocks']);
            $block = $GLOBALS['logo_test_registered_blocks'][0];
            self::assertSame('logo-list', $block['name']);
            self::assertSame(3, $block['acf_block_version']);
            self::assertSame(['anchor' => true], $block['supports']);
            self::assertFileExists($block['render_template']);
        } finally {
            unset($GLOBALS['logo_test_registered_blocks']);
        }
    }
}
