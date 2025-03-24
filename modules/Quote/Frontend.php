<?php

namespace CatalogX\Quote;
use CatalogX\Utill;

/**
 * CatalogX Quote Module Frontend class
 *
 * @class 		Frontend class
 * @version		6.0.0
 * @author 		MultivendorX
 */
class Frontend {
    /**
     * Frontend class constructor functions
     */
    public function __construct() {
        if ( ! Util::is_available() ) return;

        $display_quote_button = CatalogX()->setting->get_setting( 'quote_user_permission', [] );
        if (!empty($display_quote_button) && !is_user_logged_in()) {
            return;
        }
        add_action( 'display_shop_page_button', [ $this, 'add_button_for_quote'] );
        add_action( 'woocommerce_after_shop_loop_item', [$this, 'add_button_for_quote'], 11 );
        add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );

        // Quote button shortcode
        add_shortcode( 'catalogx_quote_button', [ $this, 'catalogx_quote_button_shortcode' ] );
    }

    /**
     * Enqueue frontend js
     * @return void
     */
    public function enqueue_scripts() {
        $frontend_script_path = CatalogX()->plugin_url . 'modules/Quote/js/';
        $frontend_script_path = str_replace( [ 'http:', 'https:' ], '', $frontend_script_path );
        
        wp_register_script('add-to-quote-cart-script', $frontend_script_path . 'frontend.js', ['jquery'], CatalogX()->version, true);
        wp_localize_script(
            'add-to-quote-cart-script',
            'addToQuoteCart',
            [
                'ajaxurl' => admin_url('admin-ajax.php'),
                'loader' => admin_url('images/wpspin_light.gif'),
                'no_more_product' => __('No more product in Quote list!', 'catalogx'),
                ]
            );
        if (is_shop() || is_product()) {
            wp_enqueue_script('add-to-quote-cart-script');
        }
    }

    /**
     * add quote button in single product page and shop page
     */
    public function add_button_for_quote($productObj) {
        global $product;
        
        $productObj = is_int($productObj) ? wc_get_product($productObj) : ($productObj ?: $product);

        if ( empty( $productObj ) )
            return;

        //Exclusion settings for shop and single product page
        if ( ! Util::is_available_for_product($productObj->get_id()) ) {
            return;
        }

        $quote_btn_text = Utill::get_translated_string( 'catalogx', 'add_to_quote', 'Add to Quote' );    
        $view_quote_btn_text = Utill::get_translated_string( 'catalogx', 'view_quote', 'View Quote' ); 

        $button_settings = CatalogX()->setting->get_setting( 'quote_button' );
        $button_css = Utill::get_button_styles($button_settings);
        $button_hover_css = Utill::get_button_styles($button_settings, true);
        
        if ( $button_hover_css ) {
            echo '<style>
                .catalogx-add-request-quote-button:hover{
                '. esc_html( $button_hover_css ) .'
                } 
            </style>';
        } 

        $quote_btn_text = !empty( $button_settings[ 'button_text' ] ) ? $button_settings[ 'button_text' ] : $quote_btn_text;
        CatalogX()->util->get_template('quote-button-template.php',
        [
            'class'             => 'catalogx-add-request-quote-button ',
            'btn_css'           => $button_css,
            'wpnonce'           => wp_create_nonce( 'add-quote-' . $productObj->get_id() ),
            'product_id'        => $productObj->get_id(),
            'label'             => $quote_btn_text,
            'label_browse'      => $view_quote_btn_text,
            'rqa_url'           => CatalogX()->quotecart->get_request_quote_page_url(),
            'exists'            => CatalogX()->quotecart->exists_in_cart( $productObj->get_id() )
        ]);
    }

    public function catalogx_quote_button_shortcode($attr) {
        ob_start();
        $product_id = isset( $attr['product_id'] ) ? (int)$attr['product_id'] : 0;
        remove_action('display_shop_page_button', [ $this, 'add_button_for_quote' ]);
        $this->add_button_for_quote($product_id);
        return ob_get_clean();
    }

}