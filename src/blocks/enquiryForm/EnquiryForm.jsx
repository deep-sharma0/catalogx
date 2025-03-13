import { useState } from 'react';
import { __ } from '@wordpress/i18n';
import './EnquiryForm.scss';
import FreeForm from './FreeForm';
import FromViewer from '../../components/AdminLibrary/Inputs/Special/FromViewer/FormViewer';
import axios from 'axios';

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
                <div className='catalogx-modal-close-btn'>
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