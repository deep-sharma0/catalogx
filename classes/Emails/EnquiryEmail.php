<?php

namespace CatalogX\Emails;
use CatalogX\Utill;

/**
 * Email to Admin for customer enquiry.
 * An email will be sent to the admin when a customer enquires about a product.
 *
 * @class EnquiryEmail class
 * @version 6.0.0
 * @author MultiVendorX
 * @extends \WC_Email
 */
class EnquiryEmail extends \WC_Email {

    public $product_id;
    public $attachments;
    public $enquiry_data;
    public $cust_name;
    public $cust_email;

    /**
     * Constructor
     */
    public function __construct() {		
        $this->id          = 'catalogx_enquiry_sent';
        $this->title       = __( 'Enquiry sent', 'catalogx' );
        $this->description = __( 'Admin will get an email when a customer enquires about a product.', 'catalogx' );

        $this->initialize_templates();

        // Call parent constructor
        parent::__construct();
    }

    protected function initialize_templates() {
        // Determine the base template path based on Pro status and email setting
        $is_khali_dabba = Utill::is_khali_dabba();
        $email_setting = $is_khali_dabba ? CatalogX()->setting->get_setting('selected_email_tpl') : '';
    
        // Use Pro template path if Pro is active and email setting is not empty, otherwise fallback to default path
        $base_template_path = ($is_khali_dabba && !empty($email_setting)) 
            ? CatalogX_Pro()->plugin_path . 'templates/' 
            : CatalogX()->plugin_path . 'templates/';
    
        // Define available email templates
        $template_map = [
            'template1' => 'emails/default-enquiry-template.php',
            'template2' => 'emails/enquiry-template1.php',
            'template3' => 'emails/enquiry-template2.php',
            'template4' => 'emails/enquiry-template3.php',
            'template5' => 'emails/enquiry-template4.php',
            'template6' => 'emails/enquiry-template5.php',
            'template7' => 'emails/enquiry-template6.php',
        ];
    
        // Set the appropriate template paths
        $this->template_html  = $template_map[$email_setting] ?? 'emails/enquiry-email.php';
        $this->template_plain = $is_khali_dabba ? 'emails/plain/enquiry-email-plain.php' : 'emails/plain/enquiry-email.php';
        $this->template_base  = $base_template_path;
    }
    

    /**
     * Trigger the email.
     */
    public function trigger($recipient, $enquiry_data, $attachments) {
        $this->recipient    = $recipient;
        $this->attachments  = $attachments;
        $this->product_id   = $enquiry_data['product_id'];
        $this->enquiry_data = $enquiry_data;
        $this->cust_name    = $enquiry_data['user_name'];
        $this->cust_email   = $enquiry_data['user_email'];
        $this->customer_email = $this->cust_email;

        if (!$this->is_enabled() || !$this->get_recipient()) {
            return false;
        }

        $this->add_vendor_emails();

        $product = wc_get_product(key($this->product_id));
        $this->find = ['{PRODUCT_NAME}', '{USER_NAME}'];
        $this->replace = [
            is_array($this->product_id) && count($this->product_id) > 1 ? 'MULTIPLE PRODUCTS' : $product->get_title(),
            $this->cust_name
        ];

        return $this->send($this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments());
    }

    /**
     * Add vendor emails to the recipient list.
     */
    protected function add_vendor_emails() {
        if (!Utill::is_active_plugin('multivendorx')) return;

        foreach ($this->product_id as $product_id => $quantity) {
            $vendor = function_exists('get_mvx_product_vendors') ? get_mvx_product_vendors($product_id) : null;

            if ($vendor) {
                $vendor_email = sanitize_email($vendor->user_data->user_email);
                $this->recipient .= ', ' . $vendor_email;

                if (strpos($this->recipient, $vendor_email) !== false) {
                    $email_setting = get_user_meta($vendor->id, 'vendor_enquiry_settings', true)['selected_email_tpl'] ?? '';
                    $this->template_html = $this->get_vendor_template($email_setting);
                }
            }
        }
    }

    /**
     * Get the appropriate template for the vendor.
     */
    protected function get_vendor_template($email_setting) {
        $template_map = [
            'template1' => 'emails/default-enquiry-template.php',
            'template2' => 'emails/enquiry-template1.php',
            'template3' => 'emails/enquiry-template2.php',
            'template4' => 'emails/enquiry-template3.php',
            'template5' => 'emails/enquiry-template4.php',
            'template6' => 'emails/enquiry-template5.php',
            'template7' => 'emails/enquiry-template6.php'
        ];

        return $template_map[$email_setting] ?? 'emails/enquiry-email.php';
    }

    /**
     * Get email subject.
     */
    public function get_default_subject() {
        return apply_filters('catalogx_enquiry_admin_email_subject', __('Product Enquiry for {PRODUCT_NAME} by {USER_NAME}', 'catalogx'), $this->object);
    }

    /**
     * Get email heading.
     */
    public function get_default_heading() {
        return apply_filters('catalogx_enquiry_admin_email_heading', __('Enquiry for {PRODUCT_NAME}', 'catalogx'), $this->object);
    }

    /**
     * Get email attachments.
     */
    public function get_attachments() {
        return apply_filters('catalogx_enquiry_admin_email_attachments', $this->attachments, $this->id, $this->object);
    }

    /**
     * Get email headers.
     */
    public function get_headers() {
        $header = "Content-Type: " . $this->get_content_type() . "\r\n";
        $header .= 'Reply-to: ' . $this->cust_name . ' <' . $this->cust_email . ">\r\n";
        return apply_filters('catalogx_enquiry_admin_email_headers', $header, $this->id, $this->object);
    }

    /**
     * Get HTML content.
     */
    public function get_content_html() {
        ob_start();
        wc_get_template($this->template_html, $this->get_template_args(), '', $this->template_base);
        return ob_get_clean();
    }

    /**
     * Get plain content.
     */
    public function get_content_plain() {
        ob_start();
        wc_get_template($this->template_plain, $this->get_template_args(), '', $this->template_base);
        return ob_get_clean();
    }

    /**
     * Get template arguments.
     */
    protected function get_template_args() {
        return [
            'email_heading'   => $this->get_heading(),
            'product_id'      => $this->product_id,
            'enquiry_data'    => $this->enquiry_data,
            'customer_email'  => $this->customer_email,
            'sent_to_admin'   => true,
            'plain_text'      => false
        ];
    }
}
