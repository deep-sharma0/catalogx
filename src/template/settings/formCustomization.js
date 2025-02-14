import { __ } from '@wordpress/i18n';

export default {
    id: 'enquiry_form_customization',
    priority: 30,
    name: __("Enquiry Form Builder", "catalogx"),
    desc: __("Design a personalized enquiry form with built-in form builder. ", "catalogx"),
    icon: 'adminLib-contact-form',
    submitUrl: 'save_enquiry',
    modal: [
        {
            key: 'form_customizer',
            type: 'form-customizer',
            desc: __("Form Customizer", "catalogx"),
            classes: 'form_customizer',
            moduleEnabled: 'enquiry',
            proSetting: true
        }
    ]
};
