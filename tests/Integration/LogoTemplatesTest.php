<?php

declare(strict_types=1);

use UamswpLogoList\Tests\Support\IntegrationTestCase;

/**
 * Supply the ACF boundary while preserving real WordPress queries and metadata.
 *
 * @param  string  $selector  ACF field name requested by the template.
 * @param  int|false  $postId  Item ID, or false for the current block's fields.
 * @return mixed The stored fixture value, without implementing ACF behavior.
 */
function get_field(
    string $selector,
    int|false $postId = false,
): mixed {
    return $postId === false
        ? ($GLOBALS['logo_template_fields'][$selector] ?? false)
        : get_post_meta($postId, $selector, true);
}

/**
 * Pin block-to-item rendering against persisted WordPress posts and terms.
 *
 * @command  composer test:integration -- --filter LogoTemplatesTest
 */
final class LogoTemplatesTest extends IntegrationTestCase
{
    /**
     * Require the category and label intersection, ordering, limit and cleanup.
     */
    public function test_the_block_renders_the_actual_filtered_query_and_restores_the_main_post(): void
    {
        // Make asymmetric taxonomy controls and logo ordering values.
        $category = self::factory()->term->create(['taxonomy' => 'logo-list-category']);
        $otherCategory = self::factory()->term->create(['taxonomy' => 'logo-list-category']);
        $label = self::factory()->term->create(['taxonomy' => 'logo-list-label']);
        $otherLabel = self::factory()->term->create(['taxonomy' => 'logo-list-label']);
        $rows = [
            ['Zulu Logo', 'logo-list-item', 'publish', 20, $category, $label],
            ['Alpha Logo', 'logo-list-item', 'publish', 5, $category, $label],
            ['Wrong Label', 'logo-list-item', 'publish', 30, $category, $otherLabel],
            ['Wrong Category', 'logo-list-item', 'publish', 40, $otherCategory, $label],
            ['Draft Logo', 'logo-list-item', 'draft', 50, $category, $label],
            ['Ordinary Post', 'post', 'publish', 60, $category, $label],
        ];
        foreach ($rows as [$title, $type, $status, $order, $categoryId, $labelId]) {
            $postId = self::factory()->post->create([
                'post_type' => $type,
                'post_status' => $status,
                'post_title' => 'Native '.$title,
                'menu_order' => $order,
            ]);
            update_post_meta($postId, 'logo_title', $title);
            wp_set_object_terms($postId, [$categoryId], 'logo-list-category');
            wp_set_object_terms($postId, [$labelId], 'logo-list-label');
        }

        // Establish a real main-query post for wp_reset_postdata() to restore.
        $context = self::factory()->post->create(['post_title' => 'Context']);
        $this->go_to(get_permalink($context));
        $GLOBALS['wp_query']->the_post();
        self::assertSame($context, get_the_ID());
        $fields = [
            'block_logo_list_heading' => 'Partner Organizations',
            'block_logo_list_filter_ids' => [$category],
            'block_logo_list_labels_ids' => [$label],
            'block_logo_list_order' => ['menu_order|DESC'],
            'block_logo_list_max' => 2,
        ];

        // Render the actual block and included item template using WP_Query.
        $html = $this->render($fields);
        self::assertStringContainsString('Partner Organizations', $html);
        self::assertStringContainsString('Zulu Logo', $html);
        self::assertStringContainsString('Alpha Logo', $html);
        self::assertLessThan(strpos($html, 'Alpha Logo'), strpos($html, 'Zulu Logo'));
        foreach (['Wrong Label', 'Wrong Category', 'Draft Logo', 'Ordinary Post', 'Native Zulu'] as $excluded) {
            self::assertStringNotContainsString($excluded, $html);
        }

        self::assertSame($context, get_the_ID());

        // Distinguish a real query limit from merely truncating fixture output.
        $fields['block_logo_list_max'] = 1;
        $limited = $this->render($fields);
        self::assertStringContainsString('Zulu Logo', $limited);
        self::assertStringNotContainsString('Alpha Logo', $limited);
        self::assertSame($context, get_the_ID());
    }

    /**
     * Keep empty front-end output distinct from the editor's two placeholders.
     */
    public function test_empty_queries_and_missing_categories_render_the_expected_preview(): void
    {
        // Exercise the missing-category branch through a real empty WP_Query.
        self::assertSame('', trim($this->render([])));
        self::assertStringContainsString('choose at least one category', $this->render([], true));

        // Exercise a configured category that has no published matching logos.
        $category = self::factory()->term->create(['taxonomy' => 'logo-list-category']);
        $fields = ['block_logo_list_filter_ids' => [$category]];
        self::assertSame('', trim($this->render($fields)));
        self::assertStringContainsString('No published logos match', $this->render($fields, true));
    }

    /**
     * Render with block-field boundary values, leaving WordPress itself intact.
     *
     * @param  array<string, mixed>  $fields  Values returned at the ACF boundary.
     * @param  bool  $preview  Whether the editor requests a preview.
     * @return string Actual template output.
     */
    private function render(
        array $fields,
        bool $preview = false,
    ): string {
        // Set only ACF's boundary and the variables WordPress passes to a block.
        $GLOBALS['logo_template_fields'] = $fields;
        $block = ['id' => 'integration'];
        $is_preview = $preview;
        ob_start();

        try {
            // Include the production template without substituting its query.
            require UAMS_LOGO_PATH.'/templates/blocks/logo-list.php';

            return (string) ob_get_contents();
        } finally {
            // Restore the output buffer and remove the block's field values.
            ob_end_clean();
            unset($GLOBALS['logo_template_fields']);
        }
    }
}
