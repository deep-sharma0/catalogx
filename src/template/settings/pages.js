import { __ } from '@wordpress/i18n';

export default {
    id: 'pages',
    priority: 80,
    name: __("Page Endpoint", "catalogx"),
    desc: __("Manage the endpoints for all pages on the site, ensuring proper routing and access.", "catalogx"),
    icon: 'adminLib-book',
    submitUrl: 'save_enquiry',
    modal : [
        {
            key: 'set_enquiry_cart_page',
            type: 'select',
            label: __("Set Enquiry Cart Page", "catalogx"),
            desc: __("Select the page on which you have inserted <code>[catalog_enquiry_cart]</code> shortcode.", "catalogx"),
            options: appLocalizer.all_pages,
            proSetting: true,
        },
        {
            key: 'set_request_quote_page',
            type: 'select',
            label: __("Set Request Quote Page", "catalogx"),
            desc: __("Select the page on which you have inserted <code>[request_quote]</code> shortcode.", "catalogx"),
            options: appLocalizer.all_pages,
            proSetting: true,
        },
        {
            key: 'set_wholesale_product_list_page',
            type: 'select',
            label: __("Set Wholesale Product List Page", "catalogx"),
            desc: __("Select the page on which you have inserted <code>[wholesale_product_list]</code> shortcode.", "catalogx"),
            options: appLocalizer.all_pages,
            proSetting: true,
        },
        {
            key: 'separator_content',
            type: 'section',
            label: "",
        },
        {
            key: 'shortCode',
            type: 'shortCode-table',
            label: __("Available Shortcodes", "catalogx"),
            desc: __('', "catalogx"),
            optionLabel: [
                'Shortcodes',
                'Description',
            ],
            option: [
                {
                    key: '',
                    label: '[catalog_enquiry_cart]',
                    desc: 'Enables you to create a seller dashboard',
                },
                {
                    key: '',
                    label: '[request_quote]',
                    desc: 'Enables you to create a seller dashboard',
                },
                {
                    key: '',
                    label: '[wholesale_product_list]',
                    desc: 'Enables you to create a seller dashboard',
                },
            ]
        }
    ]
}