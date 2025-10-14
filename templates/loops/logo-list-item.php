<?php 
    /**
     *  Template Name: Location Loop - Card layout
     *  Designed for UAMS Find-a-Doc
     * 
     *  Must be used inside a loop
     *  Required var:
     *      $id
     *      $item_id
     *      $logo_item_count
     *      $image_height
     *      $image_height_xxs
     *      $image_width
     *      $image_width_xxs
     * 
     */

    $item_element_id = $id . '_' . $logo_item_count;
    $item_title_id = $item_element_id . '_title';
    $item_button_id = $item_element_id . '_button';

    $item_title_val = get_field('logo_title', $item_id);
    $item_image = get_field('logo_image', $item_id);
    // Check if image ID exists in the media library
    // If the image was assigned to the item but later deleted from the library, $item_image would still return an ID.
    // If that's the case, remove the value from $item_image on that item.
    if ( isset($item_image) && !empty($item_image) && !wp_get_attachment_image($item_image) ) {
        $item_image = '';
    }

    $item_title_query = '';
    if ( empty($item_image) ) {
        $item_title_query = true;
    }
    $item_title = 
        (( $titles_query || $item_title_query ) ? '' : '<span class="sr-only">') .
        $item_title_val .
        (( $titles_query || $item_title_query ) ? '' : '</span>');
    if ( function_exists('bis_get_attachment_image') ) {
        $item_image_fly_xxs_1x = bis_get_attachment_image_src( $item_image, array( $image_width_xxs, $image_height_xxs ) );
        // $item_image_fly_xxs_2x = bis_get_attachment_image_src( $item_image, array( $image_width_xxs*2, $image_height_xxs*2 ) );
        $item_image_fly_xs_1x = bis_get_attachment_image_src( $item_image, array( $image_width_xs, $image_height_xs ) );
        // $item_image_fly_xs_2x = bis_get_attachment_image_src( $item_image, array( $image_width_xs*2, $image_height_xs*2 ) );
        $item_image_fly_sm_1x = bis_get_attachment_image_src( $item_image, array( $image_width_sm, $image_height_sm ) );
        // $item_image_fly_sm_2x = bis_get_attachment_image_src( $item_image, array( $image_width_sm*2, $image_height_sm*2 ) );
        $item_image_fly_md_1x = bis_get_attachment_image_src( $item_image, array( $image_width_md, $image_height_md ) );
        // $item_image_fly_md_2x = bis_get_attachment_image_src( $item_image, array( $image_width_md*2, $image_height_md*2 ) );
        $item_image_fly_lg_1x = bis_get_attachment_image_src( $item_image, array( $image_width_lg, $image_height_lg ) );
        // $item_image_fly_lg_2x = bis_get_attachment_image_src( $item_image, array( $image_width_lg*2, $image_height_lg*2 ) );
        $item_image_fly_xl_1x = bis_get_attachment_image_src( $item_image, array( $image_width_xl, $image_height_xl ) );
        // $item_image_fly_xl_2x = bis_get_attachment_image_src( $item_image, array( $image_width_xl*2, $image_height_xl*2 ) );
        $item_image_fly_xxl_1x = bis_get_attachment_image_src( $item_image, array( $image_width_xxl, $image_height_xxl ) );
        // $item_image_fly_xxl_2x = bis_get_attachment_image_src( $item_image, array( $image_width_xxl*2, $image_height_xxl*2 ) );
        $item_image_fly_fallback = $item_image_fly_lg_1x;
    }
    $item_image_alt_native = get_post_meta($item_image, '_wp_attachment_image_alt', TRUE);
    $item_image_alt_override = $item_title_val . ' logo'; 
    // $item_image_alt = $item_image_alt_override ? $item_image_alt_override : $item_image_alt_native; 
    $item_image_alt = ''; // Setting alt text value as empty, as the logo is decorative in this context, only supporting the heading
    $item_label = get_field('logo_label', $item_id);
    $item_label_name = '';
    if ($item_label) {
        $item_label_name = get_term( $item_label )->name;
    }
    $item_shortdescr = get_field('logo_shortdescr', $item_id);
    $item_longdescr = get_field('logo_longdescr', $item_id);
    $item_link = get_field('logo_link', $item_id);
    $item_link_text = get_field('logo_link_text', $item_id) ?: 'Visit Their Website';
    $item_link_target = get_field('logo_link_target', $item_id);
    $item_link_target = ( $item_link_target != 'inherit' ) ? $item_link_target : $links_target_value;
    $item_link_target_attr = ( $item_link_target != '_self' ) ? ' target=' . $item_link_target : '';
    $item_heading_link_query = ($layout == 'grid') ? true : false; // Determine when to add link to heading
    $item_button_link_query = ($layout != 'grid') ? true : false; // Determine when to add button link
    $item_general_link_element = 'a href="' . $item_link . '"' . $item_link_target_attr . ' data-moduletitle="' . $heading . '"' . ' data-itemtitle="' . $item_title_val . '"';
    $item_heading_link_element =
        (
            $item_heading_link_query ?
            '<' . $item_general_link_element . ' class="stretched-link" aria-describedby="' . $item_button_id . '">'
            :
            ''
        ) .
        $item_title . 
        (
            $item_heading_link_query ?
            '</a>'
            :
            ''
        );
    $item_button_link_element =
        '<' . 
        (
            $item_button_link_query ? 
            $item_general_link_element . 
            ' class="btn btn-' . $button_color . '"' . 
            ' aria-describedby = "' . $item_title_id . '"'
            :
            'span style="display: none;" aria-hidden="true"'
        ) . 
        ' id="' . $item_button_id . '">' . 
            $item_link_text . 
        '</' .
        ($item_button_link_query ? 'a' : 'span') . 
        '>';
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
    if( $links_query && $item_link ) {
        $item_class_name .= ' item-has-link';
    }
    if( !empty($item_image) ) {
        $item_class_name .= ' item-has-logo';
    }
?>
<div class="item<?php echo $item_class_name; ?>" id="<?php echo $item_element_id; ?>">
    <?php if ( !empty($item_image) ) { ?>
        <div class="item-img<?php echo $desaturate_query ? ' item-img-desaturate' : ''; ?>">
            <picture>
                <?php if ( function_exists( 'bis_get_attachment_image' ) ) { ?>
                    <source srcset="<?php echo $item_image_fly_xxl_1x['src']; ?>" 
                        media="(min-width: 1921px)">
                    <source srcset="<?php echo $item_image_fly_xl_1x['src']; ?>" 
                        media="(min-width: 1500px)">
                    <source srcset="<?php echo $item_image_fly_lg_1x['src']; ?>" 
                        media="(min-width: 1200px)">
                    <source srcset="<?php echo $item_image_fly_md_1x['src']; ?>" 
                        media="(min-width: 992px)">
                    <source srcset="<?php echo $item_image_fly_sm_1x['src']; ?>" 
                        media="(min-width: 768px)">
                    <source srcset="<?php echo $item_image_fly_xs_1x['src']; ?>" 
                        media="(min-width: 576px)">
                    <source srcset="<?php echo $item_image_fly_xxs_1x['src']; ?>" 
                        media="(min-width: 1px)">
                    <!-- Fallback -->
                    <img src="<?php echo $item_image_fly_fallback['src']; ?>" alt="<?php echo $item_image_alt; ?>" />
                <?php } else { ?>
                    <!-- Fallback -->
                    <img src="<?php echo wp_get_attachment_image_url( $item_image, 'aspect-16-9' ); ?>" alt="<?php echo $item_image_alt; ?>" />
                <?php } //endif ?>
            </picture>
        </div>
    <?php } //endif ?>
    <div class="item-body">
        <h3 class="<?php echo ( $titles_query || $item_title_query ) ? 'item-title' : 'item-title item-title-hidden'; ?>" id="<?php echo $item_title_id; ?>"><?php echo ($links_query && $item_link) ? $item_heading_link_element : $item_title; ?></h3>
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
        <?php if ( $links_query && $item_link ) { ?>
            <div class="link-container">
                <?php echo $item_button_link_element; ?>
            </div>
        <?php } //endif ?>
    </div>
</div>