<?php

namespace CatalogX;
use \Automattic\WooCommerce\Utilities\FeaturesUtil;

/**
 * CatalogX class main function
 *
 * @class 		CatalogX class
 * @version		6.0.0
 * @author 		MultivendorX
 */
final class CatalogX {

    private static $instance = null;
    private $file            = '';
    private $container       = [];
    
    /**
     * CatalogX class constructor function
     * @param mixed $file plugin's main file
     */
    public function __construct($file) {
        require_once trailingslashit( dirname( $file ) ) . '/config.php';

        $this->file = $file;
        $this->container[ 'plugin_url' ]     = trailingslashit( plugins_url( '', $plugin = $file ) );
        $this->container[ 'plugin_path' ]    = trailingslashit( dirname( $file ) );
        $this->container[ 'version' ]        = CATALOGX_PLUGIN_VERSION;
        $this->container[ 'rest_namespace' ] = 'catalogx/v1';
        $this->container[ 'block_paths' ]    = [];
        $this->container[ 'admin_email' ]    = get_option( 'admin_email' );

        register_activation_hook( $file, [ $this, 'activate' ] );
        register_deactivation_hook( $file, [ $this, 'deactivate' ] );

        add_action( 'before_woocommerce_init', [ $this, 'declare_compatibility' ] );
        add_action( 'woocommerce_loaded', [ $this, 'init_plugin' ] );
        add_action( 'plugins_loaded', [ $this, 'is_woocommerce_loaded'] );
        add_filter( 'woocommerce_email_classes', [ $this, 'load_emails' ] );

    }

    /**
     * Add High Performance Order Storage Support
     * @return void
     */
    public function declare_compatibility() {
        FeaturesUtil::declare_compatibility ( 'custom_order_tables', WP_CONTENT_DIR.'/plugins/woocommerce-catalog-enquiry/Woocommerce_Catalog_Enquiry.php', true );
    }
    
    public function init_plugin() {

        $this->load_plugin_textdomain();
        
        if (is_admin() && !defined('DOING_AJAX')) {
            add_filter('plugin_action_links_' . plugin_basename($this->file), [ $this, 'plugin_link' ] );
            add_filter( 'plugin_row_meta', [ $this, 'plugin_row_meta'], 10, 2 );
        }

        $this->init_classes();
        
        add_action( 'init', [ $this, 'catalogx_register_strings_and_setup_wizard' ] );
        
        do_action( 'catalogx_loaded' );

    }
    
    /**
     * Load setup class and register string
     */
    public function catalogx_register_strings_and_setup_wizard() {
        new SetupWizard();
        if (get_option('catalogx_plugin_activated')) {
            delete_option('catalogx_plugin_activated');
            wp_redirect(admin_url('admin.php?page=catalogx-setup'));
            exit;
        }

        if ( ! function_exists( 'icl_register_string' ) ) {
            return;
        }
        
        $strings = [
            'add_to_quote' => 'Add to Quote',
            'view_quote' => 'View Quote',
            'send_an_enquiry' => 'Send an enquiry',
        ];
        
        foreach ( $strings as $key => $value ) {
            icl_register_string( 'catalogx', $key, $value );
        }

        $form_settings =  CatalogX()->setting->get_option('catalogx_enquiry-form-customization_settings');

        if ( function_exists( 'icl_register_string' ) ) {
            foreach ( $form_settings['formsettings']['formfieldlist'] as $field ) {
                if ( isset( $field['label'] ) ) {
                    icl_register_string( 'catalogx', 'form_field_label_' . $field['id'], $field['label'] );
                }
                if ( isset( $field['placeholder'] ) ) {
                    icl_register_string( 'catalogx', 'form_field_placeholder_' . $field['id'], $field['placeholder'] );
                }
                if ( isset( $field['options'] ) ) {
                    foreach ( $field['options'] as $option ) {
                        icl_register_string( 'catalogx', 'form_field_option_' . $field['id'] . '_' . $option['value'], $option['label'] );
                    }
                }
            }
    
            foreach ( $form_settings['freefromsetting'] as $free_field ) {
                if ( isset( $free_field['label'] ) ) {
                    icl_register_string( 'catalogx', 'free_form_label_' . $free_field['key'], $free_field['label'] );
                }
            }
        }

        // Save the form settings to the options table
        update_option( 'catalogx_enquiry-form-customization_settings', $form_settings );
    }

    //load all classes
    public function init_classes() {
        $this->container['setting']  	= new Setting();
        $this->container['admin']    	= new Admin();
        $this->container['frontend'] 	= new Frontend();
        $this->container['restapi']	 	= new Rest();
        $this->container['util']     	= new Utill();
        $this->container['modules']	 	= new Modules();
        $this->container['shortcode']	= new Shortcode();
        $this->container['session'] 	= new Core\Session();
        $this->container['quotecart']	= new Core\QuoteCart();

        // Load all active modules
        $this->container['modules']->load_active_modules();

        $this->container['block'] 		= new Block();
    }

    public function plugin_link( $links ) {	
        $plugin_links = [
            '<a href="' . admin_url( 'admin.php?page=catalogx#&tab=settings&subtab=general' ) . '">' . __( 'Settings', 'catalogx' ) . '</a>',
            '<a href="https://catalogx.com/support/?utm_source=wpadmin&utm_medium=pluginsettings&utm_campaign=catalogx">' . __( 'Support', 'catalogx' ) . '</a>',			
        ];	
        $links = array_merge( $plugin_links, $links );
        if ( apply_filters( 'catalogx_free_active', true ) ) {
            $links[] = '<a href="' . esc_url( CATALOGX_PRO_SHOP_URL ) . '" target="_blank">' . __( 'Upgrade to Pro', 'catalogx' ) . '</a>';
        }
        return $links;
    }

    public function plugin_row_meta( $links, $file ) {
        if ( $file == 'woocommerce-catalog-enquiry/Woocommerce_Catalog_Enquiry.php' && apply_filters( 'catalogx_free_active', true ) ) {
            $row_meta = [
                'pro'    => '<a href="' . esc_url( CATALOGX_PRO_SHOP_URL ) . '" title="' . esc_attr( __( 'Upgrade to Pro', 'catalogx' ) ) . '">' . __( 'Upgrade to Pro', 'catalogx' ) . '</a>'
            ];
            return array_merge( $links, $row_meta );
        } else {
            return $links;
        }
    }

    /**
     * Take action based on if woocommerce is not loaded
     * @return void
     */
    public function is_woocommerce_loaded() {
        if ( ! did_action( 'woocommerce_loaded' ) && is_admin() ) {
            add_action( 'admin_notices', [ $this , 'woocommerce_admin_notice' ] );
        }
    }

    /**
     * Display Woocommerce inactive notice.
     * @return void
     */
    public function woocommerce_admin_notice() {
        ?>
        <div class="error">
            <p><?php printf( __( '%sCatalogX is inactive.%s The %sWooCommerce plugin%s must be active for the CatalogX to work. Please %sinstall & activate WooCommerce%s', 'catalogx' ), '<strong>', '</strong>', '<a target="_blank" href="http://wordpress.org/extend/plugins/woocommerce/">', '</a>', '<a href="' . admin_url( 'plugins.php' ) . '">', '&nbsp;&raquo;</a>' ); ?></p>
        </div>
        <?php
    }


      /**
     * CatalogX emails
     * @param array $emails
     * @return array
     */
    public function load_emails( $emails ) {
        $emails[ 'EnquiryEmail' ] = new Emails\EnquiryEmail();
        $emails[ 'requestQuoteSendEmail' ]  = new Emails\RequestQuoteSendEmail();
        return $emails;
    }

    /**
     *Catalog enquery activation function.
     * @return void
     */
    public function activate() {
        ob_start();
        $this->container['install'] = new Install();

        if (!get_option('catalogx_plugin_installed')) {
            add_option('catalogx_plugin_installed', true);
            add_option('catalogx_plugin_activated', true);
        }

        if (!get_option('catalogx_tour_active')) {
            add_option('catalogx_tour_active', true);
            add_option('catalogx_tour_version', CatalogX()->version);
        }
        flush_rewrite_rules();
        ob_end_clean();
    }

    /**
     *Catalog enquery deactivation function.
     * @return void
     */
    public function deactivate() {
        // Nothing to do write now
    }

    /**
     * Load Localisation files.
     * Note: the first-loaded translation file overrides any following ones if the same translation is present
     * @return void
     */
    public function load_plugin_textdomain() {
        if ( version_compare( $GLOBALS['wp_version'], '6.7', '<' ) ) {
            load_plugin_textdomain('catalogx', false, plugin_basename(dirname(dirname(__FILE__))) . '/languages');
        } else {
            load_textdomain( 'catalogx', WP_LANG_DIR . '/plugins/catalogx-' . determine_locale() . '.mo' );
        }
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
     * Initializes the catalog enquiry class.
     * Checks for an existing instance
     * And if it doesn't find one, create it.
     * @param mixed $file
     * @return object | null
     */
    public static function init( $file ) {
        if ( self::$instance === null ) {
            self::$instance = new self( $file );
        }

        return self::$instance;
    }
}
