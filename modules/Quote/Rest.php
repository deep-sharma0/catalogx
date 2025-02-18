<?php

namespace CatalogX\Quote;

class Rest {
    /**
     * Rest class constructor functions
     */
    public function __construct() {
        add_action( 'rest_api_init', [ $this, 'register_rest_api' ] );
    }

    /**
     * Regsiter rest api
     * @return void
     */
    public function register_rest_api() {
        register_rest_route( CatalogX()->rest_namespace, '/get-all-quote', [
            'callback'              => [ $this, 'get_all_quote' ],
            'methods'               => \WP_REST_Server::ALLMETHODS,
            'permission_callback'   => [ CatalogX()->restapi, 'catalog_permission' ]
        ] );

        register_rest_route( CatalogX()->rest_namespace, '/quote-update-cart', [
            'methods'               => \WP_REST_Server::ALLMETHODS,
            'callback'              => [ $this, 'quote_update_cart' ],
            'permission_callback'   => [ CatalogX()->restapi, 'catalog_permission' ]
        ] );

        register_rest_route( CatalogX()->rest_namespace, '/quote-remove-cart', [
            'methods'               => \WP_REST_Server::ALLMETHODS,
            'callback'              => [ $this, 'quote_remove_cart' ],
            'permission_callback'   => [ CatalogX()->restapi, 'catalog_permission' ]
        ] );

        register_rest_route( CatalogX()->rest_namespace, '/quote-send', [
            'methods'               => \WP_REST_Server::ALLMETHODS,
            'callback'              => [ $this, 'quote_send' ],
            'permission_callback'   => [ CatalogX()->restapi, 'catalog_permission' ]
        ] );

        register_rest_route( CatalogX()->rest_namespace, '/reject-quote-my-acount', [
            'methods'               => \WP_REST_Server::ALLMETHODS,
            'callback'              => [ $this, 'reject_quote_my_account' ],
            'permission_callback'   => [ CatalogX()->restapi, 'catalog_permission' ]
        ] );

    }

    /**
     * Get all the quote in cart
     * @param mixed $request
     * @return \WP_Error|\WP_REST_Response
     */
    public function get_all_quote( $request ) {
        $row =  $request['row'];
        $page = $request['page'];
    
        // Get all cart data
        $all_cart_data = CatalogX()->quotecart->get_cart_data();
    
        // Calculate pagination
        $total_items = count( $all_cart_data );
        $offset = ( $page - 1 ) * $row;
    
        // Slice data for current page
        $paginated_cart_data = array_slice( $all_cart_data, $offset, $row );
    
        // Prepare the quote list
        $quote_list = [];
        foreach ( $paginated_cart_data as $key => $item ) {
            $product = wc_get_product( $item['product_id'] );
            $thumbnail = $product->get_image( apply_filters( 'woocommerce_catalog_enquiry_cart_item_thumbnail_size', [84, 84] ) );
            $name = '';
            if ( $item['variation'] ) {
                foreach ( $item['variation'] as $label => $value ) {
                    $label = str_replace( ['attribute_pa_', 'attribute_'], '', $label );
                    $name .= "<br>" . ucfirst( $label ) . ": " . ucfirst( $value );
                }
            }
    
            $product_price = $product->get_price();
            $quantity = isset( $item['quantity'] ) ? $item['quantity'] : 1;
            $subtotal = $product_price * $quantity;
    
            $quote_list[] = apply_filters( 'catalog_quote_list_data', [
                'key'      => $key,
                'id'       => $product->get_id(),
                'image'    => $thumbnail,
                'name'     => $product->get_name() . ( $name ? $name : '' ),
                'quantity' => $item['quantity'],
                'total'    => wc_price( $subtotal ),
            ], $product );
        }
        
        return rest_ensure_response( ['count' => $total_items, 'response'=> $quote_list] );
    }
    

    /**
     * update quote in cart
     * @param mixed $request
     * @return \WP_Error|\WP_REST_Response
     */
    public function quote_update_cart( $request ) {
        $products = $request->get_param('products');

        foreach ($products as $key => $product) {
            $product_id = $product['id'];
            $quantity = $product['quantity'];
            CatalogX()->quotecart->update_cart( $product['key'], 'quantity', $quantity );
            $update_msg =  __( 'Quote cart updated!', 'catalogx');
        }

        return rest_ensure_response(['msg' => $update_msg]);

    }

    /**
     * remove quote in cart
     * @param mixed $request
     * @return \WP_Error|\WP_REST_Response
     */
    public function quote_remove_cart( $request ) {
        $product_id = $request->get_param('productId');
        $key = $request->get_param('key');
        $status = false;
        if ( $product_id && isset( $key ) ) {
            $status = CatalogX()->quotecart->remove_cart( $key );
        }
        return rest_ensure_response(['status' => $status, 'cart_data' => CatalogX()->quotecart->get_cart_data()]);
    }

    /**
     * send quote from cart and create order
     * @param mixed $request
     * @return \WP_Error|\WP_REST_Response
     */
    public function quote_send( $request ) {
        $form_data = $request->get_param('formData');
        
        // Sanitize form data
        $customer_name = sanitize_text_field($form_data['name']);
        $customer_email = sanitize_email($form_data['email']);
        $customer_phone = sanitize_text_field($form_data['phone']);
        $customer_message = sanitize_textarea_field($form_data['message']);
        $product_data = CatalogX()->quotecart->get_cart_data();
        
        
        // Create a new customer or retrieve existing customer based on email
        $customer_id = Util::get_customer_id_by_email($customer_email);
        
        // Create a new order
        $order_id = Util::create_new_order($customer_id, $customer_name, $customer_email, $customer_phone, $customer_message, $product_data);
        
        if ($order_id) {
            $redirect_url = add_query_arg(['order_id' => $order_id], get_permalink(CatalogX()->setting->get_option('woocommerce_myaccount_page_id')) . 'request-quote-thank-you/');
            return rest_ensure_response( ['order_id' => $order_id, 'redirect_url' => $redirect_url ]);
        } 
    }

    /**
     * reject quote from my-account page
     * @param mixed $request
     * @return \WP_Error|\WP_REST_Response
     */    
    public function reject_quote_my_acount($request) {

        $order_id =  $request->get_param('orderId');
        $status =  $request->get_param('status');
        $reason =  $request->get_param('reason');
        if (!empty($order_id) && !empty($status) && !empty($reason)) {
            $order = wc_get_order($order_id);
            $order->update_status('wc-quote-rejected');
            $order->set_customer_note($reason);
            $order->save();
            /* translators: %s: reject quotation number. */
            return rest_ensure_response(['message' => sprintf( __( 'You have confirmed rejection of the quotation No: %d', 'catalogx' ) , $order_id )]);
        }
    }
}