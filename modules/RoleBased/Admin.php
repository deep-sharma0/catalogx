<?php 

namespace Catalogx\RoleBased;

class Admin {
    /**
     * Admin class constructor functions
     */
    public function __construct() {
        add_action( 'rest_api_init', [ $this, 'register_rest_api' ] );
    }

    /**
     * Regsiter rest api
     * @return void
     */
    public function register_rest_api() {
        register_rest_route( Catalog()->rest_namespace, '/get-roles', [
            'methods'               => \WP_REST_Server::ALLMETHODS,
            'callback'              => [ $this, 'get_roles' ],
            'permission_callback'   => [ Catalog()->restapi, 'catalog_permission' ]
        ] );

        register_rest_route( Catalog()->rest_namespace, '/add-role', [
            'methods'               => \WP_REST_Server::ALLMETHODS,
            'callback'              => [ $this, 'add_role' ],
            'permission_callback'   => [ Catalog()->restapi, 'catalog_permission' ]
        ] );

        register_rest_route( Catalog()->rest_namespace, '/edit-role', [
            'methods'               => \WP_REST_Server::ALLMETHODS,
            'callback'              => [ $this, 'edit_role' ],
            'permission_callback'   => [ Catalog()->restapi, 'catalog_permission' ]
        ] );
    }
    
    /**
     * Get all roles for role table
     * @param mixed $request
     * @return \WP_Error|\WP_REST_Response
     */
    public function get_roles( $request ) {
        $role_list  = [];
        $counts     = $request->get_param( 'counts' );
        $limit      = $request['row'];
        $offset     = ( $request['page'] - 1 ) * $limit;
        
        $roles = wp_roles()->roles;

        // Specify the role to exclude
        $role_to_exclude = 'wholesale_user';

        // Exclude the role
        if (isset($roles[$role_to_exclude])) {
            unset($roles[$role_to_exclude]);
        }

        // Prepare roles for table view
        foreach ( $roles as $key => $role ) {
            $role_list[] = apply_filters( 'catalog_roles_list_data', [
                'id'                => $key,
                'role_name'         => $role[ 'name' ],
                'discount_type'     => $role[ 'discount_type' ] ?? null,
                'discount_amount'   => $role[ 'discount_amount' ] ?? null,
                'minimum_quantity'  => $role[ 'minimum_quantity' ] ?? null,
            ], $key, $role );
        }

        if ( $counts ) {
			return rest_ensure_response( count( $role_list ) );
		}

        return rest_ensure_response ( $role_list );
    }

    public function add_role( $request ) {
        $name             = $request->get_param( 'name' );
        $inherit_role     = $request->get_param( 'inheritRole' );
        $all_capabilties  = get_role( $inherit_role )->capabilities;
        
        if ( null === get_role( $name ) ) {
            add_role(
                $name,
                __( $name ),
                $all_capabilties
            );

            return rest_ensure_response( [ 'msg' => __( 'Add role successfully', 'catalogx' ) ] );
        }
    }

    public function edit_role( $request ) {
        $id     = $request->get_param( 'id' );
        $key    = $request->get_param( 'key' );
        $value  = $request->get_param( 'value' );

        $role_key = wp_roles()->role_key;
        $roles    = Catalog()->setting->get_option( $role_key );
        
        if ( isset( $roles[ $id ] ) ) {
            $roles[ $id ][ $key ] = $value;
            update_option( $role_key, $roles );
        }
    }
}