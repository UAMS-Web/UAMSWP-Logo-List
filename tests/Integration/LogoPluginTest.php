<?php

declare(strict_types=1);

namespace UamswpLogoList\Tests\Integration;

use TGM_Plugin_Activation;
use UamswpLogoList\Tests\Support\IntegrationTestCase;
use WP_Post_Type;
use WP_Query;
use WP_Taxonomy;

/**
 * Characterize plugin startup and persistence against WordPress itself.
 *
 * @command  composer test:integration -- --filter LogoPluginTest
 */
final class LogoPluginTest extends IntegrationTestCase
{
    /**
     * Verify metadata constants and stable callback identities after startup.
     */
    public function test_plugin_metadata_and_hook_wiring(): void
    {
        // Read the actual plugin header rather than duplicating its version.
        $file = dirname(__DIR__, 2).'/uamswp-logo-list.php';
        $header = get_file_data($file, ['version' => 'Version']);
        self::assertSame(plugin_dir_path($file), UAMS_LOGO_PATH);
        self::assertSame(plugin_dir_url($file), UAMS_LOGO_ROOT_URL);
        self::assertSame($header['version'], UAMS_LOGO_VERSION);

        // Check priorities as well as callback names, including priority zero.
        self::assertSame(10, has_action('plugins_loaded', 'UAMS\\Logo_List\\bootstrap'));
        self::assertSame(0, has_action('init', 'logo_list_items_cpt'));
        self::assertSame(0, has_action('init', 'create_logo_list_category'));
        self::assertSame(0, has_action('init', 'create_logo_list_label_tag'));
        self::assertSame(10, has_action('init', 'uamswp_logo_list_role_caps'));
        self::assertSame(10, has_action('admin_menu', 'remove_logo_taxonomy_meta'));
        self::assertSame(10, has_action('wp_enqueue_scripts', 'logo_script_register'));
        self::assertSame(10, has_filter('acf/settings/load_json', 'uamswp_logo_json_load_point'));
        self::assertSame(10, has_filter('acf/fields/wysiwyg/toolbars', 'uamswp_logo_toolbars'));
        self::assertSame(10, has_action('tgmpa_register', 'uamswp_logo_register_required_plugins'));

        // Confirm ACF filters work through WordPress even without ACF installed.
        self::assertSame(
            ['/theme/acf-json', UAMS_LOGO_PATH.'assets/json/acf-json'],
            apply_filters('acf/settings/load_json', ['/theme/acf-json'])
        );
        self::assertSame(
            ['Very Simple' => [1 => ['bold', 'italic', 'bullist', 'numlist', 'removeformat']]],
            apply_filters('acf/fields/wysiwyg/toolbars', [])
        );
    }

    /**
     * Verify init registered usable post and taxonomy objects, not just hooks.
     */
    public function test_wordpress_registered_the_logo_content_model(): void
    {
        // Inspect the resulting post type and WordPress's derived capabilities.
        $type = get_post_type_object('logo-list-item');
        self::assertInstanceOf(WP_Post_Type::class, $type);
        self::assertTrue($type->public);
        self::assertTrue($type->show_in_rest);
        self::assertTrue($type->has_archive);
        self::assertSame('logo_list_item', $type->rest_base);
        self::assertSame('edit_logo_list_item', $type->cap->edit_post);
        self::assertSame('read_logo_list_item', $type->cap->read);
        self::assertSame('publish_logo_list_items', $type->cap->publish_posts);
        self::assertSame(
            ['title', 'author', 'revisions', 'custom-fields', 'page-attributes'],
            array_keys(get_all_post_type_supports('logo-list-item'))
        );

        // Confirm both taxonomies belong to logos with distinct hierarchy rules.
        foreach (['logo-list-category' => true, 'logo-list-label' => false] as $name => $hierarchical) {
            $taxonomy = get_taxonomy($name);
            self::assertInstanceOf(WP_Taxonomy::class, $taxonomy);
            self::assertSame(['logo-list-item'], $taxonomy->object_type);
            self::assertSame($hierarchical, $taxonomy->hierarchical);
            self::assertFalse($taxonomy->public);
            self::assertTrue($taxonomy->show_ui);
            self::assertSame('manage_logo_list_taxonomies', $taxonomy->cap->manage_terms);
            self::assertSame('edit_logo_list_items', $taxonomy->cap->assign_terms);
            self::assertTrue(is_object_in_taxonomy('logo-list-item', $name));
        }
    }

    /**
     * Verify privileges are usable and remain limited to the intended roles.
     */
    public function test_only_administrators_and_editors_receive_logo_capabilities(): void
    {
        // Check every granted capability on both roles and negative controls.
        $capabilities = [
            'edit_logo_list_item', 'read_logo_list_item', 'delete_logo_list_item',
            'edit_logo_list_items', 'edit_others_logo_list_items', 'delete_logo_list_items',
            'publish_logo_list_items', 'read_private_logo_list_items',
            'delete_private_logo_list_items', 'delete_published_logo_list_items',
            'delete_others_logo_list_items', 'edit_private_logo_list_items',
            'edit_published_logo_list_items', 'manage_logo_list_taxonomies',
        ];
        foreach (['administrator', 'editor', 'author', 'contributor', 'subscriber'] as $name) {
            $role = get_role($name);
            self::assertNotNull($role);
            foreach ($capabilities as $capability) {
                self::assertSame(
                    \in_array($name, ['administrator', 'editor'], true),
                    $role->has_cap($capability),
                    $name.': '.$capability
                );
            }
        }

        // Verify WordPress resolves the registered primitive caps for an editor.
        $editor = self::factory()->user->create(['role' => 'editor']);
        self::assertTrue(user_can($editor, 'publish_logo_list_items'));
        self::assertTrue(user_can($editor, 'manage_logo_list_taxonomies'));
    }

    /**
     * Verify logo posts and their category/label relationships round-trip.
     */
    public function test_logo_queries_select_related_posts_and_exclude_unrelated_content(): void
    {
        // Create controls for another category, a draft, and a normal post.
        $matching = self::factory()->post->create(['post_type' => 'logo-list-item', 'post_status' => 'publish']);
        $other = self::factory()->post->create(['post_type' => 'logo-list-item', 'post_status' => 'publish']);
        $draft = self::factory()->post->create(['post_type' => 'logo-list-item', 'post_status' => 'draft']);
        $ordinary = self::factory()->post->create(['post_type' => 'post', 'post_status' => 'publish']);
        $parent = self::factory()->term->create(['taxonomy' => 'logo-list-category', 'name' => 'Partners']);
        $child = self::factory()->term->create(['taxonomy' => 'logo-list-category', 'name' => 'Hospitals', 'parent' => $parent]);
        $label = self::factory()->term->create(['taxonomy' => 'logo-list-label', 'name' => 'Featured']);

        // Store relationships through the public WordPress API.
        self::assertNotWPError(wp_set_object_terms($matching, [$child], 'logo-list-category'));
        self::assertNotWPError(wp_set_object_terms($draft, [$child], 'logo-list-category'));
        self::assertNotWPError(wp_set_object_terms($ordinary, [$child], 'logo-list-category'));
        self::assertNotWPError(wp_set_object_terms($matching, [$label], 'logo-list-label'));
        self::assertNotWPError(wp_set_object_terms($draft, [$label], 'logo-list-label'));
        self::assertNotWPError(wp_set_object_terms($ordinary, [$label], 'logo-list-label'));
        self::assertNotWPError(wp_set_object_terms($other, [$label], 'logo-list-label'));
        self::assertFalse(has_term($child, 'logo-list-category', $other));
        self::assertTrue(has_term($label, 'logo-list-label', $matching));

        // Query the parent category to exercise hierarchical child inclusion.
        $query = new WP_Query([
            'post_type' => 'logo-list-item',
            'post_status' => 'publish',
            'fields' => 'ids',
            'tax_query' => [[
                'taxonomy' => 'logo-list-category',
                'field' => 'term_id',
                'terms' => [$parent],
            ], [
                'taxonomy' => 'logo-list-label',
                'field' => 'term_id',
                'terms' => [$label],
            ]],
        ]);
        self::assertSame([$matching], $query->posts);
        self::assertSame('logo-list-item', get_post_type($matching));
        self::assertSame($parent, (int) get_term($child, 'logo-list-category')->parent);
    }

    /**
     * Verify the real TGMPA callback registers requirements and configuration.
     */
    public function test_required_plugin_callback_configures_tgmpa(): void
    {
        // Invoke the actual hook without downloading or activating dependencies.
        do_action('tgmpa_register');
        $instance = TGM_Plugin_Activation::get_instance();

        // Check required packages and distinguish bundled from repository sources.
        foreach ([
            'advanced-custom-fields-pro' => ['Advanced Custom Fields PRO', UAMS_LOGO_PATH.'plugins/advanced-custom-fields-pro.zip'],
            'acf-maskfield' => ['Advanced Custom Fields: Mask Field', UAMS_LOGO_PATH.'plugins/acf-maskfield.zip'],
            'acf-extended' => ['Advanced Custom Fields: Extended', 'repo'],
        ] as $slug => [$name, $source]) {
            self::assertArrayHasKey($slug, $instance->plugins);
            self::assertSame($name, $instance->plugins[$slug]['name']);
            self::assertSame($source, $instance->plugins[$slug]['source']);
            self::assertTrue($instance->plugins[$slug]['required']);
        }

        // Pin public installer configuration without depending on protected state.
        self::assertSame('uamswp', $instance->id);
        self::assertSame('', $instance->default_path);
        self::assertSame('plugins.php', $instance->parent_slug);
        self::assertSame('manage_options', $instance->capability);
        self::assertTrue($instance->has_notices);
        self::assertTrue($instance->dismissable);
        self::assertFalse($instance->is_automatic);
        self::assertSame('', $instance->message);
    }
}
