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
    $item_image_fly_1_1x = fly_get_attachment_image_src( $item_image, array( $image_width, $image_height ) );
    $item_image_fly_1_2x = fly_get_attachment_image_src( $item_image, array( $image_width*2, $image_height*2 ) );
    $item_image_fly_fallback = $item_image_fly_1_1x;
    $item_image_alt_native = get_post_meta($item_image, '_wp_attachment_image_alt', TRUE);
    $item_image_alt_override = $item_title . ' logo'; 
    $item_label = get_field('logo_label', $id);
    $item_label_name = get_term( $item_label )->name;
    $item_shortdescr = get_field('logo_shortdescr', $id);
    $item_longdescr = get_field('logo_longdescr', $id);
    $item_button_link = get_field('logo_link', $id);
    $item_button_text = get_field('logo_link_text', $id) ?: 'Visit the website for ' . $item_title;
    $item_button_target = get_field('logo_link_target', $id);
    $item_button_target = ( $item_button_target != 'inherit' ) ? $item_button_target : $links_target_value;
    $item_button_target_attr = ( $item_button_target != '_self' ) ? ' target=' . $item_button_target : '';

    $item_class_name = '';
    if( $titles_query || $item_title_query ) {
        $item_class_name .= ' item-has-title';
    }
    if( $labels_query ) {
        $item_class_name .= ' item-has-label';
    }
    if( $layout == 'grid' && $shortdescr_query ) {
        $item_class_name .= ' item-has-shortdescr';
    }
    if( $layout == 'vertical' && $item_longdescr ) {
        $item_class_name .= ' item-has-longdescr';
    }
    if( $links_query && $item_button_link ) {
        $item_class_name .= ' item-has-link';
    }
?>
<div class="item<?php echo $item_class_name; ?>">
    <?php if ( !empty($item_image) ) { ?>
        <div class="item-img<?php echo $desaturate_query ? ' item-img-desaturate' : ''; ?>">
            <picture>
                <?php if ( function_exists( 'fly_add_image_size' ) ) { ?>
                    <source srcset="<?php echo $item_image_fly_1_1x['src']; ?> 1x, <?php echo $item_image_fly_1_2x['src']; ?> 2x" 
                        media="(min-width: 1px)">
                    <!-- Fallback -->
                    <img src="<?php echo $item_image_fly_fallback['src']; ?>" alt="<?php echo $item_image_alt_override ? $item_image_alt_override : $item_image_alt_native; ?>" />
                <?php } else { ?>
                    <!-- Fallback -->
                    <img src="<?php echo wp_get_attachment_image_url( $item_image, 'aspect-16-9' ); ?>" alt="<?php echo $item_image_alt_override ? $item_image_alt_override : $item_image_alt_native; ?>" />
                <?php } //endif ?>
            </picture>
        </div>
    <?php } //endif ?>
    <div class="item-body">
        <h3 class="<?php echo ( $titles_query || $item_title_query ) ? 'item-title' : 'item-title sr-only'; ?>"><?php echo $item_title; ?></h3>
        <?php if ( $labels_query ) { ?>
            <p class="item-label"><?php echo $item_label_name; ?></p>
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
                <a href="<?php echo $item_button_link; ?>"<?php echo $links_class ? ' class="' . $links_class . '"' : ''; ?><?php echo $item_button_target_attr; ?> data-moduletitle="<?php echo $heading; ?>" data-itemtitle="<?php echo $item_title; ?>"><span <?php echo $links_class_inner ? ' class="' . $links_class_inner . '"' : ''; ?>><?php echo $item_button_text; ?></span></a>
            </div>
        <?php } //endif ?>
    </div>
</div>