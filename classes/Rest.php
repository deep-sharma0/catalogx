<?php

namespace CatalogX;

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

        register_rest_route( CatalogX()->rest_namespace, '/settings', [
            'methods'               => \WP_REST_Server::ALLMETHODS,
            'callback'              => [ $this, 'save_settings' ],
            'permission_callback'   => [ $this, 'catalogx_permission' ]
        ] );

        // enable/disable the module
        register_rest_route( CatalogX()->rest_namespace, '/modules', [
            'methods'               => \WP_REST_Server::ALLMETHODS,
            'callback'              => [ $this, 'manage_module' ],
            'permission_callback'   => [ $this, 'catalogx_permission' ]
        ] );

        register_rest_route( CatalogX()->rest_namespace, '/tour', [
            'methods'               => 'GET',
            'callback'              => [ $this, 'get_tour_status' ],
            'permission_callback'   => [ $this, 'catalogx_permission' ],
        ]);
    
        register_rest_route(CatalogX()->rest_namespace, '/tour', [
            'methods'               => 'POST',
            'callback'              => [ $this, 'set_tour_status' ],
            'permission_callback'   => [ $this, 'catalogx_permission' ],
        ]);

	}

    /**
     * get tour status
     * @return \WP_Error|\WP_REST_Response
     */
    public function get_tour_status() {
        $status = CatalogX()->setting->get_option('catalogx_tour_active', false);
        return ['active' => $status];
    }
    
    /**
     * set tour status
     * @param mixed $request
     * @return \WP_Error|\WP_REST_Response
     */
    public function set_tour_status($request) {
        update_option('catalogx_tour_active', $request->get_param( 'active' ));
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
        // $settingsname       = str_replace( "-", "_", $settingsname );
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
    public function manage_module( $request ) {
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

    /**
     * Catalog rest api permission functions
     * @return bool
     */
	public function catalogx_permission() {
		return true;
	}
}