<?php

namespace CatalogX;

class Shortcode {
    /**
     * Shortcode class construct function
     */
	public function __construct() {
		//For quote page
		add_shortcode( 'request_quote', [ $this, 'display_request_quote' ] );
        //For quote thank you page
		add_shortcode( 'request_quote_thank_you', [ $this, 'display_request_quote_thank_you' ] );
        
    }

    function frontend_scripts() {

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
    
            wp_enqueue_script('quote-thank-you-script', CatalogX()->plugin_url . 'build/blocks/quote-thank-you/index.js', [ 'wp-blocks', 'jquery', 'jquery-blockui', 'wp-element', 'wp-i18n' ], CatalogX()->version, true);
            wp_set_script_translations( 'quote-thank-you-script', 'catalogx' );
            wp_localize_script(
                'quote-thank-you-script', 'quoteThankYou', [
                'apiUrl' => untrailingslashit(get_rest_url()),
                'quote_my_account_url'  => site_url('/my-account/all-quotes/'),
                'khali_dabba'           => Utill::is_khali_dabba(),
            ]);
        }
    }

	public function display_request_quote() {
        $this->frontend_scripts();
		ob_start();
        ?>
        <div id="request_quote_list">
        </div>
        <?php
		return ob_get_clean();
	}
    
    public function display_request_quote_thank_you() {
        $this->frontend_scripts();
        ob_start();
        ?>
        <div id="quote_thank_you_page">
        </div>
        <?php
        return ob_get_clean();
    }

} 