import { useState, useEffect } from 'react';
import { __ } from '@wordpress/i18n';
import './EnquiryForm.scss';
import FromViewer from '../libreary/FormViewer';
import axios from 'axios';
import Recaptcha from './Recaptcha';

/**
 * Free form components
 * @param {*} props 
 * @returns 
 */
const FreeForm = (props) => {
    let { formFields, onSubmit } = props;
    if(!formFields) formFields = [];

    const [fileName, setFileName] = useState("");
    const [errorMessage, setErrorMessage] = useState("");
    const [captchaStatus, setCaptchaStatus] = useState(false);
    const [validationErrors, setValidationErrors] = useState({});

    /**
     * Handle input change
     * @param {*} e 
     */
    const handleChange = (e) => {
        const { name, value, type, files } = e.target;
        const filesizeLimitField = formFields.find(item => item.key === "filesize-limit");
        const maxFileSize = filesizeLimitField.label * 1024 * 1024;

        if (type === 'file') {
            const file = e.target.files[0];
            setFileName( file.name );
             // check file size
            if(file){
                if (file.size > maxFileSize) {
                    setErrorMessage(`File size exceeds ${filesizeLimitField.label} MB. Please upload a smaller file.`);
                    return;
                }
                setErrorMessage(""); // Clear any previous error message
                setFileName(file.name); // Store the uploaded file name
            }
            setInputs((prevData) => ({
                ...prevData,
                [name]: files[0],
            }));
        } else {
            setInputs((prevData) => ({
                ...prevData,
                [name]: value,
            }));
        }
    };

    const [inputs, setInputs] = useState(() => {
        const initialState = {};
        formFields.forEach((field) => {
            if (enquiryFormData.default_placeholder[field.key]) {
                initialState[field.key] = enquiryFormData.default_placeholder[field.key];
            }
        });
        return initialState;
    });
    
    
    /**
     * Handle input submit
     * @param {*} e 
     */
    const handleSubmit = (e) => {
        e.preventDefault();

        // Basic validation checks
        let errors = {};
        formFields.forEach((field) => {
            if (field.active) {
                const value = inputs[field.key] || ""; // Ensure it does not return undefined

                // Validate email format
                if (field.key === "email" && value) {
                    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    if (!emailRegex.test(value)) {
                        errors[field.key] = "Invalid email format";
                    }
                }
            }
        });

        // If there are errors, set state and return (prevent submission)
        if (Object.keys(errors).length > 0) {
            setValidationErrors(errors);
            return;
        }

        const data = new FormData();

        for (const key in inputs) {
            if (inputs.hasOwnProperty(key)) {
                data.append(key, inputs[key]);
            }
        }

        onSubmit(data);
    }

    return (
        <div className='enquiry-free-form'>
            {
                formFields.map((field) => {
                    if (!field.active) { return }

                    switch (field.key) {
                        case "name":
                            return (
                                <div className='form-free-sections'>
                                    <label>{field.label}</label>
                                    <input
                                        type="text"
                                        name={field.key}
                                        value={enquiryFormData.default_placeholder.name || inputs[field.key]}
                                        onChange={handleChange}
                                        required
                                    />
                                </div>
                            );
                        case "email":
                            return (
                                <div className='form-free-sections'>
                                    <label>{field.label}</label>
                                    <div className="field-wrapper">
                                        <input
                                            type="email"
                                            name={field.key}
                                            value={enquiryFormData.default_placeholder.email || inputs[field.key]}
                                            onChange={handleChange}
                                            required
                                        />
                                        {validationErrors[field.key] && <p className="error-message">{validationErrors[field.key]}</p>}
                                    </div>
                                </div>
                            );
                        case "phone":
                            return (
                                <div className='form-free-sections'>
                                    <label>{field.label}</label>
                                    <input
                                        type="number"
                                        name={field.key}
                                        value={inputs[field.key]}
                                        onChange={handleChange}
                                        required
                                    />
                                </div>
                            );
                        case "address":
                        case "subject":
                        case "comment":
                            return (
                                <div className='form-free-sections'>
                                    <label>{field.label}</label>
                                    <textarea
                                        name={field.key}
                                        value={inputs[field.key]}
                                        onChange={handleChange}
                                        required
                                    />
                                </div>
                            );
                        case "fileupload":
                            return (
                                <div className='form-free-sections'>
                                    <label className='attachment-main-label'>{field.label}</label>
                                    <div className="attachment-section field-wrapper">
                                        <label
                                            htmlFor="dropzone-file"
                                            className="attachment-label"
                                        >
                                            <div className="wrapper">
                                                <i class="adminLib-cloud-upload"></i>
                                                <p className="heading">
                                                    {fileName == '' ? (
                                                        <>
                                                            <span>{ __('Click to upload', 'catalogx') }</span> { __('or drag and drop', 'catalogx') }
                                                        </>
                                                    ) : fileName}
                                                </p>
                                            </div>
                                            <input 
                                                name={field.key}
                                                onChange={handleChange}
                                                required id="dropzone-file" 
                                                type="file" 
                                                className="hidden" />
                                        </label>
                                        {errorMessage && <p className="error-message">{errorMessage}</p>}
                                    </div>
                                </div>
                            );
                        case "captcha":
                            return (
                                <div className='form-free-sections'>
                                    <label>{field.label}</label>
                                    <div className='recaptcha-wrapper field-wrapper'>
                                        <Recaptcha
                                            captchaValid = {(validStatus) => setCaptchaStatus(validStatus)}
                                        />
                                    </div>
                                </div>
                            );
                    }
                })
            }

           <section className='popup-footer-section'>
                <button onClick={(e) => {
                    const captcha = formFields?.find((field)=> field.key == "captcha");
                    if (captcha?.active && !captchaStatus) 
                        return
                    handleSubmit(e)
                }}>
                    {__('Submit', 'catalogx')}</button>

                <button id='catalogx-close-enquiry-popup' className='catalogx-close-enquiry-popup'>{__('Close', 'catalogx')}</button>
           </section>
        </div>
    );
}

const EnquiryForm = (props) => {
    const [ loading, setLoading ] = useState(false);
    const [ toast, setToast ] = useState(false);
    const [ responseMessage, setResponseMessage ] = useState('');
    const formData = enquiryFormData;
    const proActive = formData.khali_dabba;

    const submitUrl = `${enquiryFormData.apiurl}/catalogx/v1/enquiries`;

    const onSubmit = (formData) => {
        setLoading(true);
      
        let productId = document.querySelector('#product-id-for-enquiry').value;
        let quantity = document.querySelector('.quantity .qty');
        if (quantity == null) {
            quantity = 1;
        } else {
            quantity = quantity.value;
        }

        formData.append('productId', productId);
        formData.append('quantity', quantity);

        axios.post(submitUrl, formData, {
            headers: {
              'Content-Type': 'multipart/form-data',
              "X-WP-Nonce": enquiryFormData.nonce
            },
          })
          .then(response => {
            setResponseMessage(response.data.msg)
            setLoading(false);
            setToast(true);
            if(response.data.redirect_link !== ''){
                window.location.href = response.data.redirect_link;
            }
            setTimeout(() => {
                setToast(false);
                window.location.reload();
            }, 3000);
          })
          .catch(error => {
            console.error('Error:', error);
          });
    }

    return (
        <div className='enquiry-form-modal'>
            {toast && 
               <div className="admin-notice-display-title">
                    <i className="admin-font adminLib-icon-yes"></i>
                    {responseMessage}
                </div>
            }
            {loading &&
                <section className='loader-component'>
                    <div class="three-body">
                        <div class="three-body__dot"></div>
                        <div class="three-body__dot"></div>
                        <div class="three-body__dot"></div>
                    </div>
                </section>
            }
            <div className='modal-wrapper'>
                <div className='modal-close-btn'>
                    <i className='admin-font adminLib-cross'></i>
                </div>
                <div>{enquiryFormData.content_before_form}</div>
                {
                    proActive ?
                    <FromViewer
                        formFields={formData.settings_pro}
                        onSubmit={onSubmit}
                    />
                    :
                    <FreeForm
                        formFields={formData.settings_free}
                        onSubmit={onSubmit}
                    />
                }
                <div>{enquiryFormData.content_after_form}</div>
            </div>
        </div>
    );
}

export default EnquiryForm;