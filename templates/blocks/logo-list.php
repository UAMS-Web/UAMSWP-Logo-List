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
    $desaturate_query = get_field('block_logo_list_desaturate'); // Check if if item logos should be programmatically desaturated
    $labels_query = false; // Fallback value for if label elements should be added to items
    $labels_query = get_field('block_logo_list_labels'); // Check if if label elements should be added to items
    $links_query = false; // Fallback value for if link elements should be added to items
    $links_query = get_field('block_logo_list_links'); // Check if link elements should be added to items
    $links_class = '';
    $links_target_query = true; // Fallback value for if item links should open in a new window
    if ($links_query) {
        // If links are enabled, load values
        if ( $links_display_query ) {
            $links_class = 'btn btn-' . $button_color; // Format the item link as a button
        } else {
            $links_class = 'sr-only stretched-link'; // Visually hide the item link and make the containing block clickable
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
            <div class="col-12 item-list">
                <?php
                    // Load values.
                    $item_heading = 'Test Organization Title';
                    $item_image = '';
                    $item_image_alt_native = get_post_meta($item_image, '_wp_attachment_image_alt', TRUE);
                    $item_image_alt_override = $item_heading . ' logo'; 
                    $item_label = 'Test Organization Label';
                    $item_shortdescr = 'Test organization short description';
                    $item_longdescr = '<p>Test organization long description</p><p>Second paragraph of organization long description</p>';
                    $item_button_text = 'Visit the website for ' . $item_heading;
                    $item_button_link = '';
                    if ( $item_button_link ) {
                        $item_button_url = '';
                        $item_button_target = '';
                    }
                ?>
                <div class="item">
                    <?php if ( !empty($item_image) ) { ?>
                        <div class="item-img">
                            <picture>
                                <?php if ( function_exists( 'fly_add_image_size' ) ) { ?>  
                                    <source srcset="<?php echo image_sizer($item_image, 455, 256, 'center', 'center'); ?> 1x, <?php echo image_sizer($item_image, 910, 512, 'center', 'center'); ?> 2x" 
                                        media="(min-width: 1921px)">
                                    <source srcset="<?php echo image_sizer($item_image, 433, 244, 'center', 'center'); ?> 1x, <?php echo image_sizer($item_image, 866, 487, 'center', 'center'); ?> 2x" 
                                        media="(min-width: 1500px)">
                                    <source srcset="<?php echo image_sizer($item_image, 455, 256, 'center', 'center'); ?> 1x, <?php echo image_sizer($item_image, 910, 512, 'center', 'center'); ?> 2x" 
                                        media="(min-width: 992px)">
                                    <source srcset="<?php echo image_sizer($item_image, 433, 244, 'center', 'center'); ?> 1x, <?php echo image_sizer($item_image, 866, 487, 'center', 'center'); ?> 2x" 
                                        media="(min-width: 768px)">
                                    <source srcset="<?php echo image_sizer($item_image, 455, 256, 'center', 'center'); ?> 1x, <?php echo image_sizer($item_image, 910, 512, 'center', 'center'); ?> 2x" 
                                        media="(min-width: 1px)">
                                    <!-- Fallback -->
                                    <img src="<?php echo image_sizer($item_image, 455, 256, 'center', 'center'); ?>" alt="<?php echo $image_alt_override ? $image_alt_override : $image_alt_native; ?>" />
                                <?php } else { ?>
                                    <!-- Fallback -->
                                    <img src="<?php echo wp_get_attachment_image_url( $item_image, 'aspect-16-9' ); ?>" alt="<?php echo $image_alt_override ? $image_alt_override : $image_alt_native; ?>" />
                                <?php } //endif ?>
                            </picture>
                        </div>
                    <?php } //endif ?>
                    <div class="item-body">
                        <h3 class="<?php echo $titles_query ? 'item-title' : 'sr-only'; ?>"><?php echo $item_heading; ?></h3>
                        <p class="item-label"><?php echo $item_label; ?></p>
                        <?php if ( $layout == 'grid' ) { ?>
                            <p class="item-text"><?php echo $item_shortdescr; ?></p>
                        <?php } elseif ( $layout == 'vertical' ) { ?>
                            <?php echo $item_longdesc; ?>
                        <?php } //endif ?>
                        <?php if ( $links_query ) { ?>
                            <a href="#"<?php echo $links_class ? ' class="' . $links_class . '"' : ''; ?><?php echo $links_target_query ? ' target="_blank"' : ''; ?> data-moduletitle="<?php echo $heading; ?>" data-itemtitle="Organization Title">Visit the website for Organization Title</a>
                        <?php } //endif ?>
                    </div>
                </div>
            </div>
            <?php if ( $more_query && !empty($more_text) && !empty($more_button_link) ) { ?>
                <div class="more">
                    <p class="lead"><?php echo $more_text; ?></p>
                    <div class="cta-container">
                        <a href="<?php echo $more_button_link_url; ?>" class="btn btn-outline-<?php echo $button_color; ?>" aria-label="<?php echo $more_button_description_attr; ?>"<?php $more_button_target ? ' target="'. $more_button_target . '"' : '' ?>><?php echo $more_button_text; ?></a>
                    </div>
                </div>
            <?php } // endif ?>
        </div>
    </div>
</section>