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
    $hide_heading = get_field('block_logo_list_hide_heading') ?: false; // Check if the block heading should be hidden
    if ( empty($description) ) {
        $description = get_field('block_logo_list_description'); // Get the block description
    }
    $background_color = get_field('block_logo_list_background_color') ?: 'bg-auto'; // Get the block background color
    // Set button color based on block background color
    if ( in_array($background_color, array('bg-white','bg-gray','bg-auto'), true ) ) {
        $button_color = 'primary';
    } else {
        $button_color = 'white';
    }

    // List Configuration
    $category_ids = get_field('block_logo_list_filter_ids') ?: array(); // Get the list of categories
    $category_logic = get_field('block_logo_list_filter_logic') ?: 'and'; // Get the filtering logic for multiple categories: AND (match all categories), OR (match any categories)
    $label_ids = get_field('block_logo_list_labels_ids') ?: array(); // Get the list of categories
    $label_logic = get_field('block_logo_list_labels_logic') ?: 'and'; // Get the filtering logic for multiple categories: AND (match all categories), OR (match any categories)
    $featured_query = get_field('block_logo_list_featured') ?: false; // Get which featured display behavior should be used
    $layout = get_field('block_logo_list_layout') ?: 'grid'; // Get the list layout
    if ( $layout == 'grid' ) { // Configuration specific to the grid layout
        $links_display_query = false; // Don't visually display item links in this layout
        $width = get_field('block_logo_list_width'); // Get the width of the item list within block
        $shortdescr_query = get_field('block_logo_list_shortdescr'); // Check if short description elements should be added to items
        $titles_query = get_field('block_logo_list_titles') ?: false; // Check if titles should always be displayed in items
    } elseif ( $layout == 'vertical' ) { // Configuration specific to the vertical list layout
        $links_display_query = true; // Visually display item links in this layout
        $width = '';
        $shortdescr_query = false;
        $titles_query = true; // Visually display item titles in this layout
    } else {
        $links_display_query = false;
        $width = '';
        $shortdescr_query = false;
        $titles_query = false;
    }
    $width_class = !empty($width) ? ' ' . $width : ''; // Set class name for list width
    $desaturate_query = get_field('block_logo_list_desaturate') ?: false; // Check if if item logos should be programmatically desaturated
    $labels_query = get_field('block_logo_list_labels') ?: false; // Check if if label elements should be added to items
    $links_query = get_field('block_logo_list_links') ?: false; // Check if link elements should be added to items
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
    $orderby_array = get_field('block_logo_list_order') ?: array(); // Get sort order of items within the list
    if ( count($orderby_array) === 1 && $orderby_array[0] != 'rand' ) { // If there is only one sort order option, and it's not Random Order...
        if ( $orderby_array[0] != 'meta_value' ) {
            $orderby_array[] = 'meta_value'; // If the one sort order option isn't Alphabetical by Organization Title, set that to the fallback
        } elseif ( $orderby_array[0] != 'menu_order' ) {
            $orderby_array[] = 'menu_order'; // If the one sort order option isn't Menu Order, set that to the fallback
        }
    }
    $orderby = implode(' ', $orderby_array) ?: 'title menu_order'; // Sort order of items within the list. Join the array elements, separated by a space

    // More Section Configuration
    $more_query = get_field('block_logo_list_more') ?: false; // Check for if the 'more' section should be added
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
    $grid_image_ratio = 2/1;
    $grid_image_width = 294;
    $grid_image_height = $grid_image_width / $grid_image_ratio;
    $grid_image_width_featured = 442;
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
if ( $featured_query ) {
    $className .= ' logo-list-featured'; // Add class to indicate featured
}

// The Query
$posts_per_page = $featured_query ? 3 : -1; // If featured, limit to three items. Otherwise, display all.
$args = array(
    'post_type' => 'logo-list-item',
    'post_status' => 'publish',
    'posts_per_page' => $posts_per_page,
    'orderby' => $orderby,
    'order' => 'ASC',
    'tax_query' => array(
        'relation' => 'AND',
        array(
            'taxonomy' => 'logo-list-category',
            'field'    => 'term_id',
            'terms'    => $category_ids,
            'operator'  => $category_logic
        ),
        array(
            'taxonomy' => 'logo-list-label',
            'field'    => 'term_id',
            'terms'    => $label_ids,
            'operator'  => $label_logic
        ),
    ),
);
if( in_array('meta_value', $orderby_array) ) {
    $args['meta_key'] = 'logo_title';
}
// print("<pre>".print_r($args,true)."</pre>");
$logo_list_query = new WP_Query( $args );

// Display the Block
if ( $logo_list_query->have_posts() ) { ?>
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
            <div class="item-list-container<?php echo $width_class; ?>">
            <div class="item-list">
                <?php
                while ( $logo_list_query->have_posts() ) : $logo_list_query->the_post();
                    $id = get_the_ID(); 
                    include( UAMS_LOGO_PATH . '/templates/loops/logo-list-item.php' ); 
                endwhile; 
                wp_reset_postdata(); ?>
            </div>
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
<?php } // endif ?>