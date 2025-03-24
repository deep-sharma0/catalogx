<?php 

namespace CatalogX\Enquiry;
use CatalogX\Utill;

/**
 * CatalogX Enquiry Module Frontend class
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

        $display_enquiry_button = CatalogX()->setting->get_setting( 'enquiry_user_permission', [] );
        if ( !empty($display_enquiry_button) && !is_user_logged_in()) {
            return;
        }

        if ( empty( CatalogX()->setting->get_setting( 'enable_cart_checkout' ) ) ) {
            add_action( 'woocommerce_after_shop_loop_item', [$this, 'add_button_in_shop_page'] );
        }

        add_action( 'display_shop_page_button', [ $this, 'add_enquiry_button' ] );

        //Hook for exclusion
        add_action( 'woocommerce_single_product_summary', [ $this, 'enquiry_button_exclusion' ], 5);

        add_action( 'wp_enqueue_scripts', [ $this, 'frontend_scripts' ] );

        // Enquiry button shortcode
        add_shortcode( 'catalogx_enquiry_button', [ $this, 'catalogx_enquiry_button_shortcode' ] );

    }

    /**
     * Add enquiry button
     * @return void
     */
    public function add_enquiry_button($productObj) {
        global $product;
        $productObj = is_int($productObj) ? wc_get_product($productObj) : ($productObj ?: $product);

        if ( empty( $productObj ) )
            return;

        if ( CatalogX()->setting->get_setting( 'is_enable_multiple_product_enquiry' ) && Utill::is_khali_dabba() ) {
            return;
        }

        $button_settings = CatalogX()->setting->get_setting( 'enquiry_button', [] );
        $button_css = Utill::get_button_styles($button_settings);
        $button_hover_css = Utill::get_button_styles($button_settings, true);
        
        if ( $button_hover_css ) {
            echo '<style>
                .catalogx-enquiry-btn:hover{
                '. esc_html( $button_hover_css ) .'
                } 
            </style>';
        }

        $additional_css_settings = CatalogX()->setting->get_setting( 'custom_css_product_page' );
        if (isset($additional_css_settings) && !empty($additional_css_settings)) {
            $button_css .= $additional_css_settings;
        }
        
        $button_settings[ 'button_text' ] = !empty( $button_settings[ 'button_text' ] ) ? $button_settings[ 'button_text' ] : \CatalogX\Utill::get_translated_string( 'catalogx', 'send_an_enquiry', 'Send an enquiry' );
        $button_position_settings = CatalogX()->setting->get_setting( 'shop_page_button_position_setting', [] );
        $position = array_search('enquiry_button', $button_position_settings);
        $position = $position !== false ? $position : 0;

        ?>
        <div id="catalogx-enquiry">
        <?php 
            if (CatalogX()->setting->get_setting( 'is_enable_out_of_stock' ) ){
                if ( !$productObj->managing_stock() && !$productObj->is_in_stock()) { ?>
                <div position = "<?php echo $position; ?>">
                    <button class="catalogx-enquiry-btn button demo btn btn-primary btn-large wp-block-button__link" style="<?php echo $button_css; ?>" href="#catalogx-modal"><?php echo esc_html( $button_settings[ 'button_text' ] ); ?></button>
                </div>
                <?php
                }
        } else { ?>
                <div position = "<?php echo $position; ?>">
                    <button class="catalogx-enquiry-btn button demo btn btn-primary btn-large wp-block-button__link" style="<?php echo $button_css; ?>" href="#catalogx-modal"><?php echo esc_html( $button_settings[ 'button_text' ] ); ?></button>
                </div>
                <?php
            }
             ?>
            <input type="hidden" name="product_name_for_enquiry" id="product-name-for-enquiry" value="<?php echo $productObj->get_name(); ?>" />
            <input type="hidden" name="product_url_for_enquiry" id="product-url-for-enquiry" value="<?php echo get_permalink( $productObj->get_id() ); ?>" />
            <input type="hidden" name="product_id_for_enquiry" id="product-id-for-enquiry" value="<?php echo $productObj->get_id(); ?>" />
            <input type="hidden" name="enquiry_product_type" id="enquiry-product-type" value="<?php
                if ($productObj && $productObj->is_type('variable')) {
                    echo 'variable';
                }
                ?>" />
            <input type="hidden" name="user_id_for_enquiry" id="user-id-for-enquiry" value="<?php echo get_current_user_id(); ?>" />  			
        </div>
        <div id="catalogx-modal" style="display: none;" class="catalogx-modal <?php echo (CatalogX()->setting->get_setting( 'is_disable_popup' ) == 'popup') ? 'popup_enable' : '' ?>">
        </div>	
        <?php
    }

    /**
     * Enquiry button exclusion
     * @return void
     */
    public function enquiry_button_exclusion() { 
        global $post;
        
        if ( ! Util::is_available_for_product( $post->ID ) ) {
            remove_action( 'display_shop_page_button', [ $this, 'add_enquiry_button' ] );
        } else {
            add_action( 'display_shop_page_button', [ $this, 'add_enquiry_button' ] );
        }
    }

    /**
     * Enqueue script
     * @return void
     */
    public function frontend_scripts() {
        $current_user = wp_get_current_user();

        wp_register_style( 'catalogx-enquiry-form-style', CatalogX()->plugin_url . 'build/blocks/enquiryForm/index.css' );
        wp_register_script( 'catalogx-enquiry-frontend-script', CatalogX()->plugin_url . 'modules/Enquiry/assets/js/frontend.js', [ 'jquery', 'jquery-blockui' ], CatalogX()->version, true );
        wp_register_script('enquiry-form-script', CatalogX()->plugin_url . 'build/blocks/enquiryForm/index.js', [ 'jquery', 'jquery-blockui', 'wp-element', 'wp-i18n', 'wp-blocks', 'wp-hooks' ], CatalogX()->version, true );
        wp_localize_script(
            'enquiry-form-script', 'enquiryFormData', [
            'apiurl'        => untrailingslashit(get_rest_url()),
            'nonce'         => wp_create_nonce( 'wp_rest' ),
            'settings_free' => $this->catalogx_free_form_settings(),
            'settings_pro'  => $this->catalogx_pro_form_settings(),
            'khali_dabba'    => \CatalogX\Utill::is_khali_dabba(),
            'product_data'  => (\CatalogX\Utill::is_khali_dabba() && !empty(CatalogX_Pro()->cart->get_cart_data())) ? CatalogX_Pro()->cart->get_cart_data() : '',
            'default_placeholder'  => [
                'name'  => $current_user->display_name,
                'email' => $current_user->user_email
            ],
            'content_before_form' => apply_filters('catalogx_add_content_before_form', ''),
            'content_after_form'  => apply_filters('catalogx_add_content_after_form', ''),
        ]);

        if (is_product()) {
            wp_enqueue_style( 'catalogx-enquiry-form-style' );

            // additional css
            $additional_css_settings = CatalogX()->setting->get_setting( 'custom_css_product_page' );
            if (isset($additional_css_settings) && !empty($additional_css_settings)) {
                wp_add_inline_style('catalogx-enquiry-form-style', $additional_css_settings);
            }
            
            wp_enqueue_script( 'catalogx-enquiry-frontend-script' );
            wp_enqueue_script( 'enquiry-form-script' );
        }
    }

    public function catalogx_free_form_settings() {
        $form_settings = CatalogX()->setting->get_option( 'catalogx_enquiry-form-customization_settings', [] );
    
        if ( function_exists( 'icl_t' ) ) {
            foreach ( $form_settings['freefromsetting'] as &$free_field ) {
                if ( isset( $free_field['label'] ) ) {
                    $free_field['label'] = icl_t( 'catalogx', 'free_form_label_' . $free_field['key'], $free_field['label'] );
                }
            }
        }
        
        return $form_settings['freefromsetting'];
    }

    public function catalogx_pro_form_settings() {
        $form_settings = CatalogX()->setting->get_option( 'catalogx_enquiry-form-customization_settings', [] );
    
        if ( function_exists( 'icl_t' ) ) {
            foreach ( $form_settings['formsettings']['formfieldlist'] as &$field ) {
                if ( isset( $field['label'] ) ) {
                    $field['label'] = icl_t( 'catalogx', 'form_field_label_' . $field['id'], $field['label'] );
                }
                if ( isset( $field['placeholder'] ) ) {
                    $field['placeholder'] = icl_t( 'catalogx', 'form_field_placeholder_' . $field['id'], $field['placeholder'] );
                }
                if ( isset( $field['options'] ) ) {
                    foreach ( $field['options'] as &$option ) {
                        $option['label'] = icl_t( 'catalogx', 'form_field_option_' . $field['id'] . '_' . $option['value'], $option['label'] );
                    }
                }
            }
        }

        return $form_settings[ 'formsettings' ];
    }

    /**
     * enquiry button shortcode
     * @return void
     */
    public function catalogx_enquiry_button_shortcode($attr) {
        ob_start();
        $product_id = isset( $attr['product_id'] ) ? (int)$attr['product_id'] : 0;

        remove_action('display_shop_page_button', [ $this, 'add_enquiry_button' ]);

        $this->add_enquiry_button($product_id);
        return ob_get_clean();
    }

    /**
     * Add enquiry button in shop page
     * @return void
     */
    public function add_button_in_shop_page() {
        global $product;
        if ( ! Util::is_available_for_product( $product->get_id() ) ) {
            return;
        }

        if (!empty(CatalogX()->setting->get_setting( 'is_enable_out_of_stock' )) ){
            if ( $product->is_in_stock()) {
                return;
            }
        }

        if ( CatalogX()->setting->get_setting( 'is_enable_multiple_product_enquiry' ) && Utill::is_khali_dabba() ) {
            return;
        }

        $button_settings = CatalogX()->setting->get_setting( 'enquiry_button', [] );
        $button_css = Utill::get_button_styles($button_settings);
        $button_hover_css = Utill::get_button_styles($button_settings, true);
        if ( $button_hover_css ) {
            echo '<style>
                .single_add_to_cart_button:hover{
                '. esc_html( $button_hover_css ) .'
                } 
            </style>';
        }

        $additional_css_settings = CatalogX()->setting->get_setting( 'custom_css_product_page' );
        if (isset($additional_css_settings) && !empty($additional_css_settings)) {
            $button_css .= $additional_css_settings;
        }
        $button_text = !empty( $button_settings[ 'button_text' ] ) ? $button_settings[ 'button_text' ] : \CatalogX\Utill::get_translated_string( 'catalogx', 'send_an_enquiry', 'Send an enquiry' );
        if ( is_shop() ) {
            $product_link = get_permalink( $product->get_id() );
            echo '<a href="' . esc_url( $product_link ) . '" class="single_add_to_cart_button button wp-block-button__link" style="' . esc_attr( $button_css ) . '">' . esc_html( $button_text ) . '</a>';
        }
    }

}