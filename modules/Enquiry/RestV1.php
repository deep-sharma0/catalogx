<?php 

namespace CatalogX\Enquiry;
use CatalogX\Utill;
use CatalogX\Enquiry\Module;

class RestV1 {
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
            'callback'              => [ $this, 'save_form_data' ],
            'permission_callback'   => [ CatalogX()->restapi, 'catalogx_permission' ],
        ]);

        register_rest_route( CatalogX()->rest_namespace, '/buttons', [
            'methods'               => \WP_REST_Server::ALLMETHODS,
            'callback'              => [ $this, 'render_buttons' ],
            'permission_callback'   => [ CatalogX()->restapi, 'catalogx_permission' ],
        ]);
	}

    /**
     * Save enquiry form data
     * @param mixed $request
     * @return \WP_Error|\WP_REST_Response
     */
    public function save_form_data( $request ) {
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

            if (Utill::is_khali_dabba()) {
                $html = \CatalogXPro\Enquiry\Util::get_html($enquiry_data);
                if ($html) { 
                    $pdf_maker = new \CatalogXPro\PDFMaker($html);
                    $pdf = $pdf_maker->output();

                    // Save the PDF to a temporary location
                    $upload_dir = wp_upload_dir();
                    $file_path = $upload_dir['basedir'] . '/enquiry-' . $enquiry_id . '.pdf';
            
                    file_put_contents($file_path, $pdf);
        
                    $pdf_output = '';
                    $pdf_maker->get_pdf_headers($file_path, $pdf_output, $pdf);
                    // echo $pdf;
                } else {
                    wp_die(__("PDF document could not be generated", 'catalogx-pro'));
                }
                $attach_pdf = CatalogX()->setting->get_setting( 'enquiry_pdf_permission' );
                if (is_array($attach_pdf) && in_array('attach_pdf_to_email', $attach_pdf, true)) {
                    $attachments[] = $file_path; // Add PDF to attachments
                }
            }
                        
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

    /**
     * render enquiry button shortcode into block
     * @return \WP_Error|\WP_REST_Response
     */
    public function render_buttons($request) {
        $product_id = $request->get_param('product_id');

        // Start output buffering
        ob_start();

        Module::init()->frontend->add_enquiry_button(intval($product_id));

        // Return the output
        return rest_ensure_response(['html' => ob_get_clean()]);
    }

}