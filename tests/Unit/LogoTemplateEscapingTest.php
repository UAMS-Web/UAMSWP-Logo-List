<?php

declare(strict_types=1);

namespace UamswpLogoList\Tests\Unit;

use DOMDocument;
use DOMXPath;
use Mockery;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\TestCase;

use function Brain\Monkey\Functions\expect;
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
     *
     * The escaping stubs follow WordPress closely enough to show what each
     * call does to a value; `wp_kses_post` is expected per test instead.
     */
    protected function setUp(): void
    {
        parent::setUp();
        setUp();
        defined('UAMS_LOGO_PATH') || define('UAMS_LOGO_PATH', dirname(__DIR__, 2));
        when('get_post_meta')->justReturn('');
        when('wp_reset_postdata')->justReturn();
        when('esc_html')->alias(fn (mixed $text): string => htmlspecialchars((string) $text, ENT_QUOTES, 'UTF-8'));
        when('esc_attr')->alias(fn (mixed $text): string => htmlspecialchars((string) $text, ENT_QUOTES, 'UTF-8'));
        when('esc_url')->alias(fn (mixed $url): string => preg_match('/^\s*javascript:/i', (string) $url) ? '' : str_replace('&', '&#038;', (string) $url));
        when('sanitize_html_class')->alias(fn (string $class): string => preg_replace('/[^A-Za-z0-9_-]/', '', $class));
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
     * Each token of the editor's "Additional CSS class(es)" is sanitized on its own, and the attribute is escaped.
     */
    public function test_the_block_sanitizes_each_editor_class_and_escapes_the_attribute(): void
    {
        $html = self::block(
            ['block_logo_list_filter_ids' => [13], 'block_logo_list_background_color' => 'bg-"dark'],
            [47 => ['logo_title' => 'Partner']],
            ['block' => ['id' => 'abc123', 'className' => 'promo "><b>x</b> partners']],
        );

        self::assertStringContainsString(
            '<section class="uams-module logo-list alignfull promo bxb partners bg-&quot;dark logo-list-layout-grid" id="logo-list-abc123">',
            $html
        );
    }

    /**
     * Editor classes that sanitize to nothing add no class, not even a stray space.
     */
    public function test_the_block_adds_no_class_when_every_editor_class_sanitizes_away(): void
    {
        $html = self::block(
            ['block_logo_list_filter_ids' => [13]],
            [47 => ['logo_title' => 'Partner']],
            ['block' => ['id' => 'abc123', 'className' => ' "> ']],
        );

        self::assertStringContainsString(
            '<section class="uams-module logo-list alignfull bg-auto logo-list-layout-grid" id="logo-list-abc123">',
            $html
        );
    }

    /**
     * The block's text fields are escaped for the heading, description and more section, and the more link's URL is escaped.
     */
    public function test_the_block_escapes_its_text_fields_and_more_link(): void
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

        self::assertStringContainsString('<span class="title">Partners &lt;b&gt;&amp; friends&lt;/b&gt;</span>', $html);
        self::assertStringContainsString('<div class="module-description"><p>Groups we &lt;i&gt;work&lt;/i&gt; with</p></div>', $html);
        self::assertStringContainsString('<p class="lead">More &lt;u&gt;partners&lt;/u&gt;</p>', $html);
        self::assertStringContainsString(
            '<a href="" class="btn btn-outline-primary" aria-label="All &#039;partners&#039; listed">See &lt;s&gt;all&lt;/s&gt;</a>',
            $html
        );
    }

    /**
     * A grid item escapes its title, short description and label, and its heading link's URL and data attributes.
     */
    public function test_a_grid_item_escapes_its_fields_and_heading_link(): void
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
            '<a href="" data-moduletitle="Our &quot;partners&quot;" data-itemtitle="Acme &lt;i&gt;Corp&lt;/i&gt;" class="stretched-link" aria-describedby="partners_2_button">Acme &lt;i&gt;Corp&lt;/i&gt;</a>',
            $html
        );
        self::assertStringContainsString('<p class="item-label">Gold &lt;b&gt;tier&lt;/b&gt;</p>', $html);
        self::assertStringContainsString('<p class="item-text">Makes &lt;b&gt;things&lt;/b&gt;</p>', $html);
    }

    /**
     * A vertical item passes its long description through `wp_kses_post` and escapes its button link.
     */
    public function test_a_vertical_item_filters_its_long_description_and_escapes_its_button_link(): void
    {
        expect('wp_kses_post')
            ->once()
            ->with('<p>Makes things</p><script>alert(1)</script>')
            ->andReturn('<p>Makes things</p>alert(1)');

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

        self::assertStringContainsString('<p>Makes things</p>alert(1)', $html);
        self::assertStringNotContainsString('<script>', $html);
        self::assertStringContainsString(
            '<a href="https://example.com/?a=1&#038;b=2" target="_blank" rel="noopener" data-moduletitle="Community partners" data-itemtitle="Acme" class="btn btn-primary" aria-describedby = "partners_2_title" id="partners_2_button">Visit &lt;b&gt;Acme&lt;/b&gt;</a>',
            $html
        );
    }

    /**
     * An item with no stored target prints no `target` at all, so a heading or title holding a space stays inside its own quoted attribute.
     */
    public function test_an_item_with_no_stored_target_prints_no_target_attribute(): void
    {
        $html = self::item([
            'logo_title' => 'Acme onerror=alert(1)',
            'logo_link' => 'https://example.com/',
            'logo_link_target' => '',
        ], [
            'heading' => 'Partners onmouseover=alert(1)',
            'links_query' => true,
        ]);

        self::assertStringContainsString(
            '<a href="https://example.com/" data-moduletitle="Partners onmouseover=alert(1)" data-itemtitle="Acme onerror=alert(1)" class="stretched-link" aria-describedby="partners_2_button">',
            $html
        );
        self::assertSame([], self::eventHandlerAttributes($html));
    }

    /**
     * A stored target outside the field's choices prints no `target` either: only `_blank` does.
     */
    public function test_an_item_prints_a_target_only_for_blank(): void
    {
        $html = self::item([
            'logo_title' => 'Acme',
            'logo_link' => 'https://example.com/',
            'logo_link_target' => '_top onmouseover=alert(1)',
        ], [
            'links_query' => true,
        ]);

        self::assertStringContainsString(
            '<a href="https://example.com/" data-moduletitle="Community partners" data-itemtitle="Acme" class="stretched-link" aria-describedby="partners_2_button">',
            $html
        );
        self::assertSame([], self::eventHandlerAttributes($html));
    }

    /**
     * List every `on*` attribute the parsed markup carries, as `tag@name`.
     *
     * @return list<string>
     */
    private static function eventHandlerAttributes(string $html): array
    {
        $document = new DOMDocument;
        $previous = libxml_use_internal_errors(true);

        try {
            $document->loadHTML('<!DOCTYPE html><html><body>'.$html.'</body></html>');
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
        }

        $found = [];
        foreach ((new DOMXPath($document))->query('//@*[starts-with(name(), "on")]') ?: [] as $attribute) {
            $found[] = $attribute->ownerElement?->nodeName.'@'.$attribute->nodeName;
        }

        return $found;
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
