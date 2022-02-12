<?php
/*
 *
 * UAMS Logo List Block
 * 
 */

// Create id attribute allowing for custom "anchor" value.
$id = '';
if ( empty( $id ) && isset($block) ) {
    if ( !empty( $block['anchor'] ) ) {
        $id = sanitize_title_with_dashes( $block['anchor'] ); // Define ID attribute value using HTML anchor from block
    } else {
        $id = 'logo-list-' . $block['id']; // Generate a unique ID attribute value by using the block ID
    }
}

// Load values.

    // Block Configuration
    if ( empty($heading) ) {
        $heading = get_field('block_logo_list_heading'); // Get the block heading
    }
    $hide_heading = false; // Fallback value for if the block heading should be hidden
    $hide_heading = get_field('block_logo_list_hide_heading'); // Check if the block heading should be hidden
    if ( empty($description) ) {
        $description = get_field('block_logo_list_description'); // Get the block description
    }
    $background_color = 'bg-auto'; // Fallback value for the block background color
    $background_color = get_field('block_logo_list_background_color'); // Get the block background color
    // Set button color based on block background color
    if ( in_array($background_color, array('bg-white','bg-gray','bg-auto'), true ) ) {
        $button_color = 'primary';
    } else {
        $button_color = 'white';
    }

    // List Configuration
    $filter_ids = get_field('block_logo_list_filter_ids') ?: array(); // Get the list of categories
    $filter_logic = 'and'; // Fallback value for filtering logic for multiple categories: AND (match all categories), OR (match any categories)
    $filter_logic = get_field('block_logo_list_filter_logic'); // Get the filtering logic for multiple categories
    $featured_query = false; // Fallback value for if featured display behavior should be used
    $featured_query = get_field('block_logo_list_featured'); // Get which featured display behavior should be used
    $layout = 'grid'; // Fallback value for list layout
    $layout = get_field('block_logo_list_layout'); // Get the list layout
    $width = 'full'; // Fallback value for the list width
    $shortdescr_query = false; // Fallback value for if short description elements should be added to items
    $titles_query = false; // Fallback value for if titles should always be visually displayed in items
    $links_display_query = false; // Default value for if links should be visually displayed in items
    if ( $layout == 'grid' ) { // Configuration specific to the grid layout
        $links_display_query = false; // Don't visually display item links in this layout
        $width = get_field('block_logo_list_width'); // Get the width of the item list within block
        $shortdescr_query = get_field('block_logo_list_shortdescr'); // Check if short description elements should be added to items
        $titles_query = get_field('block_logo_list_titles'); // Check if titles should always be displayed in items
    } elseif ( $layout == 'vertical' ) { // Configuration specific to the vertical list layout
        $links_display_query = true; // Visually display item links in this layout
        $titles_query = true; // Visually display item titles in this layout
    }
    $desaturate_query = false; // Fallback value for if item logos should be programmatically desaturated
    // $desaturate_query = get_field('block_logo_list_desaturate'); // Check if if item logos should be programmatically desaturated
    $labels_query = false; // Fallback value for if label elements should be added to items
    $labels_query = get_field('block_logo_list_labels'); // Check if if label elements should be added to items
    $links_query = false; // Fallback value for if link elements should be added to items
    $links_query = get_field('block_logo_list_links'); // Check if link elements should be added to items
    $links_class = '';
    $links_class_inner = '';
    $links_target_query = true; // Fallback value for if item links should open in a new window
    if ($links_query) {
        // If links are enabled, load values
        if ( $links_display_query ) {
            $links_class = 'btn btn-' . $button_color; // Format the item link as a button
        } else {
            $links_class = 'stretched-link'; // Make the containing block clickable
            $links_class_inner = 'sr-only'; // Visually hide the item link text
        }
        if ( empty($links_target_query) ) {
            $links_target_query = get_field('block_logo_list_links_target'); // Check if link elements should be added to items
        }
    }
    $order = 'org_title'; // Fallback sort order of items within the list
    $order = get_field('block_logo_list_order'); // Sort order of items within the list

    // More Section Configuration
    $more_query = false; // Fallback value for if the 'more' section should be added
    $more_query = get_field('block_logo_list_more'); // Check for if the 'more' section should be added
    if ( $more_query ) {
        // If 'more' section is enabled, load values
        if ( empty($more_text) ) {
            $more_text = get_field('block_logo_list_more_text'); // Get message about availability of more information
        }
        if ( empty($more_button_text) ) {
            $more_button_text = get_field('block_logo_list_more_button_text'); // Get button text in 'more' section
        }
        if ( empty($more_button_link) ) {
            $more_button_link = get_field('block_logo_list_more_button_url'); // Get button link in 'more' section
        }
        if ( $more_button_link ) {
            if ( empty($more_button_link_url) )  {
                $more_button_link_url = $more_button_link['url']; // Get button URL value in 'more' section
            }
            if ( empty($more_button_target) )  {
                $more_button_target = $more_button_link['target']; // Get button target value in 'more' section
            }
        }
        if ( empty($more_button_description) ) {
            $more_button_description = get_field('block_logo_list_more_button_description'); // Button link description in 'more' section
            $more_button_description_attr = str_replace('"', '\'', $more_button_description); // Convert double quotes to single quotes in button link description
            $more_button_description_attr = html_entity_decode(str_replace('&nbsp;', ' ', htmlentities($more_button_description_attr, null, 'utf-8'))); // Sanitize button link description value
        }
    }

    // Image Calculations
    $grid_image_ratio = 16/9;
    $grid_image_width = 200;
    $grid_image_height = $grid_image_width / $grid_image_ratio;
    $grid_image_width_featured = 400;
    $grid_image_height_featured = $grid_image_width_featured / $grid_image_ratio;
    $vertical_image_ratio = 4/3;
    $vertical_image_width = 500;
    $vertical_image_height = $vertical_image_width / $vertical_image_ratio;
    $vertical_image_width = 1000;
    $vertical_image_height = $vertical_image_width / $vertical_image_ratio;
    if ( $layout == 'grid' ) {
        if ( $featured_query ) {
            $image_width = $grid_image_width_featured;
            $image_height = $grid_image_height_featured;
        } else {
            $image_width = $grid_image_width;
            $image_height = $grid_image_height;
        }
    } elseif ( $layout == 'vertical' ) {
        if ( $featured_query ) {
            $image_width = $vertical_image_width_featured;
            $image_height = $vertical_image_height_featured;
        } else {
            $image_width = $vertical_image_width;
            $image_height = $vertical_image_height;
        }
    }

$list_items = array (
    array (
        'heading' => 'Apple',
        'image' => '',
        'label' => 'Fruit',
        'shortdescr' => 'Test organization short description',
        'longdescr' => '<p>Test organization long description</p><p>Second paragraph of organization long description</p>',
        'button_text' => 'Test organization button text',
        'button_link' => array (
            'url' => 'https://www.google.com/',
        )
    ),
    array (
        'heading' => 'Carrot',
        'image' => '',
        'label' => 'Vegetable',
        'shortdescr' => 'Test organization short description',
        'longdescr' => '<p>Test organization long description</p><p>Second paragraph of organization long description</p>',
        'button_text' => 'Test organization button text',
        'button_link' => array (
            'url' => 'https://www.cnn.com/',
        )
    ),
    array (
        'heading' => 'Banana',
        'image' => '',
        'label' => 'Fruit',
        'shortdescr' => 'Test organization short description',
        'longdescr' => '<p>Test organization long description</p><p>Second paragraph of organization long description</p>',
        'button_text' => 'Test organization button text',
        'button_link' => array (
            'url' => 'https://www.apple.com/',
        )
    ),
    array (
        'heading' => 'Strawberry',
        'image' => '',
        'label' => 'Fruit',
        'shortdescr' => 'Test organization short description',
        'longdescr' => '<p>Test organization long description</p><p>Second paragraph of organization long description</p>',
        'button_text' => 'Test organization button text',
        'button_link' => array (
            'url' => 'https://www.bestbuy.com/',
        )
    ),
    array (
        'heading' => 'Celery',
        'image' => '',
        'label' => 'Vegetable',
        'shortdescr' => 'Test organization short description',
        'longdescr' => '<p>Test organization long description</p><p>Second paragraph of organization long description</p>',
        'button_text' => 'Test organization button text',
        'button_link' => array (
            'url' => 'https://www.microsoft.com/',
        )
    )
);

// Create class attribute
$className = '';
if( !empty($block['className']) ) {
    $className .= ' ' . $block['className']; // Add additional CSS class(es) from block
}
if( !empty($background_color) ) {
    $className .= ' ' . $background_color; // Add background color value from block input
} else {
    $className .= ' bg-auto'; // Add fallback background color value
}
if( !empty($layout) ) {
    $className .= ' logo-list-layout-' . $layout; // Add background color value from block input
} else {
    $className .= ' logo-list-layout-grid'; // Add fallback background color value
}

// Display the Block
?>
<section class="uams-module logo-list alignfull<?php echo $className; ?>" id="<?php echo $id; ?>">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12<?php echo ($hide_heading && empty($description)) ? " sr-only" : ""; ?>">
                <h2 class="module-title<?php echo ($hide_heading && $description) ? " sr-only" : ""; ?>">
                    <span class="title"><?php echo $heading; ?></span>
                </h2>
                <?php echo $description ? '<div class="module-description"><p>'. $description .'</p></div>' : ''; ?>
            </div>
            <div class="col-12">
            <div class="item-list">
                <?php
                foreach($list_items as $list_item) {
                    // Load values.
                    $item_title = $list_item['heading'];
                    $item_image = $list_item['image'];
                    $item_title_query = '';
                    if ( empty($item_image) ) {
                        $item_title_query = true;
                    }
                    $image_fly_1_1x = fly_get_attachment_image_src( $item_image, array( $image_width, $image_height ) );
                    $image_fly_1_2x = fly_get_attachment_image_src( $item_image, array( $image_width*2, $image_height*2 ) );
                    $image_fly_fallback = $image_fly_1_1x;
                    $item_image_alt_native = get_post_meta($item_image, '_wp_attachment_image_alt', TRUE);
                    $item_image_alt_override = $item_title . ' logo'; 
                    $item_label = $list_item['label'];
                    $item_shortdescr = $list_item['shortdescr'];
                    $item_longdescr = $list_item['longdescr'];
                    $item_button_text = $list_item['button_text'];
                    $item_button_link = $list_item['button_link'];
                    if ( $item_button_link ) {
                        $item_button_url = $item_button_link['url'];
                    }
                ?>
                <div class="item<?php echo $links_query ? ' item-has-link' : ''; ?>">
                    <?php if ( !empty($item_image) ) { ?>
                        <div class="item-img<?php echo $desaturate_query ? ' item-img-desaturate' : ''; ?>">
                            <picture>
                                <?php if ( function_exists( 'fly_add_image_size' ) ) { ?>
                                    <source srcset="<?php echo $image_fly_1_1x['src']; ?> 1x, <?php echo $image_fly_1_2x['src']; ?> 2x" 
                                        media="(min-width: 1px)">
                                    <!-- Fallback -->
                                    <img src="<?php echo $image_fly_fallback['src']; ?>" alt="<?php echo $image_alt_override ? $image_alt_override : $image_alt_native; ?>" />
                                <?php } else { ?>
                                    <!-- Fallback -->
                                    <img src="<?php echo wp_get_attachment_image_url( $item_image, 'aspect-16-9' ); ?>" alt="<?php echo $image_alt_override ? $image_alt_override : $image_alt_native; ?>" />
                                <?php } //endif ?>
                            </picture>
                        </div>
                    <?php } //endif ?>
                    <div class="item-body">
                        <h3 class="<?php echo ( $titles_query || $item_title_query ) ? 'item-title' : 'item-title sr-only'; ?>"><?php echo $item_title; ?></h3>
                        <?php if ( $labels_query ) { ?>
                            <p class="item-label"><?php echo $item_label; ?></p>
                        <?php } //endif ?>
                        <?php if ( $layout == 'grid' ) { ?>
                            <?php if ( $shortdescr_query ) { ?>
                                <p class="item-text"><?php echo $item_shortdescr; ?></p>
                            <?php } //endif ?>
                        <?php } elseif ( $layout == 'vertical' ) { ?>
                            <?php echo $item_longdesc; ?>
                        <?php } //endif ?>
                        <?php if ( $links_query ) { ?>
                            <a href="<?php echo $item_button_url; ?>"<?php echo $links_class ? ' class="' . $links_class . '"' : ''; ?><?php echo $links_target_query ? ' target="_blank"' : ''; ?> data-moduletitle="<?php echo $heading; ?>" data-itemtitle="<?php echo $item_title; ?>"><span <?php echo $links_class_inner ? ' class="' . $links_class_inner . '"' : ''; ?>>Visit the website for <?php echo $item_title; ?></span></a>
                        <?php } //endif ?>
                    </div>
                </div>
                <?php } // end foreach ?>
            </div>
            </div>
            <?php if ( $more_query && !empty($more_text) && !empty($more_button_link) ) { 
                // If all values aren't present, don't display the section
                ?>
                <div class="col-12 more">
                    <p class="lead"><?php echo $more_text; ?></p>
                    <div class="cta-container">
                        <a href="<?php echo $more_button_link_url; ?>" class="btn btn-outline-<?php echo $button_color; ?>" aria-label="<?php echo $more_button_description_attr; ?>"<?php $more_button_target ? ' target="'. $more_button_target . '"' : '' ?>><?php echo $more_button_text; ?></a>
                    </div>
                </div>
            <?php } // endif ?>
        </div>
    </div>
</section>