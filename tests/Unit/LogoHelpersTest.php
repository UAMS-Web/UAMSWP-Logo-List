<?php

/**
 * Characterize the logo registration and ACF helpers without WordPress.
 *
 * @command  vendor/bin/pest tests/Unit/LogoHelpersTest.php
 */

declare(strict_types=1);

use Brain\Monkey\Functions;

beforeEach(function (): void {
    // Load definitions only once because several callbacks are unguarded.
    defined('UAMS_LOGO_PATH') || define('UAMS_LOGO_PATH', '/example/plugins/logo-list/');
    require_once dirname(__DIR__, 2).'/includes/class.logo-acf-functions.php';
    require_once dirname(__DIR__, 2).'/includes/class.logo-custom-post.php';
});

it('appends the bundled ACF directory without discarding or reindexing existing paths', function (): void {
    // Preserve both the default directory and another plugin's named directory.
    $paths = [0 => '/theme/acf-json', 'other-plugin' => '/other/acf-json', 7 => '/shared/acf-json'];

    expect(uamswp_logo_json_load_point($paths))->toBe([
        0 => '/theme/acf-json',
        'other-plugin' => '/other/acf-json',
        7 => '/shared/acf-json',
        8 => UAMS_LOGO_PATH.'assets/json/acf-json',
    ])->and($paths)->toHaveCount(3);
});

it('provides the bundled ACF directory when no other directories exist', function (): void {
    expect(uamswp_logo_json_load_point([]))->toBe([UAMS_LOGO_PATH.'assets/json/acf-json']);
});

it('replaces only the Very Simple toolbar and leaves other toolbars intact', function (): void {
    // Include a previous toolbar with extra rows to detect accidental merging.
    $toolbars = [
        'Full' => [1 => ['bold', 'link'], 2 => ['undo', 'redo']],
        'Very Simple' => [1 => ['link'], 2 => ['undo']],
        'Custom' => [1 => ['code']],
    ];

    expect(uamswp_logo_toolbars($toolbars))->toBe([
        'Full' => $toolbars['Full'],
        'Very Simple' => [1 => ['bold', 'italic', 'bullist', 'numlist', 'removeformat']],
        'Custom' => $toolbars['Custom'],
    ])->and(uamswp_logo_toolbars([]))->toBe([
        'Very Simple' => [1 => ['bold', 'italic', 'bullist', 'numlist', 'removeformat']],
    ]);
});

it('registers the public logo post type with its editor features and custom capabilities', function (): void {
    // Observe exactly what the registration callback passes to WordPress.
    $arguments = null;
    Functions\expect('register_post_type')->once()->with(
        'logo-list-item',
        Mockery::on(static function (array $args) use (&$arguments): bool {
            $arguments = $args;

            return true;
        })
    );
    logo_list_items_cpt();

    // Pin the complete registration apart from its separately checked labels.
    $labels = $arguments['labels'];
    unset($arguments['labels']);
    expect($arguments)->toBe([
        'label' => 'Logo List Items',
        'description' => 'Items for display in lists of logos on UAMS websites',
        'supports' => ['title', 'author', 'revisions', 'custom-fields', 'page-attributes'],
        'taxonomies' => ['logo-list-category', 'logo-list-label'],
        'hierarchical' => false,
        'public' => true,
        'show_ui' => true,
        'show_in_menu' => true,
        'menu_position' => 30,
        'menu_icon' => 'dashicons-images-alt',
        'show_in_admin_bar' => true,
        'show_in_nav_menus' => true,
        'can_export' => true,
        'has_archive' => true,
        'exclude_from_search' => false,
        'publicly_queryable' => true,
        'capabilities' => [
            'edit_post' => 'edit_logo_list_item',
            'read_post' => 'read_logo_list_item',
            'delete_post' => 'delete_logo_list_item',
            'edit_posts' => 'edit_logo_list_items',
            'edit_others_posts' => 'edit_others_logo_list_items',
            'delete_posts' => 'delete_logo_list_items',
            'publish_posts' => 'publish_logo_list_items',
            'read_private_posts' => 'read_private_logo_list_items',
            'read' => 'read_logo_list_item',
            'delete_private_posts' => 'delete_private_logo_list_items',
            'delete_published_posts' => 'delete_published_logo_list_items',
            'delete_others_posts' => 'delete_others_logo_list_items',
            'edit_private_posts' => 'edit_private_logo_list_items',
            'edit_published_posts' => 'edit_published_logo_list_items',
        ],
        'show_in_rest' => true,
        'rest_base' => 'logo_list_item',
        'rest_controller_class' => 'WP_REST_Posts_Controller',
    ])->and($labels)->toBe([
        'name' => 'Logo List Items',
        'singular_name' => 'Logo List Item',
        'menu_name' => 'Logo List Items',
        'name_admin_bar' => 'Logo List Item',
        'archives' => 'Logo List Item Archives',
        'attributes' => 'Logo List Item Attributes',
        'parent_item_colon' => 'Parent Item:',
        'all_items' => 'All Items',
        'add_new_item' => 'Add New Item',
        'add_new' => 'Add New',
        'new_item' => 'New Item',
        'edit_item' => 'Edit Item',
        'update_item' => 'Update Item',
        'view_item' => 'View Item',
        'view_items' => 'View Items',
        'search_items' => 'Search Items',
        'uploaded_to_this_item' => 'Uploaded to this item',
        'items_list' => 'Items list',
        'items_list_navigation' => 'Items list navigation',
        'filter_items_list' => 'Filter Items list',
    ]);
});

it('registers the logo taxonomy labels, permissions and editor visibility', function (
    string $callback,
    string $taxonomy,
    string $singular,
    string $plural,
    bool $hierarchical,
): void {
    // Verify each taxonomy's complete arguments, not WordPress defaults.
    Functions\expect('register_taxonomy')->once()->with($taxonomy, ['logo-list-item'], [
        'labels' => [
            'name' => 'Logo Item '.$plural,
            'singular_name' => 'Logo Item '.$singular,
            'menu_name' => $plural,
            'all_items' => 'All Logo Item '.$plural,
            'parent_item' => 'Parent Logo Item '.$singular,
            'parent_item_colon' => 'Parent Logo Item '.$singular.':',
            'new_item_name' => 'New Logo Item '.$singular,
            'add_new_item' => 'Add New Logo Item '.$singular,
            'edit_item' => 'Edit Logo Item '.$singular,
            'update_item' => 'Update Logo Item '.$singular,
            'view_item' => 'View Logo Item '.$singular,
            'separate_items_with_commas' => 'Separate Logo Item '.$plural.' with commas',
            'add_or_remove_items' => 'Add or remove Logo Item '.$plural,
            'choose_from_most_used' => 'Choose from the most used',
            'popular_items' => 'Popular Logo Item '.$plural,
            'search_items' => 'Search Logo Item '.$plural,
            'not_found' => 'Not Found',
            'no_terms' => 'No Logo Item '.$plural,
            'items_list' => 'Logo Item '.$plural.' list',
            'items_list_navigation' => 'Logo Item '.$plural.' list navigation',
        ],
        'public' => false,
        'hierarchical' => $hierarchical,
        'show_ui' => true,
        'show_in_nav_menus' => false,
        'show_tagcloud' => false,
        'show_in_quick_edit' => true,
        'show_admin_column' => true,
        'capabilities' => [
            'manage_terms' => 'manage_logo_list_taxonomies',
            'edit_terms' => 'manage_logo_list_taxonomies',
            'delete_terms' => 'manage_logo_list_taxonomies',
            'assign_terms' => 'edit_logo_list_items',
        ],
        'rewrite' => ['slug' => $taxonomy, 'with_front' => false, 'hierarchical' => false],
    ]);

    $callback();
})->with([
    ['create_logo_list_category', 'logo-list-category', 'Category', 'Categories', true],
    ['create_logo_list_label_tag', 'logo-list-label', 'Label', 'Labels', false],
]);

it('removes only the default logo taxonomy metaboxes', function (): void {
    // Pin both IDs and the screen/context to avoid affecting unrelated editors.
    Functions\expect('remove_meta_box')->once()->with('logo-list-categorydiv', 'logo-list-item', 'side');
    Functions\expect('remove_meta_box')->once()->with('tagsdiv-logo-list-label', 'logo-list-item', 'side');

    remove_logo_taxonomy_meta();
});

it('tolerates missing default roles while granting the surviving role every logo capability', function (): void {
    // Exercise the absent-role branch and continue to the next role.
    $editor = Mockery::mock();
    Functions\expect('get_role')->once()->with('administrator')->andReturn(null);
    Functions\expect('get_role')->once()->with('editor')->andReturn($editor);

    // Enumerate the intended grants independently of the production capability map.
    foreach ([
        'edit_logo_list_item', 'read_logo_list_item', 'delete_logo_list_item',
        'edit_logo_list_items', 'edit_others_logo_list_items', 'delete_logo_list_items',
        'publish_logo_list_items', 'read_private_logo_list_items',
        'delete_private_logo_list_items', 'delete_published_logo_list_items',
        'delete_others_logo_list_items', 'edit_private_logo_list_items',
        'edit_published_logo_list_items', 'manage_logo_list_taxonomies',
    ] as $capability) {
        $editor->shouldReceive('add_cap')->once()->with($capability);
    }

    uamswp_logo_list_role_caps();
});
