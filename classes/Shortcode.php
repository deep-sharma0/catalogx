<?php

namespace CatalogX;

/**
 * CatalogX Shortcode class
 *
 * @class 		Shortcode class
 * @version		6.0.0
 * @author 		MultivendorX
 */
class Shortcode {
    /**
     * Shortcode class construct function
     */
    public function __construct() {
        //For quote page
        add_shortcode( 'catalogx_request_quote', [ $this, 'display_request_quote' ] );
    }

    public function frontend_scripts() {

        $current_user = wp_get_current_user();
        if (CatalogX()->modules->is_active('quote')) {
            wp_enqueue_script('quote-cart', CatalogX()->plugin_url . 'build/blocks/quote-cart/index.js', [ 'jquery', 'jquery-blockui', 'wp-element', 'wp-i18n', 'wp-blocks' ], CatalogX()->version, true);
            wp_set_script_translations( 'quote-cart', 'catalogx' );
            wp_localize_script(
                'quote-cart', 'quoteCart', [
                'apiUrl' => untrailingslashit(get_rest_url()),
                'nonce' => wp_create_nonce( 'wp_rest' ),
                'restUrl' => CatalogX()->rest_namespace,
                'name'  => $current_user->display_name,
                'email' => $current_user->user_email
            ]);
            wp_register_style('quote-cart-style', CatalogX()->plugin_url . 'build/blocks/quote-cart/index.css');
            wp_enqueue_style('quote-cart-style');
        }
    }

    public function display_request_quote() {
        $this->frontend_scripts();
        ob_start();
        ?>
        <div id="request-quote-list">
        </div>
        <?php
        return ob_get_clean();
    }
    
} 