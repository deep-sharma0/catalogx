<?php

namespace CatalogX;

/**
 * CatalogX Admin class
 *
 * @class 		Admin class
 * @version		6.0.0
 * @author 		MultivendorX
 */
class Admin {
    public function __construct() {
        //Register admin menu
        add_action( 'admin_menu', [ $this, 'add_menu' ] );
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_script' ] );
        //For load translation
        add_action( 'load_script_textdomain_relative_path', [ $this, 'textdomain_relative_path' ], 10, 2 );
    }

    /**
     * Add menu in admin panal
     * @return void
     */
    public function add_menu() {
        global $submenu;

        add_menu_page(
            'CatalogX',
            'CatalogX',
            'manage_woocommerce',
            'catalogx',
            [ $this, 'menu_page_callback' ],
            'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAyMCAyMCI+PGcgZmlsbD0iIzlFQTNBOCIgZmlsbC1ydWxlPSJub256ZXJvIj48cGF0aCBkPSJNNy44LDUuNGMwLDAuNS0wLjQsMC45LTAuOSwwLjlDNi42LDYuMyw2LjMsNiw2LjEsNS43YzAtMC4xLTAuMS0wLjItMC4xLTAuMyAgICBjMC0wLjUsMC40LTAuOSwwLjktMC45YzAuMSwwLDAuMiwwLDAuMywwLjFDNy42LDQuNyw3LjgsNSw3LjgsNS40eiBNNSw3LjRjLTAuMSwwLTAuMiwwLTAuMiwwYy0wLjYsMC0xLjEsMC41LTEuMSwxLjEgICAgQzMuNiw5LDQsOS40LDQuNCw5LjZjMC4xLDAsMC4yLDAuMSwwLjMsMC4xYzAuNiwwLDEuMS0wLjUsMS4xLTEuMUM1LjksNy45LDUuNSw3LjUsNSw3LjR6IE01LjgsMS43Yy0wLjYsMC0xLDAuNS0xLDFzMC41LDEsMSwxICAgIHMxLTAuNSwxLTFTNi4zLDEuNyw1LjgsMS43eiBNMi45LDIuMWMtMC4zLDAtMC41LDAuMi0wLjUsMC41czAuMiwwLjUsMC41LDAuNXMwLjUtMC4yLDAuNS0wLjVTMy4yLDIuMSwyLjksMi4xeiBNMC44LDUuNyAgICBDMC4zLDUuNywwLDYuMSwwLDYuNXMwLjMsMC44LDAuOCwwLjhzMC44LTAuMywwLjgtMC44UzEuMiw1LjcsMC44LDUuN3ogTTIwLDEwLjZjLTAuMSw0LjMtMy42LDcuNy03LjksNy43Yy0xLjIsMC0yLjMtMC4zLTMuNC0wLjcgICAgbC0zLjUsMC42bDEuNC0yYy0xLjUtMS40LTIuNS0zLjUtMi41LTUuN2MwLTAuMiwwLTAuNCwwLTAuNWMwLjMsMC4xLDAuNiwwLjEsMC45LDBDNS45LDkuNyw2LjQsOSw2LjMsOC4zYzAtMC4yLTAuMS0wLjQtMC4yLTAuNSAgICBDNS43LDcsNC45LDYuOCw0LjIsNi45QzQsNywzLjgsNywzLjcsN0MzLDYuOSwyLjUsNi40LDIuNCw1LjhjLTAuMi0xLDAuNi0xLjksMS42LTEuOUM0LjYsNCw1LjEsNC40LDUuMyw1YzAsMC4xLDAsMC4yLDAsMC4yICAgIGMwLjEsMC41LDAuNCwxLDAuOSwxLjJjMC4yLDAuMSwwLjUsMC4yLDAuNywwLjJjMC43LDAsMS4zLTAuNiwxLjMtMS4zYzAtMC41LTAuMy0xLTAuOC0xLjJjMS40LTEuMSwzLjItMS43LDUuMS0xLjYgICAgQzE2LjcsMi44LDIwLjEsNi4zLDIwLDEwLjZ6IE0xNC45LDguMmMwLTAuMy0wLjItMC41LTAuNS0wLjVIOS45Yy0wLjMsMC0wLjUsMC4yLTAuNSwwLjV2NC42YzAsMC4zLDAuMiwwLjUsMC41LDAuNWgyLjZsMC41LDEuMSAgICBoMS4ybC0wLjUtMS4xaDAuOWMwLjMsMCwwLjUtMC4yLDAuNS0wLjVWOC4yeiBNMTAuNCwxMi4yaDEuNmwtMC4zLTAuNmwwLjktMC40bDAuNSwxaDAuOFY4LjdoLTMuNVYxMi4yeiIvPjwvZz48L3N2Zz4=',
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
            'catalogx#&tab=settings&subtab=all-settings',
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
                CATALOGX_PRO_SHOP_URL
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
        $tabs_names     = [ 'enquiry-catalog-customization', 'all-settings', 'enquiry-form-customization', 'enquiry-quote-exclusion', 'tools', 'enquiry-email-temp', 'wholesale', 'wholesale-registration', 'pages' ];
        foreach ( $tabs_names as $tab_name ) {
            $settings_value[ $tab_name ] = CatalogX()->setting->get_option( 'catalogx_' . $tab_name . '_settings' );
        }

        if ($current_user_role === 'administrator') {
            $quote_base_url = admin_url('admin.php?page=wc-orders&action=edit&id=');
        } elseif ($current_user_role === 'customer') {
            $quote_base_url = site_url('/my-account/view-quote/');
        } else {
            $quote_base_url = '/';
        }

        // Enque script and style
        wp_enqueue_style('catalogx-style', CatalogX()->plugin_url . 'build/index.css');
        wp_enqueue_script('catalogx-script', CatalogX()->plugin_url . 'build/index.js', [ 'wp-element', 'wp-i18n', 'react-jsx-runtime' ], Catalogx()->version, true);
        wp_set_script_translations( 'catalogx-script', 'catalogx' );

        // Localize script
        wp_localize_script( 'catalogx-script', 'appLocalizer', apply_filters( 'catalogx_settings', [
            'apiurl'                    => untrailingslashit( get_rest_url() ),
            'nonce'                     => wp_create_nonce('wp_rest'),
            'tab_name'                 => "CatalogX",
            'restUrl'                  => CatalogX()->rest_namespace,
            'all_pages'                 => $all_pages,
            'role_array'                => $all_roles,
            'all_users'                 => $all_users,
            'all_products'              => $all_products,
            'all_product_cat'           => $product_cat,
            'all_product_tag'           => $product_tags,
            'settings_databases_value'  => $settings_value,
            'active_modules'            => CatalogX()->modules->get_active_modules(),
            'user_role'                 => $current_user_role,
            'banner_img'                => CatalogX()->plugin_url . 'assets/images/catalog-pro-add-admin-banner.jpg',
            'default_img'               => CatalogX()->plugin_url . 'src/assets/images/default.png',
            'template1'                 => CatalogX()->plugin_url . 'assets/images/email/templates/catalogx-email-template-default.png',
            'template2'                 => CatalogX()->plugin_url . 'assets/images/email/templates/catalogx-email-template-1.png',
            'template3'                 => CatalogX()->plugin_url . 'assets/images/email/templates/catalogx-email-template-2.png',
            'template4'                 => CatalogX()->plugin_url . 'assets/images/email/templates/catalogx-email-template-3.png',
            'template5'                 => CatalogX()->plugin_url . 'assets/images/email/templates/catalogx-email-template-4.png',
            'template6'                 => CatalogX()->plugin_url . 'assets/images/email/templates/catalogx-email-template-5.png',
            'template7'                 => CatalogX()->plugin_url . 'assets/images/email/templates/catalogx-email-template-6.png',
            'khali_dabba'                => Utill::is_khali_dabba(),
            'pro_url'                   => esc_url( CATALOGX_PRO_SHOP_URL ),
            'order_edit'                => admin_url( "admin.php?page=wc-orders&action=edit" ),
            'site_url'                  => admin_url( 'admin.php?page=catalogx#&tab=settings&subtab=all-settings' ),
            'module_page_url'           => admin_url( 'admin.php?page=catalogx#&tab=modules' ),
            'settings_page_url'           => admin_url( 'admin.php?page=catalogx#&tab=settings&subtab=all-settings' ),
            'enquiry_form_settings_url'   => admin_url( 'admin.php?page=catalogx#&tab=settings&subtab=enquiry-form-customization' ),
            'customization_settings_url'  => admin_url( 'admin.php?page=catalogx#&tab=settings&subtab=enquiry-catalog-customization' ),
            'wholesale_settings_url'      => admin_url( 'admin.php?page=catalogx#&tab=settings&subtab=wholesale' ),
            'rule_url'                    => admin_url( 'admin.php?page=catalogx#&tab=rules' ),
            'currency'                  => get_woocommerce_currency(),
            'notifima_active'           => Utill::is_active_plugin('notifima'),
            'mvx_active'                => Utill::is_active_plugin('multivendorx'),
            'quote_module_active'       => CatalogX()->modules->is_active('quote'),
            'quote_base_url'            => $quote_base_url
        ]));
    }

    public function textdomain_relative_path($path, $url) {
        if (strpos($url, 'woocommerce-catalog-enquiry') !== false) {   
            foreach (CatalogX()->block_paths as $key => $new_path) {
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
