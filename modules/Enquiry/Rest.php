<?php 

namespace CatalogX\Enquiry;
use CatalogX\Utill;

/**
 * CatalogX Enquiry Module Rest class
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
        add_action( 'rest_api_init', [ $this, 'register_rest_apis' ] );
    }

    /**
     * Register rest apis
     * @return void
     */
    function register_rest_apis() {
        register_rest_route( CatalogX()->rest_namespace, '/enquiries', [
            'methods'               => 'POST',
            'callback'              => [ $this, 'set_enquiries' ],
            'permission_callback'   => [ $this, 'enquiry_permission' ],
        ]);
    }

    /**
     * Save enquiry form data
     * @param mixed $request
     * @return \WP_Error|\WP_REST_Response
     */
    // quantity string required
    // Retrieve the quantity of product
    // productId string required
    // Retrieve the product id of enquiry
    // bodyparams array required
    // Retrieve the all body parameter from request
    // fileparams array required
    // Retrieve the all file parameter from request
    public function set_enquiries( $request ) {
        global $wpdb;

        $quantity   = $request->get_param( 'quantity' );
        $product_id = $request->get_param( 'productId' );
        $post_params = $request->get_body_params();
        $file_data   = $request->get_file_params();

        $user       = wp_get_current_user();
        $user_name  = $user->display_name;
        $user_email = $user->user_email;
        $attachments = [];

        // Create attachment of files
        foreach ( $file_data as $file ) {
            $attachment_id = \CatalogX\Utill::create_attachment_from_files_array($file);
            if (!empty($attachment_id)) {
                $attachments[] = get_attached_file($attachment_id);
            }
        }

        unset($post_params['quantity'], $post_params['productId']);

        // Gather product information
        $product_info = [];
        if (\CatalogX\Utill::is_khali_dabba()) {
            foreach ((array) CatalogX_Pro()->cart->get_cart_data() as $data) {
                $product_info[$data['product_id']] = $data['quantity'] ?? 1;
            }
        }
        if (empty($product_info)) {
            $product_info[$product_id] = $quantity;
        }

        // Get extra fields
        $other_fields = [];
        foreach ( $post_params as $key => $value ) {
            switch ( $key ) {
                case 'name':
                    $customer_name = !empty($user_name) ? $user_name : $value ;
                    break;

                case 'email':
                    $customer_email = !empty($user_email) ? $user_email : $value;
                    break;
                
                default:
                    $other_fields[] =  [
                        'name' => $key,
                        'value' => $value
                    ];
                    break;
            }
        }

        // Prepare data for insertion
        $data = [
            'product_info'           => serialize( $product_info ),
            'user_id'                => $user->ID,
            'user_name'              => $customer_name ?? $user_name, 
            'user_email'             => $customer_email ?? $user_email, 
            'user_additional_fields' => serialize( $other_fields ),
        ];

        $product_variations = ( get_transient( 'variation_list' ) ) ? get_transient( 'variation_list' ) : [];
        $result = $wpdb->insert("{$wpdb->prefix}" . Utill::TABLES[ 'enquiry' ], $data );

        if ( $result ) {
            $enquiry_id   = $wpdb->insert_id;
            $admin_email  = CatalogX()->admin_email;
            $User_details = get_user_by( 'email', $admin_email );
            $to_user_id   = $User_details->data->ID;
        
            $chat_message = '';
            foreach( $other_fields as $key => $field ) { 
                if ( $field[ 'name' ] != 'file' ) {
                    $chat_message.= '<strong>' . $field[ 'name' ] . ':</strong><br>' . $field[ 'value' ] . '<br>';
                }
            }
    
            $wpdb->query( $wpdb->prepare( "INSERT INTO {$wpdb->prefix}" . Utill::TABLES[ 'message' ] . " SET to_user_id=%d, from_user_id=%d, chat_message=%s, product_id=%s, enquiry_id=%d, status=%s, attachment=%d", $to_user_id, $user->ID, $chat_message, serialize( $product_info ), $enquiry_id, 'unread', $attachment_id ) );

            $enquiry_data = apply_filters( 'catalogx_enquiry_form_data', [
                'enquiry_id'            => $enquiry_id,
                'user_name'             => $customer_name ?? $user_name,
                'user_email'            => $customer_email ?? $user_email,
                'product_id'            => $product_info,
                'variations'            => $product_variations,
                'user_enquiry_fields'   => $other_fields,
            ]);

            $attachments = apply_filters( 'catalogx_set_enquiry_pdf_and_attachments', [], $enquiry_id, $enquiry_data); 
                        
            $additional_email = CatalogX()->setting->get_setting( 'additional_alert_email' );
            $send_email = WC()->mailer()->emails[ 'EnquiryEmail' ];

            $send_email->trigger( $additional_email, $enquiry_data, $attachments );
                
            $redirect_link = CatalogX()->setting->get_setting( 'is_page_redirect' ) && CatalogX()->setting->get_setting( 'redirect_page_id' ) ? get_permalink(CatalogX()->setting->get_setting( 'redirect_page_id' )) : '';
            
            $msg = __( "Enquiry sent successfully", 'catalogx' );
            
            if ( \CatalogX\Utill::is_khali_dabba() ) { 
                CatalogX_Pro()->cart->unset_session(); 
            }

            return rest_ensure_response( [ 'redirect_link' => $redirect_link, 'msg' => $msg ] );
        }

        return rest_ensure_response( null );
    }

    public function enquiry_permission() {
        $user_id = get_current_user_id();
        // For non-logged in user
        if ($user_id == 0 && empty(CatalogX()->setting->get_setting( 'enquiry_user_permission' ))) {
            return true;
        }

        $user = get_userdata($user_id);
    
        // Check if user is admin or customer
        if ($user && array_intersect(['administrator', 'customer'], $user->roles)) {
            return true;
        }
    
        return new \WP_Error('woocommerce_rest_cannot_edit', __('Sorry, you are not allowed to edit this resource.', 'catalogx'), array('status' => rest_authorization_required_code()));
    }

}