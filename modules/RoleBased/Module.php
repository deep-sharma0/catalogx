<?php 

namespace Catalogx\RoleBased;

use Catalogx\Utill;

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
     * Summary of __construct
     */
    public function __construct() {
        // Init helper classes
        $this->init_classes();

        if ( Utill::is_khali_dabba() ) {
            new \CatalogxPro\RoleBased\Module();
        }

        add_action('init', [$this, 'main' ]);
    }

    /**
     * Init helper classes
     * @return void
     */
    public function init_classes() {
        $this->container[ 'admin' ]   = new Admin();
	}

    public function main() {
        add_filter('catalog_calculate_discount_in_cart_and_checkout', [$this, 'per_role_discount_amount'], 20);
        add_filter('woocommerce_get_price_html', [$this, 'custom_price_display'], 10, 2);
    }

    function per_role_discount_amount($discount) {
        global $wp_roles;
        $current_user = wp_get_current_user();
        
        // Get the user roles
        $user_roles = $current_user->roles;
        if ( $user_roles[0] == 'wholesale_user' ) {
            return;
        }

        $role = $wp_roles->roles[$user_roles[0]];

        $min_quantity = isset($role['minimum_quantity']) ? $role['minimum_quantity'] : '';
        // Get the total quantity of items in the cart
        $total_quantity = WC()->cart->get_cart_contents_count();
        $total_amount = floatval(WC()->cart->get_cart_contents_total());

        if (!empty($min_quantity)) {
            if ($total_quantity >= $min_quantity) {
                $discount_type = isset($role['discount_type']) ? $role['discount_type'] : '';
                $discount_amount = isset($role['discount_amount']) ? floatval($role['discount_amount']) : 0;
    
                // Calculate the discount based on the type
                if ($discount_type == 'fixed') {
                    if ($total_amount < $discount_amount) {
                        return;
                    }
                    $discount = -$discount_amount;
                } elseif ($discount_type == 'percentage') {
                    $discount = -($total_amount * ($discount_amount / 100));
                } else {
                    return;
                }
    
                return $discount;
            }
        }
        
    }

    function custom_price_display($price, $product) {
        global $wp_roles;
        if (is_product()) {
            // Get the regular price
            $regular_price = $product->get_regular_price();
            $current_user = wp_get_current_user();
            
            // Get the user roles
            $user_roles = $current_user->roles;

            if ($user_roles) {
                if ( $user_roles[0] == 'wholesale_user' ) {
                    return;
                }
    
                $role = $wp_roles->roles[reset($user_roles)];
                
                $settings = array(
                    'min_quantity' => Catalog()->setting->get_setting('minimum_quantity'),
                    'discount_type' => isset($role['discount_type']) ? $role['discount_type'] : '',
                    'discount_amount' => isset($role['discount_amount']) ? floatval($role['discount_amount']) : 0,
                );
        
                // Apply filter to the settings array
                $settings = apply_filters('rolebased_discount_price_settings', $settings);
        
                $discount_type = $settings['discount_type'];
                $discount_amount = $settings['discount_amount'];
                if ($discount_type == 'fixed') {
                    $amount = $discount_amount;
                } elseif ($discount_type == 'percentage') {
                    $amount = $product->get_regular_price() * ($discount_amount / 100);
                } 
                
                $discount_price = floatval($regular_price) - $amount; 

                // if ($regular_price == $discount_price) {
                    // Create the custom price display with strikethrough
                    $price = '<del>' . wc_price($regular_price) . '</del> <ins>' . wc_price($discount_price) . '</ins>';
                // }
            }
        }
        return $price;
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
        return new \WP_Error( sprintf( 'Call to unknown class %s.', $class ) );
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