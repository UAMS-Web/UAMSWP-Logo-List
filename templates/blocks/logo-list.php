<?php
/*
 * Scratch touch.
 * UAMS Logo List Block
 *
 */

// Create id attribute allowing for custom "anchor" value.
/**
 * Values the caller may preset; the block fills in any it did not.
 *
 * @var string $heading
 * @var string $description
 * @var string $more_text
 * @var array{url: string, target: string}|false $more_button_link
 */
$id = '';
/** @var array{anchor?: string, id: string, className?: string}|null $block */
if (isset($block)) {
    if (! empty($block['anchor'])) {
        $id = sanitize_title_with_dashes($block['anchor']); // Define ID attribute value using HTML anchor from block
    } else {
        $id = 'logo-list-'.$block['id']; // Generate a unique ID attribute value by using the block ID
    }
}

// Load values.

// Block Configuration
if (empty($heading)) {
    /** @var string $heading */
    $heading = get_field('block_logo_list_heading'); // Get the block heading
}
$hide_heading = get_field('block_logo_list_hide_heading') ?: false; // Check if the block heading should be hidden
if (empty($description)) {
    /** @var string $description */
    $description = get_field('block_logo_list_description'); // Get the block description
}
/** @var string $background_color */
$background_color = get_field('block_logo_list_background_color') ?: 'bg-auto'; // Get the block background color
// Set button color based on block background color
if (in_array($background_color, ['bg-white', 'bg-gray', 'bg-auto'], true)) {
    $button_color = 'primary';
} else {
    $button_color = 'white';
}

// List Configuration
$category_ids = get_field('block_logo_list_filter_ids') ?: []; // Get the list of categories
$category_logic = get_field('block_logo_list_filter_logic') ?: 'IN'; // Get the filtering logic for multiple categories: AND (match all categories), OR (match any categories)
$label_ids = get_field('block_logo_list_labels_ids') ?: []; // Get the list of categories
$label_logic = get_field('block_logo_list_labels_logic') ?: 'IN'; // Get the filtering logic for multiple categories: AND (match all categories), OR (match any categories)
$featured_query = get_field('block_logo_list_featured') ?: false; // Get which featured display behavior should be used
/** @var string $layout */
$layout = get_field('block_logo_list_layout') ?: 'grid'; // Get the list layout
// $layout is always a string here ('grid' replaces any falsy value above), so == and === agree.
// The marker covers this if and its elseif, one mutant each.
// @pest-mutate-ignore: EqualToIdentical
if ($layout == 'grid') { // Configuration specific to the grid layout
    /** @var string $width */
    $width = get_field('block_logo_list_width'); // Get the width of the item list within block
    $shortdescr_query = get_field('block_logo_list_shortdescr'); // Check if short description elements should be added to items
    $titles_query = get_field('block_logo_list_titles') ?: false; // Check if titles should always be displayed in items
} elseif ($layout == 'vertical') { // Configuration specific to the vertical list layout
    $width = '';
    // $shortdescr_query is only read under the grid layout, so its value here is never observed.
    // @pest-mutate-ignore: FalseToTrue
    $shortdescr_query = false;
    $titles_query = true; // Visually display item titles in this layout
} else {
    $width = '';
    // $shortdescr_query is only read under the grid layout, so its value here is never observed.
    // @pest-mutate-ignore: FalseToTrue
    $shortdescr_query = false;
    $titles_query = false;
}
$width_class = ! empty($width) ? ' '.$width : ''; // Set class name for list width
$desaturate_query = get_field('block_logo_list_desaturate') ?: false; // Check if if item logos should be programmatically desaturated
$labels_query = get_field('block_logo_list_labels') ?: false; // Check if if label elements should be added to items
$links_query = get_field('block_logo_list_links') ?: false; // Check if link elements should be added to items
// The fallback is only read when links are enabled, and that branch always overwrites it.
// @pest-mutate-ignore: FalseToTrue
$links_target_query = false; // Fallback value for if item links should open in a new window
if ($links_query) {
    $links_target_query = get_field('block_logo_list_links_target'); // Check if link elements should be added to items
}
$links_target_value = $links_target_query ? '_blank' : '_self';
/** @var list<string> $orderby_array */
$orderby_array = get_field('block_logo_list_order') ?: []; // Get sort order of items within the list
if (count($orderby_array) === 1 && (! str_starts_with($orderby_array[0], 'rand'))) { // If there is only one sort order option, and it's not Random Order...
    if (! str_starts_with($orderby_array[0], 'meta_value')) {
        $orderby_array[] = 'meta_value|ASC'; // If the one sort order option isn't Alphabetical by Organization Title, set that to the fallback
    } else {
        $orderby_array[] = 'menu_order|ASC'; // The one sort order option is Alphabetical by Organization Title, so Menu Order is the fallback
    }
}
$orderby = [];
foreach ($orderby_array as $str) {
    $strArr = explode('|', $str);
    if (count($strArr) === 2) {
        $orderby[$strArr[0]] = $strArr[1];
    } else {
        $orderby[$strArr[0]] = 'ASC';
    }
}

$max_items = get_field('block_logo_list_max') ?: -1; // Get maximum number of items
if ($featured_query) {
    // $max_items is never 0 here ('?: -1' above turns 0 into -1), so < 1 and < 0 agree.
    // @pest-mutate-ignore: DecrementInteger
    $max_items_unlimited = $max_items < 1;
    $max_items = $max_items_unlimited ? 3 : min($max_items, 3); // If Featured mode is on, limit max to 3; treat -1 (no limit) as 3.
}

// More Section Configuration
/**
 * The caller may preset these; the block fills in any it did not.
 *
 * @var string $more_button_text
 * @var string $more_button_link_url
 * @var string $more_button_description_attr
 */
$more_query = get_field('block_logo_list_more') ?: false; // Check for if the 'more' section should be added
if ($more_query) {
    // If 'more' section is enabled, load values
    if (empty($more_text)) {
        /** @var string $more_text */
        $more_text = get_field('block_logo_list_more_text'); // Get message about availability of more information
    }
    if (empty($more_button_text)) {
        /** @var string $more_button_text */
        $more_button_text = get_field('block_logo_list_more_button_text'); // Get button text in 'more' section
    }
    if (empty($more_button_link)) {
        /** @var array{url: string, target: string}|false $more_button_link */
        $more_button_link = get_field('block_logo_list_more_button_url'); // Get button link in 'more' section
    }
    if ($more_button_link) {
        if (empty($more_button_link_url)) {
            $more_button_link_url = $more_button_link['url']; // Get button URL value in 'more' section
        }
    }
    if (empty($more_button_description)) {
        /** @var string $more_button_description */
        $more_button_description = get_field('block_logo_list_more_button_description'); // Button link description in 'more' section
        $more_button_description_attr = str_replace('"', "'", $more_button_description); // Convert double quotes to single quotes in button link description
        $more_button_description_attr = html_entity_decode(str_replace('&nbsp;', ' ', htmlentities($more_button_description_attr, ENT_NOQUOTES, 'utf-8'))); // Sanitize button link description value
    }
}

// Image Calculations
// Grid Layout
$grid_image_ratio = 16 / 9;
$grid_image_width_xxs = 177; // equal to half of $line-length-min + em at lead font size
$grid_image_width_xxs_featured = 221; // equal to half of $line-length-min + em at lead font size
$grid_image_width_xs = $grid_image_width_xxs;
$grid_image_width_xs_featured = $grid_image_width_xxs_featured;
$grid_image_width_sm = $grid_image_width_xxs;
$grid_image_width_sm_featured = $grid_image_width_xxs_featured;
$grid_image_width_md = $grid_image_width_xxs;
$grid_image_width_md_featured = $grid_image_width_xxs_featured;
$grid_image_width_lg = 354; // equal to $line-length-min + em at base font size
$grid_image_width_lg_featured = 442; // equal to $line-length-min + em at lead font size
$grid_image_width_xl = $grid_image_width_lg;
$grid_image_width_xl_featured = $grid_image_width_lg_featured;
$grid_image_width_xxl = $grid_image_width_lg;
$grid_image_width_xxl_featured = $grid_image_width_lg_featured;
// Vertical List Layout
$vertical_image_ratio = 4 / 3;
$vertical_image_width_xxs = 236;
$vertical_image_width_xxs_featured = $vertical_image_width_xxs;
$vertical_image_width_xs = 172;
$vertical_image_width_xs_featured = $vertical_image_width_xs;
$vertical_image_width_sm = $vertical_image_width_xs;
$vertical_image_width_sm_featured = 224;
$vertical_image_width_md = $vertical_image_width_xs;
$vertical_image_width_md_featured = 275;
$vertical_image_width_lg = $vertical_image_width_xs;
$vertical_image_width_lg_featured = 350; // max 359
$vertical_image_width_xl = $vertical_image_width_xs;
$vertical_image_width_xl_featured = 359;
$vertical_image_width_xxl = $vertical_image_width_xs;
$vertical_image_width_xxl_featured = $vertical_image_width_xl_featured;

$image_width_xxs = '';
$image_height_xxs = '';
$image_width_xs = '';
$image_height_xs = '';
$image_width_sm = '';
$image_height_sm = '';
$image_width_md = '';
$image_height_md = '';
$image_width_lg = '';
$image_height_lg = '';
$image_width_xl = '';
$image_height_xl = '';
$image_width_xxl = '';
$image_height_xxl = '';

// $layout is always a string here, so == and === agree.
// The marker covers this if and its elseif, one mutant each.
// @pest-mutate-ignore: EqualToIdentical
if ($layout == 'grid') {
    if ($featured_query) {
        $image_width_xxs = $grid_image_width_xxs_featured;
        $image_height_xxs = $image_width_xxs / $grid_image_ratio;
        $image_width_xs = $grid_image_width_xs_featured;
        $image_height_xs = $image_width_xs / $grid_image_ratio;
        $image_width_sm = $grid_image_width_sm_featured;
        $image_height_sm = $image_width_sm / $grid_image_ratio;
        $image_width_md = $grid_image_width_md_featured;
        $image_height_md = $image_width_md / $grid_image_ratio;
        $image_width_lg = $grid_image_width_lg_featured;
        $image_height_lg = $image_width_lg / $grid_image_ratio;
        $image_width_xl = $grid_image_width_xl_featured;
        $image_height_xl = $image_width_xl / $grid_image_ratio;
        $image_width_xxl = $grid_image_width_xxl_featured;
        $image_height_xxl = $image_width_xxl / $grid_image_ratio;
    } else {
        $image_width_xxs = $grid_image_width_xxs;
        $image_height_xxs = $image_width_xxs / $grid_image_ratio;
        $image_width_xs = $grid_image_width_xs;
        $image_height_xs = $image_width_xs / $grid_image_ratio;
        $image_width_sm = $grid_image_width_sm;
        $image_height_sm = $image_width_sm / $grid_image_ratio;
        $image_width_md = $grid_image_width_md;
        $image_height_md = $image_width_md / $grid_image_ratio;
        $image_width_lg = $grid_image_width_lg;
        $image_height_lg = $image_width_lg / $grid_image_ratio;
        $image_width_xl = $grid_image_width_xl;
        $image_height_xl = $image_width_xl / $grid_image_ratio;
        $image_width_xxl = $grid_image_width_xxl;
        $image_height_xxl = $image_width_xxl / $grid_image_ratio;
    }
} elseif ($layout == 'vertical') {
    if ($featured_query) {
        $image_width_xxs = $vertical_image_width_xxs_featured;
        $image_height_xxs = $image_width_xxs / $vertical_image_ratio;
        $image_width_xs = $vertical_image_width_xs_featured;
        $image_height_xs = $image_width_xs / $vertical_image_ratio;
        $image_width_sm = $vertical_image_width_sm_featured;
        $image_height_sm = $image_width_sm / $vertical_image_ratio;
        $image_width_md = $vertical_image_width_md_featured;
        $image_height_md = $image_width_md / $vertical_image_ratio;
        $image_width_lg = $vertical_image_width_lg_featured;
        $image_height_lg = $image_width_lg / $vertical_image_ratio;
        $image_width_xl = $vertical_image_width_xl_featured;
        $image_height_xl = $image_width_xl / $vertical_image_ratio;
        $image_width_xxl = $vertical_image_width_xxl_featured;
        $image_height_xxl = $image_width_xxl / $vertical_image_ratio;
    } else {
        $image_width_xxs = $vertical_image_width_xxs;
        $image_height_xxs = $image_width_xxs / $vertical_image_ratio;
        $image_width_xs = $vertical_image_width_xs;
        $image_height_xs = $image_width_xs / $vertical_image_ratio;
        $image_width_sm = $vertical_image_width_sm;
        $image_height_sm = $image_width_sm / $vertical_image_ratio;
        $image_width_md = $vertical_image_width_md;
        $image_height_md = $image_width_md / $vertical_image_ratio;
        $image_width_lg = $vertical_image_width_lg;
        $image_height_lg = $image_width_lg / $vertical_image_ratio;
        $image_width_xl = $vertical_image_width_xl;
        $image_height_xl = $image_width_xl / $vertical_image_ratio;
        $image_width_xxl = $vertical_image_width_xxl;
        $image_height_xxl = $image_width_xxl / $vertical_image_ratio;
    }
}

// Create class attribute
$className = '';
if (! empty($block['className'])) {
    $block_classes = array_filter(array_map(sanitize_html_class(...), preg_split('/\s+/', $block['className']) ?: [])); // Sanitize each additional CSS class from block, dropping any left empty
    if ($block_classes !== []) {
        // $className is still '' here, so .= and = agree.
        // @pest-mutate-ignore: ConcatEqualToEqual
        $className .= ' '.implode(' ', $block_classes); // Add additional CSS class(es) from block
    }
}
$className .= ' '.$background_color; // Add background color value from block input (falls back to bg-auto above)
$className .= ' logo-list-layout-'.$layout; // Add layout value from block input (falls back to grid above)
if ($featured_query) {
    $className .= ' logo-list-featured'; // Add class to indicate featured
}

// The Query
$args = [
    'post_type' => 'logo-list-item',
    'post_status' => 'publish',
    'posts_per_page' => $max_items,
    'orderby' => $orderby,
    'tax_query' => [
        'relation' => 'AND',
        [
            'taxonomy' => 'logo-list-category',
            'field' => 'term_id',
            'terms' => $category_ids,
            'operator' => $category_logic,
        ],
    ],
];
$label_ids_tax_query = [
    'taxonomy' => 'logo-list-label',
    'field' => 'term_id',
    'terms' => $label_ids,
    'operator' => $label_logic,
];
if (! empty($label_ids)) {
    $args['tax_query'][] = $label_ids_tax_query; // If the labels input has value(s), add its array to the tax_query array in $args
}
if (array_key_exists('meta_value', $orderby)) {
    $args['meta_key'] = 'logo_title'; // If the block should be sorted by meta value, add the appropriate meta key to $args
}
// print("<pre>".print_r($args,true)."</pre>");
$logo_list_query = new WP_Query($args);

// Editor placeholder (ACF Blocks V3 always renders a preview, so an unconfigured block would otherwise be blank).
if (! empty($is_preview) && (! $category_ids || ! $logo_list_query->have_posts())) {
    printf(
        '<div class="uams-block-placeholder" style="padding:1.5rem;border:1px dashed currentColor;text-align:center;"><strong>%s</strong><br>%s</div>',
        esc_html__('UAMS Logo List'),
        $category_ids
            ? esc_html__('No published logos match the selected categories and labels.')
            : esc_html__('Click the pencil icon in the block toolbar and choose at least one category.')
    );
}

// Display the Block
if ($category_ids && $logo_list_query->have_posts()) { ?>
<section class="uams-module logo-list alignfull<?php echo esc_attr($className); ?>" id="<?php echo $id; ?>">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12<?php echo ($hide_heading && empty($description)) ? ' sr-only' : ''; ?>">
                <h2 class="module-title<?php echo ($hide_heading && $description) ? ' sr-only' : ''; ?>">
                    <span class="title"><?php echo esc_html($heading); ?></span>
                </h2>
                <?php echo $description ? '<div class="module-description"><p>'.esc_html($description).'</p></div>' : ''; ?>
            </div>
            <div class="col-12">
            <div class="item-list-container<?php echo $width_class; ?>">
            <div class="item-list">
                <?php
                $logo_item_count = 0;
    while ($logo_list_query->have_posts()) {
        $logo_list_query->the_post();
        $item_id = get_the_ID();
        include UAMS_LOGO_PATH.'/templates/loops/logo-list-item.php';
        $logo_item_count++;
    }
    wp_reset_postdata(); ?>
            </div>
            </div>
            </div>
            <?php if ($more_query && ! empty($more_text) && ! empty($more_button_link)) {
                // If all values aren't present, don't display the section
                ?>
                <div class="col-12 more">
                    <p class="lead"><?php echo esc_html($more_text); ?></p>
                    <div class="cta-container">
                        <a href="<?php echo esc_url($more_button_link_url); ?>" class="btn btn-outline-<?php echo $button_color; ?>" aria-label="<?php echo esc_attr($more_button_description_attr); ?>"><?php echo esc_html($more_button_text); ?></a>
                    </div>
                </div>
            <?php } // endif?>
        </div>
    </div>
</section>
<?php } // endif?>