<?php 

namespace CatalogX\Catalog;

/**
 * CatalogX Catalog Module Frontend class
 *
 * @class 		Frontend class
 * @version		6.0.0
 * @author 		MultivendorX
 */
class Frontend{
    /**
     * Frontend class constructor function.
     */
    public function __construct() {
        // Check the exclution
        if ( ! Util::is_available() ) return;

        // Cart page redirect settings
        add_action( 'template_redirect', [ $this, 'catalogx_redirect_page' ], 10 );

        // Display single product page descrioption box 
        add_action( 'display_shop_page_description_box', [ self::class, 'show_description_box' ] );

        // Hooks for exclutions
        add_filter( 'woocommerce_get_price_html' , [ $this, 'exclude_price_for_selected_product' ] , 10, 2 );

        add_filter( 'woocommerce_loop_add_to_cart_link', [ $this, 'exclude_add_to_cart_button' ], 10, 2 );
        
        add_action( 'woocommerce_single_product_summary', [ $this, 'exclusion_for_single_product_page' ], 5 );

        //register description box
        $this->register_description_box();

        add_action( 'wp_enqueue_scripts', [ $this, 'frontend_scripts' ] );
    }
    
    /**
     * Redirect cart and checkout page to home page
     * @return void
     */
    public static function catalogx_redirect_page() {

        // Get setting for sales enabled
        $sales_enabled = CatalogX()->setting->get_setting( 'enable_cart_checkout' );

        // Check sales enabled setting is enable or not
        if ( !empty($sales_enabled) ) return;

        // Get cart and checkout page id
        $cart_page_id       = wc_get_page_id( 'cart' );
        $checkout_page_id   = wc_get_page_id( 'checkout' );

        // Redirect to redirect url if page is cart page or checkout page
        if ( is_page( $cart_page_id ) || is_page( $checkout_page_id ) ) {
            wp_redirect( home_url() );
            exit;
        }
    }

    /**
     * Enqueue script
     * @return void
     */
    public function frontend_scripts() {
        if (is_product() || is_shop()) {
            wp_enqueue_style( 'catalogx-frontend-style', CatalogX()->plugin_url . 'modules/Catalog/assets/css/frontend.css' );
        }
    }

    /**
     * Display single product page descrioption box 
     * @return void
     */
    public static function show_description_box() {
        global $post;

        if ( ! Util::is_available_for_product( $post->ID  ) ) {
            return;
        }

        ?>
        <div class="desc-box">
            <?php $input_box = CatalogX()->setting->get_setting( 'additional_input' );
            if ($input_box) { ?>
                <div class="desc">
                    <?php echo $input_box; ?>
                </div>
            <?php } ?>
        </div>
        <?php
    }

    /**
     * Price exclusion for shop page
     * @return void
     */

    public function exclude_price_for_selected_product( $price, $product ) {
        $price_hide_product_page = CatalogX()->setting->get_setting( 'hide_product_price' );
        
        if ( Util::is_available_for_product( $product->get_id() ) && $price_hide_product_page && is_shop() ) {
            return '';
        }
        
        return $price;
    }

    /**
     * Shop page add to cart button exclusion for block
     * @return void
     */
    public function exclude_add_to_cart_button( $button, $product ) {
        if ( ! Util::is_available_for_product( $product->get_id() ) ) {
            return $button;
        }
        
        return empty( CatalogX()->setting->get_setting( 'enable_cart_checkout' ) ) ? '' : $button;
        
    }

    /**
     * Single product page add to cart button exclusion
     * @return void
     */
    public function exclusion_for_single_product_page() { 
        global $post;

        if ( Util::is_available_for_product( $post->ID ) && is_product() ) {
            if ( empty(CatalogX()->setting->get_setting( 'enable_cart_checkout' )) ) {
                remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30 );
                remove_action( 'woocommerce_single_variation', 'woocommerce_single_variation_add_to_cart_button', 20 );
                // for block support
                remove_action('woocommerce_simple_add_to_cart', 'woocommerce_simple_add_to_cart', 30);
            }
        }
    }

    /**
     * Register description box for display in shop page
     * @return void
     */
    public function register_description_box() {
        
        // Get shop page button settings
        $position_settings = CatalogX()->setting->get_setting( 'shop_page_possition_setting', [] );

        // Priority of colide position
        $possiton_priority = 1;

        // Possiotion after a particular section
        $possition_after   = 'sku_category';

        // If possition settings exists
        if ( $position_settings ) {
            // Get the colide possition priority
            $possiton_priority = array_search( 'additional_input', array_keys( $position_settings ) ) + 1;

            // Get the possition after
            $possition_after   = $position_settings[ 'additional_input' ]; 
        }
        
        // Display button group in a hooked based on possition setting
        switch ( $possition_after ) {
            case 'sku_category':
                add_action( 'woocommerce_product_meta_end', [ self::class, 'display_description_box' ], 99 + $possiton_priority );
                break;
            case 'add_to_cart':
            case 'product_description':
                add_action( 'woocommerce_product_meta_start', [ self::class, 'display_description_box' ], 99 + $possiton_priority );
                break;
            case 'price_section':
                add_action( 'woocommerce_single_product_summary', [ self::class, 'display_description_box' ], 10 + $possiton_priority );
                break;
            default:
                add_action( 'woocommerce_single_product_summary', [ self::class, 'display_description_box' ], 6 + $possiton_priority );
                break;
        }
    }

     /**
     * Display descriopton box
     * @return void
     */
    public static function display_description_box() {
        do_action( 'display_shop_page_description_box' );
    }
}