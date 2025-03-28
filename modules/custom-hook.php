<?php
if (!defined('ABSPATH')) {
    exit;
}

// Debug to confirm loading
error_log('custom-hooks.php loaded');

function custom_catalogx_default_description($content) {
    global $post;

    if (!is_singular('product') || !class_exists('\CatalogX\Catalog\Util') || !\CatalogX\Catalog\Util::is_available_for_product($post->ID)) {
        return $content;
    }

    $product = wc_get_product($post->ID);
    $product_description = $product->get_description();

    if (empty($product_description)) {
        $additional_input = CatalogX()->setting->get_setting('additional_input');
        if (!empty($additional_input)) {
            error_log('Replacing empty description with additional_input');
            return $additional_input;
        }
    }

    return $content;
}
add_filter('the_content', 'custom_catalogx_default_description', 20);

function custom_override_show_description_box() {
    global $post;

    if (!class_exists('\CatalogX\Catalog\Util') || !\CatalogX\Catalog\Util::is_available_for_product($post->ID)) {
        return;
    }

    $product = wc_get_product($post->ID);
    $product_description = $product->get_description();

    if (!empty($product_description)) {
        error_log('Suppressing desc-box due to existing description');
        return; // Suppress desc-box
    }
}
add_action('display_shop_page_description_box', 'custom_override_show_description_box', 5);

// Remove original hook on init
add_action('init', function() {
    remove_action('display_shop_page_description_box', ['CatalogX\Catalog\Frontend', 'show_description_box']);
});