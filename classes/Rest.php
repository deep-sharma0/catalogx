<?php

namespace CatalogX;
use CatalogX\Enquiry\Module as EnquiryModule;
use CatalogX\Quote\Module as QuoteModule;

/**
 * CatalogX Rest class
 *
 * @class 		Rest class
 * @version		6.0.0
 * @author 		MultivendorX
 */
class Rest {
    /**
     * Rest class constructor function
     */
    public function __construct() {
        if ( current_user_can( 'manage_options' ) ) {
            add_action( 'rest_api_init', [ $this, 'register_rest_apis' ] );
        }
    }

    /**
     * Register rest api
     * @return void
     */
    function register_rest_apis() {

        register_rest_route( CatalogX()->rest_namespace, '/settings', [
            'methods'               => 'POST',
            'callback'              => [ $this, 'set_settings' ],
            'permission_callback'   => [ $this, 'catalogx_permission' ]
        ] );

        // enable/disable the module
        register_rest_route( CatalogX()->rest_namespace, '/modules', [
            'methods'               => 'POST',
            'callback'              => [ $this, 'set_modules' ],
            'permission_callback'   => [ $this, 'catalogx_permission' ]
        ] );

        register_rest_route( CatalogX()->rest_namespace, '/tour', [
            [
                'methods'               => 'GET',
                'callback'              => [ $this, 'get_tour' ],
                'permission_callback'   => [ $this, 'catalogx_permission' ],
            ],
            [
                'methods'               => 'POST',
                'callback'              => [ $this, 'set_tour' ],
                'permission_callback'   => [ $this, 'catalogx_permission' ],
            ]
        ] );

        register_rest_route( CatalogX()->rest_namespace, '/buttons', [
            'methods'               => 'GET',
            'callback'              => [ $this, 'get_buttons' ],
            'permission_callback'   => [ $this, 'catalogx_permission' ],
        ]);

    }

    /**
     * get tour status
     * @return \WP_Error|\WP_REST_Response
     */
    public function get_tour() {
        $status = CatalogX()->setting->get_option('catalogx_tour_active', false);
        return ['active' => $status];
    }
    
    /**
     * set tour status
     * @param mixed $request
     * @return \WP_Error|\WP_REST_Response
     */
    // active boolean required
    // catalogx tour active or not
    public function set_tour($request) {
        update_option('catalogx_tour_active', $request->get_param( 'active' ));
        return ['success' => true];
    }

    /**
     * Save global settings
     * @param mixed $request
     * @return \WP_Error|\WP_REST_Response
     */
    // setting array required
    // all the settings of a particular id 
    // settingName string required
    // Give the setting id 
    public function set_settings( $request ) {
        $all_details        = [];
        $get_settings_data  = $request->get_param( 'setting' );
        $settingsname       = $request->get_param( 'settingName' );
        $optionname         = 'catalogx_' . $settingsname . '_settings';

        // save the settings in database
        CatalogX()->setting->update_option( $optionname, $get_settings_data );

        do_action( 'catalogx_settings_after_save', $settingsname, $get_settings_data );

        $all_details[ 'error' ] = __( 'Settings Saved', 'catalogx' );

        //setup wizard settings
        $action = $request->get_param('action');

        if ($action == 'enquiry') {
            $display_option = $request->get_param('displayOption');
            $restrict_user = $request->get_param('restrictUserEnquiry');
            CatalogX()->setting->update_setting('is_disable_popup', $display_option, 'catalogx_all-settings_settings');
            CatalogX()->setting->update_setting('enquiry_user_permission', $restrict_user, 'catalogx_all-settings_settings');
        }
        
        if ($action == 'quote') {
            $restrict_user = $request->get_param('restrictUserQuote');
            CatalogX()->setting->update_setting('quote_user_permission', $restrict_user, 'catalogx_all-settings_settings');
        }

        return rest_ensure_response($all_details);
    }

    /**
     * Manage module setting. Active or Deactive modules.
     * @param mixed $request
     * @return void
     */
    // id string required
    // Give the module id 
    // action string required
    // Give the action that is activate or deactivate
    public function set_modules( $request ) {
        $moduleId   = $request->get_param( 'id' );
        $action     = $request->get_param( 'action' );

        // Setup wizard module
        $modules = $request->get_param('modules');
        foreach ($modules as $module_id) {
            CatalogX()->modules->activate_modules([$module_id]);
        }
        // Handle the actions
        switch ( $action ) {
            case 'activate':
                CatalogX()->modules->activate_modules([$moduleId]);
                break;
            
            default:
                CatalogX()->modules->deactivate_modules([$moduleId]);
                break;
        }
    }

    public function get_buttons($request) {
        $product_id = $request->get_param('product_id');
        $button_type = $request->get_param('button_type');

        // Start output buffering
        ob_start();

        if ($button_type == 'enquiry') {
            EnquiryModule::init()->frontend->add_enquiry_button(intval($product_id));
        }

        if ($button_type == 'quote') {
            QuoteModule::init()->frontend->add_button_for_quote(intval($product_id));
        }

        do_action('catalogx_get_buttons', $button_type, $product_id);

        // Return the output
        return rest_ensure_response(['html' => ob_get_clean()]);
    }

    /**
     * Catalog rest api permission functions
     * @return bool
     */
    public function catalogx_permission() {
        return true;
    }

}