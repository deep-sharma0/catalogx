<?php

namespace CatalogX\Quote;

/**
 * CatalogX Quote Module class
 *
 * @class 		Module class
 * @version		6.0.0
 * @author 		MultivendorX
 */
class Module {
    /**
     * Container contain all helper class
     * @var array
     */
    private $container = [];

    /**
     * Contain reference of the class
     * @var 
     */
    private static $instance = null;

    /**
     * Catalog class constructor function
     */
    public function __construct() {

        // Init helper classes
        $this->init_classes();

        do_action( 'load_premium_quote_module' );

        if (CatalogX()->modules->is_active('quote')) {
            $this->create_page_for_quote();
        }
    }

   
    /**
     * Init helper classes
     * @return void
     */
    public function init_classes() {
        $this->container[ 'admin' ]     = new Admin();
        $this->container[ 'ajax' ]      = new Ajax();
        $this->container[ 'frontend' ]  = new Frontend();
        $this->container[ 'rest' ]      = new Rest();
        $this->container[ 'util' ]      = new Util();
    }

    /**
     * Create page for quote
     * @return void
     */
    public function create_page_for_quote() {
        // quote page
        $option_value = get_option('catalogx_request_quote_page');
        if ($option_value > 0 && get_post($option_value)) {
            return;
        }

        $page_found = get_posts([
            'name' => 'my-quote',
            'post_status' => 'publish',
            'post_type' => 'page',
            'fields' => 'ids',
            'numberposts' => 1
        ]);
        if ($page_found) {
            if (!$option_value) {
                update_option('catalogx_request_quote_page', $page_found[0]);
            }
            return;
        }
        $page_data = [
            'post_status' => 'publish',
            'post_type' => 'page',
            'post_author' => 1,
            'post_name' => 'my-quote',
            'post_title' => __('My Quote', 'catalogx'),
            'post_content' => $this->request_quote_block() ? $this->request_quote_block() : '[catalogx_request_quote]',
            'comment_status' => 'closed'
        ];
        $page_id = wp_insert_post($page_data);
        update_option('catalogx_request_quote_page', $page_id);
    }

    public function request_quote_block() {
        return '<!-- wp:catalogx/quote-cart -->
                <div id="request-quote-list"></div>
                <!-- /wp:catalogx/quote-cart -->';
    }

    /**
     * Magic getter function to get the reference of class.
     * Accept class name, If valid return reference, else Wp_Error. 
     * @param   mixed $class
     * @return  object | \WP_Error
     */
    public function __get( $class ) {
        if ( array_key_exists( $class, $this->container ) ) {
            return $this->container[ $class ];
        }
        return new \WP_Error( sprintf('Call to unknown class %s.', $class ) );
    }

    /**
     * Magic setter function to store a reference of a class.
     * Accepts a class name as the key and stores the instance in the container.
     *
     * @param string $class The class name or key to store the instance.
     * @param object $value The instance of the class to store.
     */
    public function __set( $class, $value ) {
        $this->container[ $class ] = $value;
    }

    /**
     * Initializes Catalog class.
     * Checks for an existing instance
     * And if it doesn't find one, create it.
     * @param mixed $file
     * @return object | null
     */
    public static function init() {
        if ( self::$instance === null ) {
            self::$instance = new self();
        }

        return self::$instance;
    }
}