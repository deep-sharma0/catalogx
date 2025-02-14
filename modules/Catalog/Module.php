<?php 

namespace CatalogEnquiry\Catalog;

class Module{
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
    }

    /**
     * Init helper classes
     * @return void
     */
    public function init_classes() {
		$this->container[ 'util' ]      = new Util();
		$this->container[ 'fontend' ]   = new Frontend();
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
