<?php

namespace Catalogx;

class Admin {
    /**
     * CatalogEnquiry class constructor function
     */
    public function __construct() {
        add_action( 'admin_menu', [ $this, 'add_menu' ] );
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_script' ] );
        add_action( 'load_script_textdomain_relative_path', [ $this, 'replace_path' ], 10, 2 );
    }

    /**
     * Add menu in admin panal
     * @return void
     */
    public function add_menu() {
        global $submenu;

        add_menu_page(
            __( 'catalogx', 'catalogx' ),
            __( 'CatalogX', 'catalogx' ),
            'manage_woocommerce',
            'catalogx',
            [ $this, 'menu_page_callback' ],
            'data:image/svg+xml;base64,' . base64_encode( '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><g fill="#9EA3A8" fill-rule="nonzero"><path d="M7.8,5.4c0,0.5-0.4,0.9-0.9,0.9C6.6,6.3,6.3,6,6.1,5.7c0-0.1-0.1-0.2-0.1-0.3    c0-0.5,0.4-0.9,0.9-0.9c0.1,0,0.2,0,0.3,0.1C7.6,4.7,7.8,5,7.8,5.4z M5,7.4c-0.1,0-0.2,0-0.2,0c-0.6,0-1.1,0.5-1.1,1.1    C3.6,9,4,9.4,4.4,9.6c0.1,0,0.2,0.1,0.3,0.1c0.6,0,1.1-0.5,1.1-1.1C5.9,7.9,5.5,7.5,5,7.4z M5.8,1.7c-0.6,0-1,0.5-1,1s0.5,1,1,1    s1-0.5,1-1S6.3,1.7,5.8,1.7z M2.9,2.1c-0.3,0-0.5,0.2-0.5,0.5s0.2,0.5,0.5,0.5s0.5-0.2,0.5-0.5S3.2,2.1,2.9,2.1z M0.8,5.7    C0.3,5.7,0,6.1,0,6.5s0.3,0.8,0.8,0.8s0.8-0.3,0.8-0.8S1.2,5.7,0.8,5.7z M20,10.6c-0.1,4.3-3.6,7.7-7.9,7.7c-1.2,0-2.3-0.3-3.4-0.7    l-3.5,0.6l1.4-2c-1.5-1.4-2.5-3.5-2.5-5.7c0-0.2,0-0.4,0-0.5c0.3,0.1,0.6,0.1,0.9,0C5.9,9.7,6.4,9,6.3,8.3c0-0.2-0.1-0.4-0.2-0.5    C5.7,7,4.9,6.8,4.2,6.9C4,7,3.8,7,3.7,7C3,6.9,2.5,6.4,2.4,5.8c-0.2-1,0.6-1.9,1.6-1.9C4.6,4,5.1,4.4,5.3,5c0,0.1,0,0.2,0,0.2    c0.1,0.5,0.4,1,0.9,1.2c0.2,0.1,0.5,0.2,0.7,0.2c0.7,0,1.3-0.6,1.3-1.3c0-0.5-0.3-1-0.8-1.2c1.4-1.1,3.2-1.7,5.1-1.6    C16.7,2.8,20.1,6.3,20,10.6z M14.9,8.2c0-0.3-0.2-0.5-0.5-0.5H9.9c-0.3,0-0.5,0.2-0.5,0.5v4.6c0,0.3,0.2,0.5,0.5,0.5h2.6l0.5,1.1    h1.2l-0.5-1.1h0.9c0.3,0,0.5-0.2,0.5-0.5V8.2z M10.4,12.2h1.6l-0.3-0.6l0.9-0.4l0.5,1h0.8V8.7h-3.5V12.2z"/></g></svg>' ),
            50
        );

        add_submenu_page(
            'catalogx',
            __( 'Enquiry Messages', 'catalogx' ),
            __( 'Enquiry Messages', 'catalogx' ),
            'manage_woocommerce',
            'catalogx#&tab=enquiry-messages',
            '__return_null'
        );

        add_submenu_page(
            'catalogx',
            __( 'Quotation Requests', 'catalogx' ),
            __( 'Quotation Requests', 'catalogx' ),
            'manage_woocommerce',
            'catalogx#&tab=quote-requests',
            '__return_null'
        );

        add_submenu_page(
            'catalogx',
            __( 'Wholesale Users', 'catalogx' ),
            __( 'Wholesale Users', 'catalogx' ),
            'manage_woocommerce',
            'catalogx#&tab=wholesale-users',
            '__return_null'
        );

        add_submenu_page(
            'catalogx',
            __('Dynamic Pricing Rules', 'catalogx'),
            __('Dynamic Pricing Rules', 'catalogx'),
            'manage_woocommerce',
            'catalogx#&tab=rules',
            '__return_null'
        );

        add_submenu_page(
            'catalogx',
            __( 'Settings', 'catalogx' ),
            __( 'Settings', 'catalogx' ),
            'manage_woocommerce',
            'catalogx#&tab=settings&subtab=all_settings',
            '__return_null'
        );

        add_submenu_page(
            'catalogx',
            __( 'Modules', 'catalogx' ),
            __( 'Modules', 'catalogx' ),
            'manage_woocommerce',
            'catalogx#&tab=modules',
            '__return_null'
        );

        if ( ! Utill::is_khali_dabba() ) {
            $submenu[ 'catalogx' ][] = [
                '<style>
					a:has(.upgrade-to-pro){
						background: linear-gradient(-28deg, #f6a091, #bb939c, #5f6eb3) !important;
						color: White !important;
					};
				</style>
                <div class="upgrade-to-pro"><i style="margin-right: 0.25rem" class="dashicons dashicons-awards"></i>' . __( 'Upgrade to pro', 'catalogx' ). '</div>',
                'manage_woocommerce',
                'https://catalogx.com/pricing/?utm_source=plugin&utm_medium=settings&utm_campaign=tracking'
            ];
        }

        remove_submenu_page( 'catalogx', 'catalogx' );
    }

    /**
     * Callback function for menu page
     * @return void
     */
    public function menu_page_callback() {
        echo '<div id="admin-main-wrapper"></div>';
    }

    /**
     * Enqueue javascript and css
     * @return void
     */
    public function enqueue_script() {

        if ( get_current_screen()->id !== 'toplevel_page_catalogx' ) return ;

        // Support for media
        wp_enqueue_media();
        
        // Prepare data of all pages
        $pages      = get_pages();
        $all_pages  = [];

        if ( $pages ) {
            foreach ( $pages as $page ) {
                $all_pages[] = [
                    'value' => $page->ID,
                    'label' => $page->post_title,
                    'key'   => $page->ID,
                ];
            }
        }
        
        // Prepare data of all user roles
        $roles      = wp_roles()->roles;
        $all_roles  = [];

        if ( $roles ) {
            foreach ( $roles as $key => $role ) {
                $all_roles[] = [
                    'value' => $key,
                    'label' => $role[ 'name' ],
                    'key'   => $key,
                ];
            }
        }

        // Get all users id and name and prepare data
        $users      = get_users( [ 'fields' => [ 'display_name', 'id' ] ] );
        $all_users  = [];

        foreach ( $users as $user ) {
            $all_users[] = [
                'value' => $user->ID,
                'label' => $user->display_name,
                'key'   => $user->ID,
            ];
        }

        // Prepare all products
        $products_ids = wc_get_products( [ 'limit' => -1, 'return' => 'ids' ] );
        $all_products = [];

        foreach ( $products_ids as $id ) {
            $product_name = get_the_title( $id );

            $all_products[] = [
                'value' => $id,
                'label' => $product_name,
                'key'   => $id,
            ];
        }

        // Prepare all product terms
        $terms = get_terms( 'product_cat', [ 'orderby' => 'name', 'order' => 'ASC' ] );
        $product_cat = [];

        if ( $terms && empty($terms->errors)) {
            foreach ($terms as $term) {
                $product_cat[] = [
                    'value' => $term->term_id,
                    'label' => $term->name,
                    'key'   => $term->term_id,
                ];
            }
        }

        // Prepare all product tages
        $tags         = get_terms( 'product_tag', [ 'hide_empty' => false ] );
        $product_tags = [];
        if ( $tags ) {
            foreach ( $tags as $tag ) {
                $product_tags[] = [
                    'value' => $tag->term_id,
                    'label' => $tag->name,
                    'key'   => $tag->term_id,
                ];
            }
        }

        // Get current user role
        $current_user      = wp_get_current_user();
        $current_user_role = '';
        if ( ! empty( $current_user->roles ) && is_array( $current_user->roles ) ) {
            $current_user_role = reset($current_user->roles);
        }

        // Get all tab setting's database value
        $settings_value = [];
        $tabs_names     = [ 'enquiry_catalog_customization', 'all_settings', 'enquiry_form_customization', 'enquiry_quote_exclusion', 'tools', 'enquiry_email_temp', 'wholesale', 'wholesale_registration', 'pages' ];
        foreach ( $tabs_names as $tab_name ) {
            $settings_value[ $tab_name ] = Catalog()->setting->get_option( 'catalog_' . $tab_name . '_settings' );
        }

        if ($current_user_role === 'administrator') {
            $quote_base_url = admin_url('admin.php?page=wc-orders&action=edit&id=');
        } elseif ($current_user_role === 'customer') {
            $quote_base_url = site_url('/my-account/view-quote/');
        } else {
            $quote_base_url = '/';
        }
        

        // Enque script and style
        wp_enqueue_style('mvx-catalog-style', Catalog()->plugin_url . 'build/index.css');
        wp_enqueue_script('mvx-catalog-script', Catalog()->plugin_url . 'build/index.js', [ 'wp-element', 'wp-i18n', 'react-jsx-runtime' ], '1.0.0', true);
        wp_set_script_translations( 'mvx-catalog-script', 'catalogx' );

        // Localize script
        wp_localize_script( 'mvx-catalog-script', 'appLocalizer', apply_filters( 'catalog_settings', [
            'apiurl'                    => untrailingslashit( get_rest_url() ),
            'nonce'                     => wp_create_nonce('wp_rest'),
            'tab_name'                 => "CatalogX",
            'restUrl'                  => 'catalog/v1',
            'all_pages'                 => $all_pages,
            'role_array'                => $all_roles,
            'all_users'                 => $all_users,
            'all_products'              => $all_products,
            'all_product_cat'           => $product_cat,
            'all_product_tag'           => $product_tags,
            'settings_databases_value'  => $settings_value,
            'active_modules'            => Catalog()->modules->get_active_modules(),
            'user_role'                 => $current_user_role,
            'banner_img'                => Catalog()->plugin_url . 'assets/images/catalog-pro-add-admin-banner.jpg',
            'template1'                 => Catalog()->plugin_url . 'assets/images/email/templates/default_wc_tpl.png',
            'template2'                 => Catalog()->plugin_url . 'assets/images/email/templates/woocommerce_catalog_send_email_tpl_1.png',
            'template3'                 => Catalog()->plugin_url . 'assets/images/email/templates/woocommerce_catalog_send_email_tpl_2.png',
            'template4'                 => Catalog()->plugin_url . 'assets/images/email/templates/woocommerce_catalog_send_email_tpl_3.png',
            'template5'                 => Catalog()->plugin_url . 'assets/images/email/templates/woocommerce_catalog_send_email_tpl_4.png',
            'template6'                 => Catalog()->plugin_url . 'assets/images/email/templates/woocommerce_catalog_send_email_tpl_5.png',
            'template7'                 => Catalog()->plugin_url . 'assets/images/email/templates/woocommerce_catalog_send_email_tpl_6.png',
            'khali_dabba'                => Utill::is_khali_dabba(),
            'pro_url'                   => esc_url( WOOCOMMERCE_CATALOG_ENQUIRY_PRO_SHOP_URL ),
            'order_edit'                => admin_url( "admin.php?page=wc-orders&action=edit" ),
            'site_url'                  => admin_url( 'admin.php?page=catalogx#&tab=settings&subtab=all_settings' ),
            'module_page_url'           => admin_url( 'admin.php?page=catalogx#&tab=modules' ),
            'settings_page_url'           => admin_url( 'admin.php?page=catalogx#&tab=settings&subtab=all_settings' ),
            'enquiry_form_settings_url'   => admin_url( 'admin.php?page=catalogx#&tab=settings&subtab=enquiry_form_customization' ),
            'customization_settings_url'  => admin_url( 'admin.php?page=catalogx#&tab=settings&subtab=enquiry_catalog_customization' ),
            'wholesale_settings_url'      => admin_url( 'admin.php?page=catalogx#&tab=settings&subtab=wholesale' ),
            'rule_url'                    => admin_url( 'admin.php?page=catalogx#&tab=rules' ),
            'currency'                  => get_woocommerce_currency(),
            'stock_alert_open'          => is_plugin_active('woocommerce-product-stock-alert/product_stock_alert.php'),
            'mvx_active'                => Utill::is_active_MVX(),
            'quote_module_active'       => Catalog()->modules->is_active('quote'),
            'quote_base_url'            => $quote_base_url
        ]));
    }

    public function replace_path($path, $url) {
        if (strpos($url, 'woocommerce-catalog-enquiry') !== false) {   
            foreach (Catalog()->block_paths as $key => $new_path) {
                if (strpos($url, $key) !== false) {
                    $path = $new_path;
                }
            }
    
            if (strpos($url, 'block') === false) {
                $path = 'build/index.js';
            }
        }
        
        return $path;
    }

}
