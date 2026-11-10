<?php

/**
 * Characterize the block and item templates with WordPress boundaries mocked.
 *
 * @command  vendor/bin/pest tests/Unit/LogoTemplatesTest.php
 */

declare(strict_types=1);

namespace UamswpLogoList\Tests\Unit;

use Brain\Monkey\Functions;
use Mockery;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\TestCase;
use UamswpLogoList\Tests\Support\LogoTemplateRenderer;

use function Brain\Monkey\Functions\when;
use function Brain\Monkey\setUp;
use function Brain\Monkey\tearDown;

/**
 * Native PHPUnit isolation prevents generated Brain Monkey functions leaking.
 *
 * @command  vendor/bin/pest tests/Unit/LogoTemplatesTest.php
 */
#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
final class LogoTemplatesTest extends TestCase
{
    /** Set up the WordPress boundary for each isolated test. */
    protected function setUp(): void
    {
        parent::setUp();
        setUp();
        when('get_post_meta')->justReturn('Native attachment description');
        when('wp_get_attachment_image')->alias(fn (mixed $id): string => $id === 919 ? '' : '<img />');
        when('wp_get_attachment_image_url')->alias(fn (mixed $id, string $size): string => '/media/'.$id.'-'.$size.'.png');
    }

    /** Verify mock expectations and release the isolated function replacements. */
    protected function tearDown(): void
    {
        try {
            $this->addToAssertionCount(Mockery::getContainer()->mockery_getExpectationCount());
            tearDown();
        } finally {
            parent::tearDown();
        }
    }

    /** Reject blocks with missing categories or missing query results. */
    #[DataProvider('emptyBlocks')]
    public function test_renders_nothing_without_categories_or_posts(
        array $fields,
        array $posts,
    ): void {
        Functions\expect('wp_reset_postdata')->never();

        $result = LogoTemplateRenderer::block($fields, $posts);

        expect(trim($result['html']))->toBe('');
    }

    /** @return array<string, array{array<string, mixed>, array<int, array<string, mixed>>}> */
    public static function emptyBlocks(): array
    {
        return [
            'no categories despite a matching post' => [[], [47 => ['logo_title' => 'Must not appear']]],
            'categories but no matches' => [['block_logo_list_filter_ids' => [13, 29]], []],
        ];
    }

    /** Explain incomplete editor previews without rendering an empty section. */
    #[DataProvider('previewMessages')]
    public function test_explains_unconfigured_or_empty_editor_previews(
        array $fields,
        string $message,
    ): void {
        Functions\expect('wp_reset_postdata')->never();

        $result = LogoTemplateRenderer::block($fields, [], ['is_preview' => true]);

        expect($result['html'])
            ->toContain('class="uams-block-placeholder"', '<strong>UAMS Logo List</strong>', $message)
            ->not->toContain('<section');
    }

    /** @return array<string, array{array<string, mixed>, string}> */
    public static function previewMessages(): array
    {
        return [
            'no selection' => [[], 'Click the pencil icon in the block toolbar and choose at least one category.'],
            'no matching logos' => [['block_logo_list_filter_ids' => [29]], 'No published logos match the selected categories and labels.'],
        ];
    }

    /** Pin the complete default query, including its empty category constraint. */
    public function test_queries_published_items_with_default_arguments(): void
    {
        Functions\expect('wp_reset_postdata')->never();

        $result = LogoTemplateRenderer::block();

        expect($result['args'])->toBe([
            'post_type' => 'logo-list-item',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'orderby' => [],
            'tax_query' => [
                'relation' => 'AND',
                [
                    'taxonomy' => 'logo-list-category',
                    'field' => 'term_id',
                    'terms' => [],
                    'operator' => 'IN',
                ],
            ],
        ]);
    }

    /** Preserve distinct category and label operators and values. */
    public function test_combines_distinct_category_and_label_filters(): void
    {
        Functions\expect('wp_reset_postdata')->never();

        $result = LogoTemplateRenderer::block([
            'block_logo_list_filter_ids' => [13, 29],
            'block_logo_list_filter_logic' => 'AND',
            'block_logo_list_labels_ids' => [71],
            'block_logo_list_labels_logic' => 'NOT IN',
            'block_logo_list_max' => 17,
        ]);

        expect($result['args']['posts_per_page'])->toBe(17)
            ->and($result['args']['tax_query'])->toBe([
                'relation' => 'AND',
                [
                    'taxonomy' => 'logo-list-category',
                    'field' => 'term_id',
                    'terms' => [13, 29],
                    'operator' => 'AND',
                ],
                [
                    'taxonomy' => 'logo-list-label',
                    'field' => 'term_id',
                    'terms' => [71],
                    'operator' => 'NOT IN',
                ],
            ]);
    }

    /** Pin ordering syntax, fallback keys, and optional metadata sorting. */
    #[DataProvider('orderingOptions')]
    public function test_passes_ordering_and_its_fallback(
        array $orders,
        array $expected,
        bool $hasMetaKey,
    ): void {
        Functions\expect('wp_reset_postdata')->never();

        $result = LogoTemplateRenderer::block(['block_logo_list_order' => $orders]);

        expect($result['args']['orderby'])->toBe($expected);

        if ($hasMetaKey) {
            expect($result['args']['meta_key'])->toBe('logo_title');
        } else {
            expect($result['args'])->not->toHaveKey('meta_key');
        }
    }

    /** @return array<string, array{list<string>, array<string, string>, bool}> */
    public static function orderingOptions(): array
    {
        return [
            'single menu order' => [['menu_order|DESC'], ['menu_order' => 'DESC', 'meta_value' => 'ASC'], true],
            'single alphabetical order' => [['meta_value|DESC'], ['meta_value' => 'DESC', 'menu_order' => 'ASC'], true],
            'single date without direction' => [['date'], ['date' => 'ASC', 'meta_value' => 'ASC'], true],
            'random without direction' => [['rand'], ['rand' => 'ASC'], false],
            'random seed' => [['rand(37)|DESC'], ['rand(37)' => 'DESC'], false],
            'multiple explicit orders' => [['date|DESC', 'menu_order|ASC'], ['date' => 'DESC', 'menu_order' => 'ASC'], false],
            'malformed direction currently defaults' => [['date|DESC|ignored', 'menu_order'], ['date' => 'ASC', 'menu_order' => 'ASC'], false],
        ];
    }

    /** Limit featured results without increasing smaller positive limits. */
    #[DataProvider('featuredLimits')]
    public function test_caps_featured_queries_at_three(
        mixed $maximum,
        int $expected,
    ): void {
        Functions\expect('wp_reset_postdata')->never();

        $result = LogoTemplateRenderer::block([
            'block_logo_list_featured' => true,
            'block_logo_list_max' => $maximum,
        ]);

        expect($result['args']['posts_per_page'])->toBe($expected);
    }

    /** @return array<string, array{int|false, int}> */
    public static function featuredLimits(): array
    {
        return [
            'unset' => [false, 3],
            'zero' => [0, 3],
            'unlimited' => [-1, 3],
            'negative' => [-9, 3],
            'one' => [1, 1],
            'two' => [2, 2],
            'three' => [3, 3],
            'larger' => [19, 3],
        ];
    }

    /** Preserve post identity, ordering, block classes, and post-data cleanup. */
    public function test_renders_each_queried_logo_once_and_resets_post_data(): void
    {
        Functions\expect('wp_reset_postdata')->once();

        $result = LogoTemplateRenderer::block([
            'block_logo_list_filter_ids' => [29],
            'block_logo_list_heading' => 'Regional allies',
            'block_logo_list_description' => 'Across the state',
            'block_logo_list_width' => 'width-narrow',
        ], [
            83 => ['logo_title' => 'Ozark Coalition'],
            47 => ['logo_title' => 'Delta Alliance'],
        ], ['block' => ['id' => 'block-6', 'className' => 'campaign-partners'], 'is_preview' => true]);

        expect($result['html'])
            ->toContain('class="uams-module logo-list alignfull campaign-partners bg-auto logo-list-layout-grid" id="logo-list-block-6"')
            ->toContain('<span class="title">Regional allies</span>', '<div class="module-description"><p>Across the state</p></div>')
            ->toContain('class="item-list-container width-narrow"', 'id="logo-list-block-6_0"', 'id="logo-list-block-6_1"')
            ->not->toContain('uams-block-placeholder');
        expect(substr_count($result['html'], 'Ozark Coalition'))->toBe(1)
            ->and(substr_count($result['html'], 'Delta Alliance'))->toBe(1)
            ->and(strpos($result['html'], 'Ozark Coalition'))->toBeLessThan(strpos($result['html'], 'Delta Alliance'));
    }

    /** Hide the wrapper or just the heading according to description content. */
    #[DataProvider('headingVisibility')]
    public function test_hides_the_heading_or_its_wrapper(
        bool $hidden,
        string $description,
        string $wrapper,
        string $headingClass,
    ): void {
        Functions\expect('wp_reset_postdata')->once();

        $result = LogoTemplateRenderer::block([
            'block_logo_list_filter_ids' => [13],
            'block_logo_list_heading' => 'Our partners',
            'block_logo_list_hide_heading' => $hidden,
            'block_logo_list_description' => $description,
        ], [47 => ['logo_title' => 'Delta Alliance']]);

        expect($result['html'])->toContain($wrapper."\n                ".$headingClass, '<span class="title">Our partners</span>');
        expect(str_contains($result['html'], 'module-description'))->toBe($description !== '');
    }

    /** @return array<string, array{bool, string, string, string}> */
    public static function headingVisibility(): array
    {
        return [
            'visible with no description' => [false, '', '<div class="col-12">', '<h2 class="module-title">'],
            'visible with description' => [false, 'Partner introduction', '<div class="col-12">', '<h2 class="module-title">'],
            'hidden without description' => [true, '', '<div class="col-12 sr-only">', '<h2 class="module-title">'],
            'hidden with description' => [true, 'Partner introduction', '<div class="col-12">', '<h2 class="module-title sr-only">'],
        ];
    }

    /** Respect custom anchors and caller-supplied heading and description text. */
    public function test_sanitizes_anchors_and_preserves_caller_values(): void
    {
        Functions\expect('sanitize_title_with_dashes')->once()->with('Partner Anchor!')->andReturn('partner-anchor');
        Functions\expect('wp_reset_postdata')->once();

        $result = LogoTemplateRenderer::block([
            'block_logo_list_filter_ids' => [13],
            'block_logo_list_heading' => 'Ignored field heading',
            'block_logo_list_description' => 'Ignored field description',
        ], [47 => ['logo_title' => 'Delta Alliance']], [
            'block' => ['id' => 'unused', 'anchor' => 'Partner Anchor!'],
            'heading' => 'Caller heading',
            'description' => 'Caller description',
        ]);

        expect($result['html'])
            ->toContain('id="partner-anchor"', '<span class="title">Caller heading</span>', '<p>Caller description</p>')
            ->not->toContain('Ignored field')->not->toContain('logo-list-unused');
    }

    /** Pin IDs for rendering outside an ACF block context. */
    public function test_uses_an_empty_id_without_block_context(): void
    {
        Functions\expect('wp_reset_postdata')->once();

        $result = LogoTemplateRenderer::block(['block_logo_list_filter_ids' => [13]], [47 => ['logo_title' => 'Delta Alliance']]);

        expect($result['html'])->toContain('id=""', 'id="_0"');
    }

    /** Pin button colors and the current omission of the more-link target. */
    #[DataProvider('backgroundColors')]
    public function test_renders_the_more_section_with_background_button_color(
        mixed $background,
        string $color,
        string $target = '_blank',
    ): void {
        Functions\expect('wp_reset_postdata')->once();

        $result = LogoTemplateRenderer::block([
            'block_logo_list_filter_ids' => [13],
            'block_logo_list_background_color' => $background,
            'block_logo_list_more' => true,
            'block_logo_list_more_text' => 'Explore our full network',
            'block_logo_list_more_button_text' => 'Meet every partner',
            'block_logo_list_more_button_url' => ['url' => '/partners/all', 'target' => $target],
            'block_logo_list_more_button_description' => "Meet \"all\"\u{00A0}partners",
        ], [47 => ['logo_title' => 'Delta Alliance']]);

        expect($result['html'])
            ->toContain('<div class="col-12 more">', '<p class="lead">Explore our full network</p>')
            ->toContain('href="/partners/all" class="btn btn-outline-'.$color.'" aria-label="Meet \'all\' partners">Meet every partner</a>')
            ->not->toContain('target="_blank"');
    }

    /** @return array<string, array{0: string|false, 1: string, 2?: string}> */
    public static function backgroundColors(): array
    {
        return [
            'default background' => [false, 'primary'],
            'white background' => ['bg-white', 'primary'],
            'gray background' => ['bg-gray', 'primary'],
            'automatic background' => ['bg-auto', 'primary'],
            'colored background' => ['bg-red', 'white'],
            'same-window more link' => ['bg-white', 'primary', ''],
        ];
    }

    /** Suppress incomplete or disabled calls to action. */
    #[DataProvider('incompleteMoreSections')]
    public function test_omits_incomplete_or_disabled_more_sections(array $fields): void
    {
        Functions\expect('wp_reset_postdata')->once();

        $result = LogoTemplateRenderer::block(array_replace([
            'block_logo_list_filter_ids' => [13],
            'block_logo_list_more' => true,
            'block_logo_list_more_text' => 'More partners',
            'block_logo_list_more_button_url' => ['url' => '/partners', 'target' => ''],
            'block_logo_list_more_button_description' => 'All partners',
        ], $fields), [47 => ['logo_title' => 'Delta Alliance']]);

        expect($result['html'])->not->toContain('col-12 more')->not->toContain('cta-container');
    }

    /** @return array<string, array{array<string, mixed>}> */
    public static function incompleteMoreSections(): array
    {
        return [
            'disabled' => [['block_logo_list_more' => false]],
            'missing text' => [['block_logo_list_more_text' => '']],
            'missing link' => [['block_logo_list_more_button_url' => false]],
        ];
    }

    /** Respect caller-supplied call-to-action values rather than replacing them. */
    public function test_preserves_caller_overrides_for_more_section(): void
    {
        Functions\expect('wp_reset_postdata')->once();

        $result = LogoTemplateRenderer::block([
            'block_logo_list_filter_ids' => [13],
            'block_logo_list_more' => true,
        ], [47 => ['logo_title' => 'Delta Alliance']], [
            'more_text' => 'Caller more text',
            'more_button_text' => 'Caller button',
            'more_button_link' => ['url' => '/ignored-url', 'target' => '_blank'],
            'more_button_link_url' => '/caller-url',
            'more_button_target' => '_self',
            'more_button_description' => 'Caller description',
            'more_button_description_attr' => 'Caller accessible label',
        ]);

        expect($result['html'])->toContain('<p class="lead">Caller more text</p>', 'href="/caller-url"', 'aria-label="Caller accessible label">Caller button</a>')
            ->not->toContain('/ignored-url');
    }

    /** Keep titles visible when the attachment is absent or has been deleted. */
    #[DataProvider('missingImages')]
    public function test_shows_text_only_logos_as_visible_titles(mixed $image): void
    {
        $html = LogoTemplateRenderer::item(['logo_title' => 'Delta Alliance', 'logo_image' => $image]);

        expect($html)
            ->toContain('<div class="item item-has-title" id="partners-west_2">', '<h3 class="item-title" id="partners-west_2_title">Delta Alliance</h3>')
            ->not->toContain('<picture>')->not->toContain('item-title-hidden')->not->toContain('<span class="sr-only">')->not->toContain('link-container')->not->toContain('item-has-logo');
    }

    /** @return array<string, array{int|false|null}> */
    public static function missingImages(): array
    {
        return [
            'unset image' => [null],
            'false image' => [false],
            'zero image' => [0],
            'deleted attachment' => [919],
        ];
    }

    /** Render native attachment URLs and the configured title and image classes. */
    #[DataProvider('nativeImageOptions')]
    public function test_uses_the_wordpress_image_fallback(
        bool $titles,
        bool $desaturate,
    ): void {
        $html = LogoTemplateRenderer::item([
            'logo_title' => 'Delta Alliance',
            'logo_image' => 317,
        ], ['titles_query' => $titles, 'desaturate_query' => $desaturate]);

        expect($html)->toContain('<img src="/media/317-aspect-16-9.png" alt="" />', 'item-has-logo')
            ->not->toContain('<source')->not->toContain('Native attachment description');
        expect(str_contains($html, 'item-img-desaturate'))->toBe($desaturate)
            ->and(str_contains($html, 'item-title-hidden'))->toBe(! $titles)
            ->and(str_contains($html, '<span class="sr-only">Delta Alliance</span>'))->toBe(! $titles)
            ->and(str_contains($html, 'item-has-title'))->toBe($titles);
    }

    /** @return array<string, array{bool, bool}> */
    public static function nativeImageOptions(): array
    {
        return [
            'hidden title, normal image' => [false, false],
            'visible title, desaturated image' => [true, true],
        ];
    }

    /** Pin grid links, target inheritance, and default versus explicit labels. */
    #[DataProvider('linkTargets')]
    public function test_renders_grid_heading_links(
        string $itemTarget,
        string $parentTarget,
        string $attribute,
        mixed $text,
        string $expectedText,
    ): void {
        $html = LogoTemplateRenderer::item([
            'logo_title' => 'Delta Alliance',
            'logo_link' => 'https://delta.example/visit',
            'logo_link_text' => $text,
            'logo_link_target' => $itemTarget,
            'logo_shortdescr' => 'Local support',
        ], ['links_query' => true, 'links_target_value' => $parentTarget, 'shortdescr_query' => true]);

        expect($html)
            ->toContain('<a href="https://delta.example/visit"'.$attribute.' data-moduletitle="Community partners" data-itemtitle="Delta Alliance" class="stretched-link" aria-describedby="partners-west_2_button">Delta Alliance</a>')
            ->toContain('<span style="display: none;" aria-hidden="true" id="partners-west_2_button">'.$expectedText.'</span>')
            ->toContain('item-has-shortdescr', 'item-has-link', '<p class="item-text">Local support</p>')
            ->not->toContain('class="btn btn-')->not->toContain('item-has-longdescr');
    }

    /** @return array<string, array{string, string, string, string|false, string}> */
    public static function linkTargets(): array
    {
        return [
            'inherit new window with default text' => ['inherit', '_blank', ' target=_blank', false, 'Visit Their Website'],
            'inherit same window with custom text' => ['inherit', '_self', '', 'Explore Delta', 'Explore Delta'],
            'explicit new window' => ['_blank', '_self', ' target=_blank', 'Read more', 'Read more'],
            'explicit same window' => ['_self', '_blank', '', 'Read more', 'Read more'],
        ];
    }

    /** Render vertical descriptions and buttons without grid-only presentation. */
    public function test_uses_a_visible_button_and_long_description_in_vertical_lists(): void
    {
        Functions\expect('get_term')->once()->with(71)->andReturn((object) ['name' => 'Founding partner']);

        $html = LogoTemplateRenderer::item([
            'logo_title' => 'Delta Alliance',
            'logo_label' => 71,
            'logo_shortdescr' => 'Do not display this short description',
            'logo_longdescr' => '<p>Serving the delta since 1987.</p>',
            'logo_link' => '/delta',
            'logo_link_text' => 'Explore Delta',
            'logo_link_target' => '_self',
        ], ['layout' => 'vertical', 'titles_query' => true, 'labels_query' => true, 'shortdescr_query' => true, 'links_query' => true, 'button_color' => 'white']);

        expect($html)
            ->toContain('item-has-title item-has-label item-has-longdescr item-has-link')
            ->toContain('<h3 class="item-title" id="partners-west_2_title">Delta Alliance</h3>')
            ->toContain('<p class="item-label">Founding partner</p>', '<p>Serving the delta since 1987.</p>')
            ->toContain('<a href="/delta" data-moduletitle="Community partners" data-itemtitle="Delta Alliance" class="btn btn-white" aria-describedby = "partners-west_2_title" id="partners-west_2_button">Explore Delta</a>')
            ->not->toContain('stretched-link')->not->toContain('item-has-shortdescr')->not->toContain('Do not display this short description')->not->toContain('target=');
    }

    /** Require both a configured link and permission to show it. */
    #[DataProvider('disabledLinks')]
    public function test_omits_links_unless_block_and_logo_enable_them(
        bool $enabled,
        mixed $url,
    ): void {
        $html = LogoTemplateRenderer::item([
            'logo_title' => 'Delta Alliance',
            'logo_link' => $url,
            'logo_link_target' => '_blank',
        ], ['links_query' => $enabled]);

        expect($html)->not->toContain('item-has-link')->not->toContain('<a ')->not->toContain('link-container')->not->toContain('partners-west_2_button');
    }

    /** @return array<string, array{bool, string|false}> */
    public static function disabledLinks(): array
    {
        return [
            'block disables a populated link' => [false, '/delta'],
            'item has no link' => [true, false],
        ];
    }

    /** Pin the empty paragraph currently emitted for missing enabled labels. */
    public function test_keeps_an_empty_enabled_label_paragraph(): void
    {
        Functions\expect('get_term')->never();

        $html = LogoTemplateRenderer::item(['logo_title' => 'Delta Alliance'], ['labels_query' => true, 'layout' => 'vertical']);

        expect($html)->toContain('item-has-label', '<p class="item-label"></p>')
            ->not->toContain('item-has-longdescr')->not->toContain('item-text');
    }

    /** Keep nonstandard layouts free of grid and vertical descriptions. */
    public function test_omits_disabled_labels_and_descriptions_in_other_layouts(): void
    {
        Functions\expect('get_term')->once()->with(71)->andReturn((object) ['name' => 'Hidden label']);

        $html = LogoTemplateRenderer::item([
            'logo_title' => 'Delta Alliance',
            'logo_label' => 71,
            'logo_shortdescr' => 'Hidden short text',
            'logo_longdescr' => '<p>Hidden long text</p>',
            'logo_link' => '/delta',
            'logo_link_target' => '_self',
        ], ['layout' => 'experimental', 'links_query' => true, 'shortdescr_query' => true]);

        expect($html)->toContain('class="btn btn-primary"', '>Visit Their Website</a>')
            ->not->toContain('item-has-label')->not->toContain('item-label')->not->toContain('item-has-shortdescr')->not->toContain('item-has-longdescr')->not->toContain('Hidden label')->not->toContain('Hidden short text')->not->toContain('Hidden long text');
    }

    /** Preserve each breakpoint source and use the large image as fallback. */
    public function test_uses_every_responsive_source_and_large_fallback(): void
    {
        when('bis_get_attachment_image')->justReturn('available');
        foreach ([[101, 61], [203, 79], [307, 113], [409, 157], [503, 193], [601, 229], [709, 271]] as $index => $dimensions) {
            Functions\expect('bis_get_attachment_image_src')->once()->with(317, $dimensions)->andReturn(['src' => '/responsive-'.$index.'.png']);
        }

        $html = LogoTemplateRenderer::item(['logo_title' => 'Delta Alliance', 'logo_image' => 317]);

        preg_match_all('/<source srcset="([^"]+)"\s+media="([^"]+)">/', $html, $sources);
        expect($sources[1])->toBe(['/responsive-6.png', '/responsive-5.png', '/responsive-4.png', '/responsive-3.png', '/responsive-2.png', '/responsive-1.png', '/responsive-0.png'])
            ->and($sources[2])->toBe(['(min-width: 1921px)', '(min-width: 1500px)', '(min-width: 1200px)', '(min-width: 992px)', '(min-width: 768px)', '(min-width: 576px)', '(min-width: 1px)'])
            ->and($html)->toContain('<img src="/responsive-4.png" alt="" />')
            ->not->toContain('aspect-16-9');
    }

    /** Pass layout-specific dimensions and display options to the item template. */
    #[DataProvider('responsiveDimensions')]
    public function test_supplies_layout_specific_responsive_dimensions(
        string $layout,
        bool $featured,
        array $dimensions,
    ): void {
        Functions\expect('wp_reset_postdata')->once();
        when('bis_get_attachment_image')->justReturn('available');
        foreach ($dimensions as [$width, $height, $calls]) {
            Functions\expect('bis_get_attachment_image_src')->times($calls)->with(317, [$width, $height])->andReturn(['src' => '/sized-'.$width.'.png']);
        }

        $result = LogoTemplateRenderer::block([
            'block_logo_list_filter_ids' => [13],
            'block_logo_list_layout' => $layout,
            'block_logo_list_featured' => $featured,
            'block_logo_list_links' => true,
            'block_logo_list_links_target' => true,
            'block_logo_list_shortdescr' => true,
            'block_logo_list_width' => 'width-narrow',
        ], [47 => [
            'logo_title' => 'Delta Alliance',
            'logo_image' => 317,
            'logo_link' => '/delta',
            'logo_link_target' => 'inherit',
            'logo_shortdescr' => 'Grid summary',
            'logo_longdescr' => '<p>Vertical detail</p>',
        ]]);

        expect($result['html'])->toContain('logo-list-layout-'.$layout, 'target=_blank', '<source srcset=');
        expect(str_contains($result['html'], 'logo-list-featured'))->toBe($featured)
            ->and(str_contains($result['html'], 'Grid summary'))->toBe($layout === 'grid')
            ->and(str_contains($result['html'], 'Vertical detail'))->toBe($layout === 'vertical')
            ->and(str_contains($result['html'], 'width-narrow'))->toBe($layout === 'grid')
            ->and(str_contains($result['html'], 'item-title-hidden'))->toBe($layout === 'grid');
    }

    /** @return array<string, array{string, bool, list<array{int, int|float, int}>}> */
    public static function responsiveDimensions(): array
    {
        return [
            'regular grid' => ['grid', false, [[177, 99.5625, 4], [354, 199.125, 3]]],
            'featured grid' => ['grid', true, [[221, 124.3125, 4], [442, 248.625, 3]]],
            'regular vertical' => ['vertical', false, [[236, 177, 1], [172, 129, 6]]],
            'featured vertical' => ['vertical', true, [[236, 177, 1], [172, 129, 1], [224, 168, 1], [275, 206.25, 1], [350, 262.5, 1], [359, 269.25, 2]]],
        ];
    }

    /** Propagate grid controls rather than only exercising item-local overrides. */
    public function test_propagates_grid_titles_labels_desaturation_and_same_window_links(): void
    {
        Functions\expect('wp_reset_postdata')->once();
        Functions\expect('get_term')->once()->with(71)->andReturn((object) ['name' => 'Supporting partner']);

        $result = LogoTemplateRenderer::block([
            'block_logo_list_filter_ids' => [13],
            'block_logo_list_titles' => true,
            'block_logo_list_desaturate' => true,
            'block_logo_list_labels' => true,
            'block_logo_list_links' => true,
            'block_logo_list_links_target' => false,
        ], [47 => [
            'logo_title' => 'Delta Alliance',
            'logo_image' => 317,
            'logo_label' => 71,
            'logo_link' => '/delta',
            'logo_link_target' => 'inherit',
            'logo_shortdescr' => 'Disabled grid summary',
        ]]);

        expect($result['html'])
            ->toContain('item-has-title item-has-label item-has-link item-has-logo', 'item-img-desaturate', '<p class="item-label">Supporting partner</p>')
            ->toContain('<h3 class="item-title"', 'class="stretched-link"')
            ->not->toContain('item-title-hidden')->not->toContain('Disabled grid summary')->not->toContain('target=')->not->toContain('item-has-shortdescr');
    }

    /** Pin the nonstandard layout fallback rather than silently treating it as grid. */
    public function test_renders_other_layouts_without_grid_or_vertical_options(): void
    {
        Functions\expect('wp_reset_postdata')->once();

        $result = LogoTemplateRenderer::block([
            'block_logo_list_filter_ids' => [13],
            'block_logo_list_layout' => 'experimental',
            'block_logo_list_titles' => true,
            'block_logo_list_width' => 'width-narrow',
            'block_logo_list_shortdescr' => true,
        ], [47 => [
            'logo_title' => 'Delta Alliance',
            'logo_image' => 317,
            'logo_shortdescr' => 'Disabled short description',
            'logo_longdescr' => '<p>Disabled long description</p>',
        ]]);

        expect($result['html'])
            ->toContain('logo-list-layout-experimental', '<span class="sr-only">Delta Alliance</span>', '<div class="item-list-container">')
            ->not->toContain('item-has-title')->not->toContain('width-narrow')->not->toContain('Disabled short description')->not->toContain('Disabled long description');
    }

    /** The image service is currently consulted even for a missing attachment. */
    public function test_keeps_missing_images_hidden_when_the_image_service_is_available(): void
    {
        when('bis_get_attachment_image')->justReturn('available');
        Functions\expect('bis_get_attachment_image_src')->times(7)->with(false, Mockery::type('array'))->andReturn(['src' => '/must-not-display.png']);

        $html = LogoTemplateRenderer::item(['logo_title' => 'Delta Alliance']);

        expect($html)->toContain('<h3 class="item-title" id="partners-west_2_title">Delta Alliance</h3>')
            ->not->toContain('/must-not-display.png')->not->toContain('<picture>')->not->toContain('<source')->not->toContain('item-has-logo');
    }

    /** Keep the vertical list container free of the grid-only width class. */
    public function test_omits_the_width_class_from_vertical_lists(): void
    {
        Functions\expect('wp_reset_postdata')->once();

        $result = LogoTemplateRenderer::block([
            'block_logo_list_filter_ids' => [13],
            'block_logo_list_layout' => 'vertical',
            'block_logo_list_width' => 'width-narrow',
        ], [47 => ['logo_title' => 'Delta Alliance']]);

        expect($result['html'])->toContain('<div class="item-list-container">');
    }

    /** Default every disabled block option to off when the block leaves it unset. */
    public function test_leaves_desaturation_labels_and_links_off_by_default(): void
    {
        Functions\expect('wp_reset_postdata')->once();
        when('get_term')->justReturn((object) ['name' => 'Unused label']);

        $result = LogoTemplateRenderer::block([
            'block_logo_list_filter_ids' => [13],
        ], [47 => [
            'logo_title' => 'Delta Alliance',
            'logo_image' => 317,
            'logo_label' => 71,
            'logo_link' => '/delta',
            'logo_link_target' => '_self',
        ]]);

        expect($result['html'])
            ->toContain('<div class="item-img">', 'item item-has-logo')
            ->not->toContain('item-img-desaturate')
            ->not->toContain('Unused label')
            ->not->toContain('item-label')
            ->not->toContain('item-has-label')
            ->not->toContain('link-container')
            ->not->toContain('item-has-link');
    }

    /** Decode ampersands and accented characters in the more-button label. */
    public function test_decodes_entities_in_the_more_button_label(): void
    {
        Functions\expect('wp_reset_postdata')->once();

        $result = LogoTemplateRenderer::block([
            'block_logo_list_filter_ids' => [13],
            'block_logo_list_more' => true,
            'block_logo_list_more_text' => 'More partners',
            'block_logo_list_more_button_text' => 'Meet them',
            'block_logo_list_more_button_url' => ['url' => '/partners', 'target' => ''],
            'block_logo_list_more_button_description' => "Caf\u{00E9} & Bar partners",
        ], [47 => ['logo_title' => 'Delta Alliance']]);

        expect($result['html'])->toContain("aria-label=\"Caf\u{00E9} & Bar partners\"");
    }

    /** Pass empty dimensions to the image service for unrecognized layouts. */
    public function test_requests_unsized_images_for_other_layouts(): void
    {
        Functions\expect('wp_reset_postdata')->once();
        when('bis_get_attachment_image')->justReturn('available');
        Functions\expect('bis_get_attachment_image_src')->times(7)->with(317, ['', ''])->andReturn(['src' => '/unsized.png']);

        $result = LogoTemplateRenderer::block([
            'block_logo_list_filter_ids' => [13],
            'block_logo_list_layout' => 'experimental',
        ], [47 => ['logo_title' => 'Delta Alliance', 'logo_image' => 317]]);

        expect($result['html'])->toContain('<img src="/unsized.png" alt="" />');
    }

    /** Apply a chosen background color to the section and fall back to automatic. */
    #[DataProvider('sectionBackgrounds')]
    public function test_applies_the_background_color_to_the_section(
        mixed $background,
        string $expected,
    ): void {
        Functions\expect('wp_reset_postdata')->once();

        $result = LogoTemplateRenderer::block([
            'block_logo_list_filter_ids' => [13],
            'block_logo_list_background_color' => $background,
        ], [47 => ['logo_title' => 'Delta Alliance']]);

        expect($result['html'])->toContain('<section class="uams-module logo-list alignfull '.$expected.' logo-list-layout-grid"');
    }

    /** @return array<string, array{string|false, string}> */
    public static function sectionBackgrounds(): array
    {
        return [
            'chosen color' => ['bg-blue', 'bg-blue'],
            'automatic fallback' => [false, 'bg-auto'],
        ];
    }

    /** Leave the heading wrapper with no description markup when none is set. */
    public function test_renders_no_description_markup_without_a_description(): void
    {
        Functions\expect('wp_reset_postdata')->once();

        $result = LogoTemplateRenderer::block(['block_logo_list_filter_ids' => [13], 'block_logo_list_heading' => 'Partners'], [47 => ['logo_title' => 'Delta Alliance']]);

        expect($result['html'])->toMatch('#<span class="title">Partners</span>\s*</h2>\s*</div>#')
            ->not->toContain('module-description');
    }

    /** Combine every grid class when titles and short descriptions are both on. */
    public function test_combines_title_and_short_description_classes_in_grids(): void
    {
        $html = LogoTemplateRenderer::item([
            'logo_title' => 'Delta Alliance',
            'logo_image' => 317,
            'logo_shortdescr' => 'Local support',
        ], ['titles_query' => true, 'labels_query' => true, 'shortdescr_query' => true]);

        expect($html)->toContain('<div class="item item-has-title item-has-label item-has-shortdescr item-has-logo" id="partners-west_2">');
    }

    /** Mark long descriptions only for vertical items that have one. */
    #[DataProvider('longDescriptionLayouts')]
    public function test_marks_long_descriptions_only_in_vertical_lists_with_text(
        string $layout,
        mixed $longDescription,
    ): void {
        $html = LogoTemplateRenderer::item([
            'logo_title' => 'Delta Alliance',
            'logo_longdescr' => $longDescription,
        ], ['layout' => $layout]);

        expect($html)->not->toContain('item-has-longdescr');
    }

    /** @return array<string, array{string, string|false}> */
    public static function longDescriptionLayouts(): array
    {
        return [
            'grid with text' => ['grid', '<p>Detail</p>'],
            'vertical without text' => ['vertical', false],
        ];
    }

    /** Require both the block option and an item link for every link surface. */
    public function test_omits_every_link_surface_when_the_block_disables_links(): void
    {
        $html = LogoTemplateRenderer::item([
            'logo_title' => 'Delta Alliance',
            'logo_image' => 317,
            'logo_link' => '/delta',
            'logo_link_target' => '_self',
        ], ['links_query' => false]);

        expect($html)
            ->toContain('<h3 class="item-title item-title-hidden" id="partners-west_2_title">')
            ->not->toContain('item-has-link')
            ->not->toContain('stretched-link')
            ->not->toContain('<a ')
            ->not->toContain('link-container');
    }

    /** Render the plain image class when desaturation is off. */
    public function test_renders_the_plain_image_class_without_desaturation(): void
    {
        $html = LogoTemplateRenderer::item(['logo_title' => 'Delta Alliance', 'logo_image' => 317], ['desaturate_query' => false]);

        expect($html)->toContain('<div class="item-img">');
    }

    /** Pin the more-button target, which is currently computed and never printed. */
    public function test_discards_the_more_button_target(): void
    {
        Functions\expect('wp_reset_postdata')->once();

        $result = LogoTemplateRenderer::block([
            'block_logo_list_filter_ids' => [13],
            'block_logo_list_more' => true,
            'block_logo_list_more_text' => 'More partners',
            'block_logo_list_more_button_text' => 'Meet them',
            'block_logo_list_more_button_url' => ['url' => '/partners', 'target' => '_blank'],
            'block_logo_list_more_button_description' => 'All partners',
        ], [47 => ['logo_title' => 'Delta Alliance']]);

        expect($result['html'])->toContain('aria-label="All partners">Meet them</a>')
            ->not->toContain('target=');
    }

    /** Keep a missing image value unchanged when the attachment lookup finds nothing. */
    public function test_passes_a_missing_image_value_to_the_image_service_unchanged(): void
    {
        when('wp_get_attachment_image')->justReturn('');
        when('bis_get_attachment_image')->justReturn('available');
        $received = [];
        when('bis_get_attachment_image_src')->alias(function (mixed $image) use (&$received): array {
            $received[] = $image;

            return ['src' => '/unused.png'];
        });

        LogoTemplateRenderer::item(['logo_title' => 'Delta Alliance', 'logo_image' => false]);

        expect($received)->toHaveCount(7)
            ->and(array_filter($received, static fn (mixed $image): bool => $image !== false))->toBe([]);
    }
}
