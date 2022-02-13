<?php 
    /**
     *  Template Name: Location Loop - Card layout
     *  Designed for UAMS Find-a-Doc
     * 
     *  Must be used inside a loop
     *  Required var: $id
     */

    $item_title = get_field('logo_title', $id);
    $item_image = get_field('logo_image', $id);
    $item_title_query = '';
    if ( empty($item_image) ) {
        $item_title_query = true;
    }
    $image_fly_1_1x = fly_get_attachment_image_src( $item_image, array( $image_width, $image_height ) );
    $image_fly_1_2x = fly_get_attachment_image_src( $item_image, array( $image_width*2, $image_height*2 ) );
    $image_fly_fallback = $image_fly_1_1x;
    $item_image_alt_native = get_post_meta($item_image, '_wp_attachment_image_alt', TRUE);
    $item_image_alt_override = $item_title . ' logo'; 
    $item_label = get_field('logo_label', $id);
    $item_shortdescr = get_field('logo_shortdescr', $id);
    $item_longdescr = get_field('logo_longdescr', $id);
    $item_button_link = get_field('logo_link', $id);
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
            <?php echo $item_longdescr; ?>
        <?php } //endif ?>
        <?php if ( $links_query && $item_button_link ) { ?>
            <div class="link-container">
                <a href="<?php echo $item_button_link; ?>"<?php echo $links_class ? ' class="' . $links_class . '"' : ''; ?><?php echo $links_target_query ? ' target="_blank"' : ''; ?> data-moduletitle="<?php echo $heading; ?>" data-itemtitle="<?php echo $item_title; ?>"><span <?php echo $links_class_inner ? ' class="' . $links_class_inner . '"' : ''; ?>>Visit the website for <?php echo $item_title; ?></span></a>
            </div>
        <?php } //endif ?>
    </div>
</div>