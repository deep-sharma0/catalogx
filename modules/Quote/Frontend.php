<?php

namespace CatalogX\Quote;

use CatalogX\Utill;

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
        add_action ('display_shop_page_button', [ $this, 'add_button_for_quote'] );
        add_action('woocommerce_after_shop_loop_item', [$this, 'add_button_for_quote'], 11 );
        add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );

        // Quote button shortcode
        add_shortcode( 'catalogx_quote_button', [ $this, 'catalogx_quote_button_shortcode' ] );
    }

    /**
     * Enqueue frontend js
     * @return void
     */
    public function enqueue_scripts() {
        if (is_shop() || is_product()) {
             $frontend_script_path = CatalogX()->plugin_url . 'modules/Quote/js/';
            $frontend_script_path = str_replace( [ 'http:', 'https:' ], '', $frontend_script_path );

            wp_enqueue_script('add-to-quote-cart-script', $frontend_script_path . 'frontend.js', ['jquery'], CatalogX()->version, true);
            wp_localize_script(
                'add-to-quote-cart-script',
                'addToQuoteCart',
                [
                    'ajaxurl' => admin_url('admin-ajax.php'),
                    'loader' => admin_url('images/wpspin_light.gif'),
                    'no_more_product' => __('No more product in Quote list!', 'catalogx'),
                ]
            );
        }
    }

    /**
     * add quote button in single product page and shop page
     */
    public function add_button_for_quote() {
        global $product;
        
        if (!$product) return;
        //Exclusion settings for shop and single product page
        if ( ! Util::is_available_for_product($product->get_id()) ) {
            return;
        }

        $quote_btn_text = Utill::get_translated_string( 'catalogx', 'add_to_quote', 'Add to Quote' );    
        $view_quote_btn_text = Utill::get_translated_string( 'catalogx', 'view_quote', 'View Quote' ); 

        $settings_array = CatalogX()->setting->get_setting( 'quote_button' );
        $btn_css = $button_hover_css = "";
        $border_size = ( !empty( $settings_array[ 'button_border_size' ] ) ) ? esc_html( $settings_array[ 'button_border_size' ] ).'px' : '1px';
        if ( !empty( $settings_array[ 'button_background_color' ] ) )
            $btn_css .= "background:" . esc_html( $settings_array[ 'button_background_color' ] ) . ";";
        if ( !empty( $settings_array[ 'button_text_color' ] ) )
            $btn_css .= "color:" . esc_html( $settings_array[ 'button_text_color' ] ) . ";";
        if ( !empty( $settings_array[ 'button_border_color' ] ) )
            $btn_css .= "border: " . $border_size . " solid " . esc_html( $settings_array[ 'button_border_color' ] ) . ";";
        if ( !empty( $settings_array[ 'button_font_size' ] ) )
            $btn_css .= "font-size:" . esc_html( $settings_array[ 'button_font_size' ] ) . "px;";
        if ( !empty( $settings_array[ 'button_border_radious' ] ) )
            $btn_css .= "border-radius:" . esc_html( $settings_array[ 'button_border_radious' ] ) . "px;";
        if ( !empty( $settings_array[ 'button_font_width' ] ) )
            $btn_css .= "font-weight:" . esc_html( $settings_array[ 'button_font_width' ] ) . "px;";
        if ( !empty( $settings_array[ 'button_padding' ] ) )
            $btn_css .= "padding:" . esc_html( $settings_array[ 'button_padding' ] ) . "px;";
        if ( !empty( $settings_array[ 'button_margin' ] ) )
            $btn_css .= "margin:" . esc_html( $settings_array[ 'button_margin' ] ) . "px;";

        if ( isset( $settings_array[ 'button_background_color_onhover' ] ) )
            $button_hover_css .= !empty( $settings_array[ 'button_background_color_onhover' ] ) ? 'background: ' . $settings_array[ 'button_background_color_onhover' ] . ' !important;' : '';
        if ( isset( $settings_array[ 'button_text_color_onhover' ] ) )
            $button_hover_css .= !empty( $settings_array[ 'button_text_color_onhover' ] ) ? ' color: ' . $settings_array[ 'button_text_color_onhover' ] . ' !important;' : '';
        if ( isset( $settings_array[ 'button_border_color_onhover' ] ) )
            $button_hover_css .= !empty( $settings_array[ 'button_border_color_onhover' ] ) ? 'border: ' . $border_size . ' solid' . $settings_array[ 'button_border_color_onhover' ] . ' !important;' : '';
        
            if ( $button_hover_css ) {
            echo '<style>
                .catalogx-add-request-quote-button:hover{
                '. esc_html( $button_hover_css ) .'
                } 
            </style>';
        } 

        $quote_btn_text = !empty( $settings_array[ 'button_text' ] ) ? $settings_array[ 'button_text' ] : $quote_btn_text;
        CatalogX()->util->get_template('quote-button-template.php',
        [
            'class'             => 'catalogx-add-request-quote-button ',
            'btn_css'         => $btn_css,
            'wpnonce'           => wp_create_nonce( 'add-quote-' . $product->get_id() ),
            'product_id'        => $product->get_id(),
            'label'             => $quote_btn_text,
            'label_browse'      => $view_quote_btn_text,
            'rqa_url'           => CatalogX()->quotecart->get_request_quote_page_url(),
            'exists'            => CatalogX()->quotecart->exists_in_cart( $product->get_id() )
        ]);
    }

    public function catalogx_quote_button_shortcode() {
        ob_start();
        remove_action('display_shop_page_button', [ $this, 'add_button_for_quote' ]);
        $this->add_button_for_quote();
        return ob_get_clean();
    }

}