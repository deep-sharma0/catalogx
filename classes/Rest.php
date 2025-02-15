<?php

namespace Catalogx;

class Rest {
    /**
     * Rest class constructor function
     */
    public function __construct() {
		add_action( 'rest_api_init', [ $this, 'register_rest_apis' ] );
    }

    /**
     * Register rest api
     * @return void
     */
    function register_rest_apis() {

        register_rest_route( Catalog()->rest_namespace, '/save_enquiry', [
            'methods'               => \WP_REST_Server::ALLMETHODS,
            'callback'              => [ $this, 'save_settings' ],
            'permission_callback'   => [ $this, 'catalog_permission' ]
        ] );

        register_rest_route( Catalog()->rest_namespace, '/module_manage', [
            'methods'               => \WP_REST_Server::ALLMETHODS,
            'callback'              => [ $this, 'manage_module' ],
            'permission_callback'   => [ $this, 'catalog_permission' ]
        ] );

        // setup wizard api
        register_rest_route( Catalog()->rest_namespace, '/module-save', [
            'methods'               => \WP_REST_Server::ALLMETHODS,
            'callback'              => [ $this, 'save_module' ],
            'permission_callback'   => [ $this, 'catalog_permission' ]
        ] );

        register_rest_route( Catalog()->rest_namespace, '/save-settings', [
            'methods'               => \WP_REST_Server::ALLMETHODS,
            'callback'              => [ $this, 'save_settings_wizard' ],
            'permission_callback'   => [ $this, 'catalog_permission' ]
        ] );

        register_rest_route( Catalog()->rest_namespace, '/tour', [
            'methods'               => 'GET',
            'callback'              => [ $this, 'get_tour_status' ],
            'permission_callback'   => [ $this, 'catalog_permission' ],
        ]);
    
        register_rest_route(Catalog()->rest_namespace, '/tour', [
            'methods'               => 'POST',
            'callback'              => [ $this, 'set_tour_status' ],
            'permission_callback'   => [ $this, 'catalog_permission' ],
        ]);

	}

    /**
     * get tour status
     * @return \WP_Error|\WP_REST_Response
     */
    public function get_tour_status() {
        $status = get_option('catalog_tour_active', false);
        return ['active' => $status];
    }
    
    /**
     * set tour status
     * @param mixed $request
     * @return \WP_Error|\WP_REST_Response
     */
    public function set_tour_status($request) {
        update_option('catalog_tour_active', $request->get_param( 'active' ));
        return ['success' => true];
    }

    /**
     * Save global settings
     * @param mixed $request
     * @return \WP_Error|\WP_REST_Response
     */
    public function save_settings( $request ) {
        $all_details        = [];
        $get_settings_data  = $request->get_param( 'setting' );
        $settingsname       = $request->get_param( 'settingName' );
        $settingsname       = str_replace( "-", "_", $settingsname );
        $optionname         = 'catalog_' . $settingsname . '_settings';

        // save the settings in database
        Catalog()->setting->update_option( $optionname, $get_settings_data );

        do_action( 'catalog_settings_after_save', $settingsname, $get_settings_data );

        $all_details[ 'error' ] = __( 'Settings Saved', 'catalogx' );

        return rest_ensure_response($all_details);
	}

    /**
     * Manage module setting. Active or Deactive modules.
     * @param mixed $request
     * @return void
     */
    public function manage_module( $request ) {
        $moduleId   = $request->get_param( 'id' );
        $action     = $request->get_param( 'action' );

        // Handle the actions
        switch ( $action ) {
            case 'activate':
                Catalog()->modules->activate_modules([$moduleId]);
                break;
            
            default:
                Catalog()->modules->deactivate_modules([$moduleId]);
                break;
        }
    }

    /**
     * Manage module from setup wizard.
     * @param mixed $request
     * @return void
     */
    public function save_module( $request ) {
        $modules = $request->get_param('modules');
        foreach ($modules as $module_id) {
            Catalog()->modules->activate_modules([$module_id]);
        }
    }

    /**
     * Manage settings from setup wizard.
     * @param mixed $request
     * @return void
     */
    public function save_settings_wizard( $request ) {
        $action = $request->get_param('action');

        if ($action == 'enquiry') {
            $display_option = $request->get_param('displayOption');
            $restrict_user = $request->get_param('restrictUserEnquiry');
            Catalog()->setting->update_setting('is_disable_popup', $display_option, 'catalog_all_settings_settings');
            Catalog()->setting->update_setting('enquiry_user_permission', $restrict_user, 'catalog_all_settings_settings');
        }
        
        if ($action == 'quote') {
            $restrict_user = $request->get_param('restrictUserQuote');
            Catalog()->setting->update_setting('quote_user_permission', $restrict_user, 'catalog_all_settings_settings');
        }

        if ($action == 'wholesale') {
            $type = $request->get_param('wholesaleType');
            $amount = $request->get_param('wholesaleAmount');
            $quantity = $request->get_param('minimumQuantity');

            $wholesale_discount = array(
                'wholesale_discount_type' => $type,
                'wholesale_amount' => floatval($amount),
                'minimum_quantity' => intval($quantity),
            );
            Catalog()->setting->update_setting('wholesale_discount', $wholesale_discount, 'catalog_wholesale_settings');
        }
    }

    /**
     * Catalog rest api permission functions
     * @return bool
     */
	public function catalog_permission() {
		// return current_user_can('manage_options');
        return true;
	}
}