import { useState } from 'react';
import { __ } from '@wordpress/i18n';
import Recaptcha from './Recaptcha';

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
        <div className='catalogx-enquiry-free-form'>
            {
                formFields.map((field) => {
                    if (!field.active) { return }

                    switch (field.key) {
                        case "name":
                            return (
                                <div className='catalogx-form-free-sections'>
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
                                <div className='catalogx-form-free-sections'>
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
                                <div className='catalogx-form-free-sections'>
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
                                <div className='catalogx-form-free-sections'>
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
                                <div className='catalogx-form-free-sections'>
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
                                <div className='catalogx-form-free-sections'>
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

export default FreeForm;