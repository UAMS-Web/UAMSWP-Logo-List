<?php

declare(strict_types=1);

namespace UamswpLogoList\Tests\Unit;

use Mockery;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\TestCase;

use function Brain\Monkey\Functions\when;
use function Brain\Monkey\setUp;
use function Brain\Monkey\tearDown;

/**
 * Pin how editor-supplied values reach the block and item markup.
 *
 * The block template constructs `WP_Query`, so each test runs in its own
 * process, where Mockery can overload the class before the template loads it.
 *
 * @command  vendor/bin/pest tests/Unit/LogoTemplateEscapingTest.php
 */
#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
final class LogoTemplateEscapingTest extends TestCase
{
    /**
     * Start a clean WordPress function boundary for each isolated process.
     */
    protected function setUp(): void
    {
        parent::setUp();
        setUp();
        defined('UAMS_LOGO_PATH') || define('UAMS_LOGO_PATH', dirname(__DIR__, 2));
        when('get_post_meta')->justReturn('');
        when('wp_reset_postdata')->justReturn();
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
     * The editor's "Additional CSS class(es)" value currently lands in the section's class attribute as typed.
     */
    public function test_the_block_currently_appends_the_editor_classes_verbatim(): void
    {
        $html = self::block(
            ['block_logo_list_filter_ids' => [13], 'block_logo_list_background_color' => 'bg-"dark'],
            [47 => ['logo_title' => 'Partner']],
            ['block' => ['id' => 'abc123', 'className' => 'promo "><b>x</b> partners']],
        );

        self::assertStringContainsString(
            '<section class="uams-module logo-list alignfull promo "><b>x</b> partners bg-"dark logo-list-layout-grid" id="logo-list-abc123">',
            $html
        );
    }

    /**
     * The block's text fields currently reach the heading, description and more section as stored.
     */
    public function test_the_block_currently_echoes_its_text_fields_raw(): void
    {
        $html = self::block(
            [
                'block_logo_list_filter_ids' => [13],
                'block_logo_list_heading' => 'Partners <b>& friends</b>',
                'block_logo_list_description' => 'Groups we <i>work</i> with',
                'block_logo_list_more' => true,
                'block_logo_list_more_text' => 'More <u>partners</u>',
                'block_logo_list_more_button_text' => 'See <s>all</s>',
                'block_logo_list_more_button_url' => ['url' => 'javascript:alert(1)', 'target' => ''],
                'block_logo_list_more_button_description' => 'All "partners" listed',
            ],
            [47 => ['logo_title' => 'Partner']],
        );

        self::assertStringContainsString('<span class="title">Partners <b>& friends</b></span>', $html);
        self::assertStringContainsString('<div class="module-description"><p>Groups we <i>work</i> with</p></div>', $html);
        self::assertStringContainsString('<p class="lead">More <u>partners</u></p>', $html);
        self::assertStringContainsString(
            '<a href="javascript:alert(1)" class="btn btn-outline-primary" aria-label="All \'partners\' listed">See <s>all</s></a>',
            $html
        );
    }

    /**
     * A grid item's title, short description, label and link currently reach the markup as stored.
     */
    public function test_a_grid_item_currently_echoes_its_fields_raw(): void
    {
        when('get_term')->justReturn((object) ['name' => 'Gold <b>tier</b>']);

        $html = self::item([
            'logo_title' => 'Acme <i>Corp</i>',
            'logo_shortdescr' => 'Makes <b>things</b>',
            'logo_label' => 5,
            'logo_link' => 'javascript:alert(1)',
            'logo_link_target' => 'inherit',
        ], [
            'heading' => 'Our "partners"',
            'labels_query' => true,
            'shortdescr_query' => true,
            'links_query' => true,
        ]);

        self::assertStringContainsString(
            '<a href="javascript:alert(1)" data-moduletitle="Our "partners"" data-itemtitle="Acme <i>Corp</i>" class="stretched-link" aria-describedby="partners_2_button">Acme <i>Corp</i></a>',
            $html
        );
        self::assertStringContainsString('<p class="item-label">Gold <b>tier</b></p>', $html);
        self::assertStringContainsString('<p class="item-text">Makes <b>things</b></p>', $html);
    }

    /**
     * A vertical item's long description and link text currently reach the markup as stored.
     */
    public function test_a_vertical_item_currently_echoes_its_fields_raw(): void
    {
        $html = self::item([
            'logo_title' => 'Acme',
            'logo_longdescr' => '<p>Makes things</p><script>alert(1)</script>',
            'logo_link' => 'https://example.com/?a=1&b=2',
            'logo_link_text' => 'Visit <b>Acme</b>',
            'logo_link_target' => '_blank',
        ], [
            'layout' => 'vertical',
            'titles_query' => true,
            'links_query' => true,
        ]);

        self::assertStringContainsString('<p>Makes things</p><script>alert(1)</script>', $html);
        self::assertStringContainsString(
            '<a href="https://example.com/?a=1&b=2" target=_blank data-moduletitle="Community partners" data-itemtitle="Acme" class="btn btn-primary" aria-describedby = "partners_2_title" id="partners_2_button">Visit <b>Acme</b></a>',
            $html
        );
    }

    /**
     * Render the block template over a fixed, ordered set of logo items.
     *
     * @param  array<string, mixed>  $fields  ACF block field values.
     * @param  array<int, array<string, mixed>>  $posts  Item field values keyed by post ID.
     * @param  array<string, mixed>  $variables  Values already in the template's scope.
     */
    private static function block(
        array $fields,
        array $posts,
        array $variables = [],
    ): string {
        self::fields($fields, $posts);

        $cursor = 0;
        $ids = array_keys($posts);
        $query = Mockery::mock('overload:WP_Query');
        $query->shouldReceive('__construct')->once();
        $query->shouldReceive('have_posts')->andReturnUsing(function () use (&$cursor, $ids): bool {
            return $cursor < \count($ids);
        });
        $query->shouldReceive('the_post')->andReturnUsing(function () use (&$cursor): void {
            $cursor++;
        });
        when('get_the_ID')->alias(function () use (&$cursor, $ids): int {
            return $ids[$cursor - 1];
        });

        return self::render('blocks/logo-list.php', $variables);
    }

    /**
     * Render one item, post 47, inside a block scope without a logo image.
     *
     * @param  array<string, mixed>  $fields  ACF item field values.
     * @param  array<string, mixed>  $variables  Overrides for the block scope.
     */
    private static function item(
        array $fields,
        array $variables = [],
    ): string {
        self::fields([], [47 => $fields]);

        return self::render('loops/logo-list-item.php', array_replace([
            'id' => 'partners',
            'item_id' => 47,
            'logo_item_count' => 2,
            'heading' => 'Community partners',
            'layout' => 'grid',
            'titles_query' => false,
            'labels_query' => false,
            'shortdescr_query' => false,
            'links_query' => false,
            'links_target_value' => '_self',
            'button_color' => 'primary',
            'desaturate_query' => false,
        ], $variables));
    }

    /**
     * Stub the ACF boundary: block values without a post, item values by post ID.
     *
     * @param  array<string, mixed>  $block  Block field values.
     * @param  array<int, array<string, mixed>>  $posts  Item field values keyed by post ID.
     */
    private static function fields(
        array $block,
        array $posts,
    ): void {
        when('get_field')->alias(function (string $name, int|false $post = false) use ($block, $posts): mixed {
            $values = $post === false ? $block : ($posts[$post] ?? []);

            return \array_key_exists($name, $values) ? $values[$name] : false;
        });
    }

    /**
     * Include a template with the given scope and return what it printed.
     *
     * @param  string  $template  Path relative to templates/.
     * @param  array<string, mixed>  $variables  The template's scope.
     */
    private static function render(
        string $template,
        array $variables,
    ): string {
        extract($variables, EXTR_SKIP);
        ob_start();

        try {
            include dirname(__DIR__, 2).'/templates/'.$template;

            return (string) ob_get_contents();
        } finally {
            ob_end_clean();
        }
    }
}
