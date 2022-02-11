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
        $heading = get_field('block_logo_list_heading');
    }
    if ( empty($hide_heading) ) {
        $hide_heading = get_field('block_logo_list_hide_heading');
    }
    if ( empty($description) ) {
        $description = get_field('block_logo_list_description');
    }
    if ( empty($background_color) ) {
        $background_color = get_field('block_logo_list_background_color');
    }
    if ( empty($button_color) && ( $background_color == 'bg-white' || $background_color == 'bg-gray' || $background_color == 'bg-auto' ) ) {
        $button_color = 'primary';
    } else {
        $button_color = 'white';
    }

    // List Configuration
    $filter_ids = get_field('block_logo_list_filter_ids') ?: array(); // Get list of categories
    if ( empty($filter_logic) ) {
        $filter_logic = get_field('block_logo_list_filter_logic'); // Define which logic to use: AND (match all categories), OR (match any categories)
    }
    if ( empty($featured) ) {
        $featured = get_field('block_logo_list_featured'); // Enable featured behavior
    }
    if ( empty($layout) ) {
        $layout = get_field('block_logo_list_layout'); // Define which layout to use
    }
    $width = 'full'; // Default list width
    $shortdescr = false; // Default display behavior for item short descriptions
    $titles_query = false; // Default display behavior for item titles. If true, always display item titles.
    $links_display = false; // Default display behavior for item links
    if ( $layout == 'grid' ) { // Configuration specific to the grid layout
        if ( empty($width) ) {
            $width = get_field('block_logo_list_width'); // Set width of item list within block
        }
        if ( empty($shortdescr) ) {
            $shortdescr = get_field('block_logo_list_shortdescr'); // If true, add short descriptions to items
        }
        $titles_query = get_field('block_logo_list_titles'); // If true, display all item titles
    } elseif ( $layout == 'vertical' ) {
        $titles_query = true; // Always display item titles in this layout
    }
    if ( empty($desaturate) ) {
        $desaturate = get_field('block_logo_list_desaturate'); // If true, desaturate logos
    }
    if ( empty($labels) ) {
        $labels = get_field('block_logo_list_labels'); // If true, add labels to items
    }
    if ( empty($links) ) {
        $links = get_field('block_logo_list_links'); // If true, add links to items
    }
    $links_class = '';
    if ($links) {
        // If links are enabled, load values
        if ( empty($links_display) ) {
            // Determine if the link element should be visible
            if ( $layout == 'grid' ) {
                $links_display = false;
            } elseif ( $layout == 'vertical' ) {
                $links_display = true;
            }
        }
        if ( $links_display ) {
            $links_class = 'btn btn-' . $button_color; // Format the link as a button
        } else {
            $links_class = 'sr-only stretched-link'; // Visually hide the link and make the containing block clickable
        }
        if ( empty($links_target) ) {
            $links_target = get_field('block_logo_list_links_target'); // If true, open links in new window
        }
    }
    if ( empty($order) ) {
        $order = get_field('block_logo_list_order');
    }

    // More Section Configuration
    if ( empty($more) ) {
        $more = get_field('block_logo_list_more');
    }
    if ( $more ) {
        // If 'more' section is enabled, load values
        if ( empty($more_text) ) {
            $more_text = get_field('block_logo_list_more_text');
        }
        if ( empty($more_button_text) ) {
            $more_button_text = get_field('block_logo_list_more_button_text');
        }
        if ( empty($more_button_url) ) {
            $more_button_url = get_field('block_logo_list_more_button_url');
        }
        if ( empty($more_button_target) )  {
            $more_button_target = $more_button_url['target'];
        }
        if ( empty($more_button_description) ) {
            $more_button_description = get_field('block_logo_list_more_button_description');
            $more_button_description_attr = str_replace('"', '\'', $more_button_description);
            $more_button_description_attr = html_entity_decode(str_replace('&nbsp;', ' ', htmlentities($more_button_description_attr, null, 'utf-8')));
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
                    $item_label = 'Test Organization Title';
                    $item_shortdesc = 'Test Organization Title';
                    $item_longdescr = 'Test Organization Title';
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
                            <p class="item-text"><?php echo $item_shortdesc; ?></p>
                        <?php } elseif ( $layout == 'vertical' ) { ?>
                            <?php echo $item_longdesc; ?>
                        <?php } //endif ?>
                        <?php if ( $links ) { ?>
                            <a href="#"<?php echo $links_class ? ' class="' . $links_class . '"' : ''; ?><?php echo $links_target ? ' target="_blank"' : ''; ?> data-moduletitle="<?php echo $heading; ?>" data-itemtitle="Organization Title">Visit the website for Organization Title</a>
                        <?php } //endif ?>
                    </div>
                </div>
            </div>
            <?php if ( $more ) { ?>
                <div class="more">
                    <p class="lead"><?php echo $more_text; ?></p>
                    <div class="cta-container">
                        <a href="<?php echo $more_button_url['url']; ?>" class="btn btn-outline-<?php echo $button_color; ?>" aria-label="<?php echo $more_button_description_attr; ?>"<?php $more_button_target ? ' target="'. $more_button_target . '"' : '' ?>><?php echo $more_button_text; ?></a>
                    </div>
                </div>
            <?php } // endif ?>
        </div>
    </div>
</section>