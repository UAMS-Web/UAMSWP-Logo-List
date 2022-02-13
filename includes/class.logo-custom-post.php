<?php

// Register Custom Post Type
if ( ! function_exists('logo_list_items_cpt') ) {
	function logo_list_items_cpt() {
	
		$labels = array(
			'name'                  => 'Logo List Items',
			'singular_name'         => 'Logo List Item',
			'menu_name'             => 'Logo List Items',
			'name_admin_bar'        => 'Logo List Item',
			'archives'              => 'Logo List Item Archives',
			'attributes'            => 'Logo List Item Attributes',
			'parent_item_colon'     => 'Parent Item:',
			'all_items'             => 'All Items',
			'add_new_item'          => 'Add New Item',
			'add_new'               => 'Add New',
			'new_item'              => 'New Item',
			'edit_item'             => 'Edit Item',
			'update_item'           => 'Update Item',
			'view_item'             => 'View Item',
			'view_items'            => 'View Items',
			'search_items'          => 'Search Items',
			'uploaded_to_this_item' => 'Uploaded to this item',
			'items_list'            => 'Items list',
			'items_list_navigation' => 'Items list navigation',
			'filter_items_list'     => 'Filter Items list',
		);
		$capabilities = array(
			'edit_post'      		=> "edit_logo_list_item",
			'read_post'      		=> "read_logo_list_item",
			'delete_post'        	=> "delete_logo_list_item",
			'edit_posts'         	=> "edit_logo_list_items",
			'edit_others_posts'  	=> "edit_others_logo_list_items",
			'publish_posts'      	=> "publish_logo_list_items",
			'read_private_posts'    => "read_private_logo_list_items",
			'read'                  => "read",
			'delete_posts'          => "delete_logo_list_items",
			'delete_private_posts'  => "delete_private_logo_list_items",
			'delete_published_posts' => "delete_published_logo_list_items",
			'delete_others_posts'   => "delete_others_logo_list_items",
			'edit_private_posts'    => "edit_private_logo_list_items",
			'edit_published_posts'  => "edit_published_logo_list_items",
		);
		$args = array(
			'label'                 => 'Logo List Items',
			'description'           => 'Items for display in lists of logos on UAMS websites',
			'labels'                => $labels,
			'supports'              => array( 'title', 'author', 'revisions','custom-fields', 'page-attributes' ),
			'taxonomies'            => array(),
			'hierarchical'          => false,
			'public'                => true,
			'show_ui'               => true,
			'show_in_menu'          => true,
			'menu_position'         => 30,
			'menu_icon'             => 'dashicons-images-alt',
			'show_in_admin_bar'     => true,
			'show_in_nav_menus'     => true,
			'can_export'            => true,
			'has_archive'           => true,
			'exclude_from_search'   => false,
			'publicly_queryable'    => true,
			'capabilities'          => $capabilities,
			'show_in_rest'          => true,
			'rest_base'             => 'logo_list_item',
			'rest_controller_class' => 'WP_REST_Posts_Controller',
		);
		register_post_type( 'logo-list-item', $args );
	
	}
	add_action( 'init', 'logo_list_items_cpt', 0 );
	
}

// Register Taxonomies
// Place in order for sub-menu
if ( ! function_exists('create_logo_list_category') ) {
	function create_logo_list_category() {
		$labels = array(
			'name'                       => 'Logo Item Categories',
			'singular_name'              => 'Logo Item Category',
			'menu_name'                  => 'Categories',
			'all_items'                  => 'All Logo Item Categories',
			'parent_item'                => 'Parent Logo Item Category',
			'parent_item_colon'          => 'Parent Logo Item Category:',
			'new_item_name'              => 'New Logo Item Category',
			'add_new_item'               => 'Add New Logo Item Category',
			'edit_item'                  => 'Edit Logo Item Category',
			'update_item'                => 'Update Logo Item Category',
			'view_item'                  => 'View Logo Item Category',
			'separate_items_with_commas' => 'Separate Logo Item Categories with commas',
			'add_or_remove_items'        => 'Add or remove Logo Item Categories',
			'choose_from_most_used'      => 'Choose from the most used',
			'popular_items'              => 'Popular Logo Item Categories',
			'search_items'               => 'Search Logo Item Categories',
			'not_found'                  => 'Not Found',
			'no_terms'                   => 'No Logo Item Categories',
			'items_list'                 => 'Logo Item Categories list',
			'items_list_navigation'      => 'Logo Item Categories list navigation',
		);
		$rewrite = array(
			'slug'                       => 'logo-list-category',
			'with_front'                 => false,
			'hierarchical'               => false,
		);
		$capabilities = array(
			'manage_terms'               => 'manage_options',
			'edit_terms'                 => 'manage_options',
			'delete_terms'               => 'manage_options',
			'assign_terms'               => 'manage_options',
		);
		$args = array(
			'labels'                     => $labels,
			'hierarchical'               => true,
			'public'                     => false,
			'show_ui'                    => true,
			'show_admin_column'          => true,
			'show_in_nav_menus'          => false,
			'show_tagcloud'              => false,
			'rewrite'                    => $rewrite,
			'capabilities'               => $capabilities,
			'show_in_quick_edit'         => true,
		);
		register_taxonomy( 'logo-list-category', array( 'logo-list-item' ), $args );
	
	}
	add_action( 'init', 'create_logo_list_category', 0 );
}
if ( ! function_exists('create_logo_list_label_tag') ) {
	function create_logo_list_label_tag() {
		$labels = array(
			'name'                       => 'Logo Item Labels',
			'singular_name'              => 'Logo Item Label',
			'menu_name'                  => 'Labels',
			'all_items'                  => 'All Logo Item Labels',
			'parent_item'                => 'Parent Logo Item Label',
			'parent_item_colon'          => 'Parent Logo Item Label:',
			'new_item_name'              => 'New Logo Item Label',
			'add_new_item'               => 'Add New Logo Item Label',
			'edit_item'                  => 'Edit Logo Item Label',
			'update_item'                => 'Update Logo Item Label',
			'view_item'                  => 'View Logo Item Label',
			'separate_items_with_commas' => 'Separate Logo Item Labels with commas',
			'add_or_remove_items'        => 'Add or remove Logo Item Labels',
			'choose_from_most_used'      => 'Choose from the most used',
			'popular_items'              => 'Popular Logo Item Labels',
			'search_items'               => 'Search Logo Item Labels',
			'not_found'                  => 'Not Found',
			'no_terms'                   => 'No Logo Item Labels',
			'items_list'                 => 'Logo Item Labels list',
			'items_list_navigation'      => 'Logo Item Labels list navigation',
		);
		$rewrite = array(
			'slug'                       => 'logo-list-category',
			'with_front'                 => false,
			'hierarchical'               => false,
		);
		$capabilities = array(
			'manage_terms'               => 'manage_options',
			'edit_terms'                 => 'manage_options',
			'delete_terms'               => 'manage_options',
			'assign_terms'               => 'manage_options',
		);
		$args = array(
			'labels'                     => $labels,
			'description'                => 'Testing the description',
			'hierarchical'               => false,
			'public'                     => false,
			'show_ui'                    => true,
			'show_admin_column'          => true,
			'show_in_nav_menus'          => false,
			'show_tagcloud'              => false,
			'rewrite'                    => $rewrite,
			'capabilities'               => $capabilities,
			'show_in_quick_edit'         => true,
		);
		register_taxonomy( 'logo-list-label', array( 'logo-list-item' ), $args );
	
	}
	add_action( 'init', 'create_logo_list_label_tag', 0 );
}

// Remove Taxonomies from Post Editor
function remove_logo_taxonomy_meta() {
    remove_meta_box( 'logo-list-category'.'div', 'logo-list-item', 'side' );
    remove_meta_box( 'tagsdiv-'.'logo-list-label', 'logo-list-item', 'side' );
}
add_action( 'admin_menu' , 'remove_logo_taxonomy_meta' );