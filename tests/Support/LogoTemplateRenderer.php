<?php

declare(strict_types=1);

namespace UamswpLogoList\Tests\Support;

use Mockery;

use function Brain\Monkey\Functions\stubTranslationFunctions;
use function Brain\Monkey\Functions\when;

/**
 * Capture the real templates without loading WordPress or the plugin entry point.
 */
final class LogoTemplateRenderer
{
    /**
     * Render a block with a finite, ordered query result.
     *
     * @param  array<string, mixed>  $fields  ACF block values.
     * @param  array<int, array<string, mixed>>  $posts  Item fields keyed by post ID.
     * @param  array<string, mixed>  $variables  Values supplied by the caller.
     * @return array{html: string, args: array<string, mixed>} Output and query input.
     */
    public static function block(
        array $fields = [],
        array $posts = [],
        array $variables = [],
    ): array {
        // Keep each field associated with its own post rather than its call order.
        self::fields($fields, $posts);
        stubTranslationFunctions();
        self::passEscapingThrough();
        \defined('UAMS_LOGO_PATH') || define('UAMS_LOGO_PATH', dirname(__DIR__, 2));

        // Model query iteration, including the pre-loop have_posts checks.
        $args = [];
        $cursor = 0;
        $ids = array_keys($posts);
        $query = Mockery::mock('overload:WP_Query');
        $query->shouldReceive('__construct')->once()->withArgs(function (array $input) use (&$args): bool {
            $args = $input;

            return true;
        });
        $query->shouldReceive('have_posts')->andReturnUsing(function () use (&$cursor, $ids): bool {
            return $cursor < \count($ids);
        });
        $query->shouldReceive('the_post')->andReturnUsing(function () use (&$cursor): void {
            $cursor++;
        });
        when('get_the_ID')->alias(function () use (&$cursor, $ids): int {
            return $ids[$cursor - 1];
        });

        // Include in a local scope, preserving caller-supplied template values.
        $html = self::capture('blocks/logo-list.php', $variables);

        return ['html' => $html, 'args' => $args];
    }

    /**
     * Return every value the templates escape unchanged.
     *
     * These tests characterize layout and field handling; the escaping itself is
     * pinned in `LogoTemplateEscapingTest`, so the escapers are identities here.
     */
    private static function passEscapingThrough(): void
    {
        foreach (['esc_html', 'esc_attr', 'esc_url', 'sanitize_html_class', 'wp_kses_post'] as $escaper) {
            when($escaper)->returnArg();
        }
    }

    /**
     * Render one item with deliberately distinct dimensions at every breakpoint.
     *
     * @param  array<string, mixed>  $fields  ACF item values for post 47.
     * @param  array<string, mixed>  $variables  Overrides for the parent block scope.
     * @return string The captured item markup.
     */
    public static function item(
        array $fields = [],
        array $variables = [],
    ): string {
        self::fields([], [47 => $fields]);
        self::passEscapingThrough();

        return self::capture('loops/logo-list-item.php', array_replace([
            'id' => 'partners-west',
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
            'image_width_xxs' => 101,
            'image_height_xxs' => 61,
            'image_width_xs' => 203,
            'image_height_xs' => 79,
            'image_width_sm' => 307,
            'image_height_sm' => 113,
            'image_width_md' => 409,
            'image_height_md' => 157,
            'image_width_lg' => 503,
            'image_height_lg' => 193,
            'image_width_xl' => 601,
            'image_height_xl' => 229,
            'image_width_xxl' => 709,
            'image_height_xxl' => 271,
        ], $variables));
    }

    /**
     * Stub only the ACF boundary; individual tests own all other expectations.
     *
     * @param  array<string, mixed>  $block  Block field values.
     * @param  array<int, array<string, mixed>>  $posts  Per-post field values.
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
     * Include a template and always release its output buffer on failure.
     *
     * @param  string  $template  Path relative to templates/.
     * @param  array<string, mixed>  $variables  Template scope.
     * @return string The unmodified output.
     */
    private static function capture(
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
