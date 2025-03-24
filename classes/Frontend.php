<?php


namespace CatalogX;

/**
 * CatalogX frontend class
 *
 * @class 		Frontend class
 * @version		6.0.0
 * @author 		MultiVendorX
 */
class Frontend {
    /**
     * Fontend class constructor functions
     */
    public function __construct() {
        add_action( 'init', [ $this, 'display_button_group' ] );
        add_action( 'wp', [ $this, 'display_price_and_description' ] );

        add_action('woocommerce_cart_calculate_fees', [$this, 'calculate_discount']);
        add_action('woocommerce_review_order_before_shipping', [$this, 'calculate_discount']);

    }

    /**
     * Register button group display function in shop pages.
     * @return void
     */
    public function display_button_group() {
        // Get shop page button settings
        $position_settings = CatalogX()->setting->get_setting( 'shop_page_possition_setting', [] );        

        // Priority of colide position
        $possiton_priority = 1;

        // Possiotion after a particular section
        $possition_after   = 'sku_category';

        // If possition settings exists
        if ( $position_settings ) {
            // Get the colide possition priority
            $possiton_priority = array_search( 'custom_button', array_keys( $position_settings ) ) + 1;

            // Get the possition after
            $possition_after   = $position_settings[ 'custom_button' ]; 
        }

        // Display button group in a hooked based on possition setting
        switch ( $possition_after ) {
            case 'sku_category':
                add_action( 'woocommerce_product_meta_end', [ $this, 'add_button_group' ], 99 + $possiton_priority );
                break;
            case 'add_to_cart':
                add_action( 'woocommerce_after_add_to_cart_button', [ $this, 'add_button_group' ], 99 + $possiton_priority );
                break;
            case 'product_description':
                add_action( 'woocommerce_before_add_to_cart_form', [ $this, 'add_button_group' ], 99 + $possiton_priority );
                break;
            case 'price_section':
                add_action( 'woocommerce_single_product_summary', [ $this, 'add_button_group' ], 10 + $possiton_priority );
                break;
            default:
                add_action( 'woocommerce_single_product_summary', [ $this, 'add_button_group' ], 6 + $possiton_priority );
                break;
        }
    }

    /**
     * Display all button group
     * @return void
     */
    function add_button_group() {
        ?>
        <!-- single-product-page-action-btn-catalogx -->
            <div class="single-product-page-action-btn-catalogx">
                <?php do_action( 'display_shop_page_button' ); ?>
            </div>
        <?php
    }

    /**
     * Display product price and description in single product page.
     * @return void
     */
    public function display_price_and_description() {
        $price_hide_product_page = CatalogX()->setting->get_setting( 'hide_product_price' );
        if ( $price_hide_product_page && is_product() ) {
            add_filter( 'woocommerce_show_variation_price', '__return_false' );
            remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price', 10 );
            remove_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_price', 10 );
            // for block support
            add_filter( 'woocommerce_get_price_html', '__return_empty_string' );
        }
        
        $desc_hide_product_page = CatalogX()->setting->get_setting( 'hide_product_desc' );
        if ( $desc_hide_product_page ) {
            remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_excerpt', 20);
            // for block support
            add_filter('woocommerce_short_description', '__return_empty_string');
            add_filter('render_block_core/post-excerpt', '__return_empty_string');
        }
    }

    /**
     * Calculate discount on cart and checkout page.
     * @return void
     */
    public function calculate_discount() {
        /**
         * Filter for cart and checkout discount
         * @var int discount amount
         */
        $discount = apply_filters( 'catalogx_calculate_discount_in_cart_and_checkout', 0 );

        if ($discount != 0) {
            // Apply the discount as a fee
            WC()->cart->add_fee( __('Discount', 'catalogx'), $discount, true );
        }
    }

}