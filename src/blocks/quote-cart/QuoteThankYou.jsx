import React, { useState, useEffect } from 'react';
import { useLocation } from 'react-router-dom';
import { getApiLink } from "../../services/apiService";
import { __ } from "@wordpress/i18n";
import axios from 'axios';

const QuoteThankYou = (props) => {
    const [orderId, setOrderId] = useState(props.order_id);
    const [status, setStatus] = useState(props.status);
    const [reason, setReason] = useState('');
    const [successMessage, setSuccessMessage] = useState('');
  
    const handleRejectQuote = () => {
        axios({
			method: "post",
			url: `${quoteCart.apiUrl}/catalogx/v1/quotes`,
			headers: { "X-WP-Nonce": quoteCart.nonce },
			data: {
				orderId: orderId,
                status: status,
                reason: reason,
			},
		}).then((response) => {
            setSuccessMessage(response.data.message);
		});
    }

    return (
        <>
            {successMessage ? (
                <div className="success-message">{successMessage}</div>
            ) : orderId && status ? (
                <div className="reject-quote-from-mail">
                    <div className="reject-content">
                        <p>{`${__('You are about to reject the quote', 'catalogx')} ${orderId}`}</p>
                        <p>
                            <label>
                                {__('Please feel free to enter here your reason or provide us your feedback:', 'catalogx')}
                            </label>
                            <textarea
                                name="reason"
                                id="reason"
                                cols="10"
                                rows="3"
                                value={reason}
                                onChange={(e) => setReason(e.target.value)}
                            ></textarea>
                        </p>
                        <button onClick={handleRejectQuote}>
                            {__('Reject the quote', 'catalogx')}
                        </button>
                    </div>
                </div>
            ) : orderId ? (
                <div>
                    <p>
                        Thank you for your quote request{' '}
                        <strong>
                            {quoteCart.khali_dabba ? (
                                <a href={quoteCart.quote_my_account_url}>{orderId}</a>
                            ) : (
                                orderId
                            )}
                        </strong>.
                    </p>
                    <p>
                        {__(
                            'Our team is reviewing your details and will get back to you shortly with a personalized quote. We appreciate your patience and look forward to serving you!',
                            'catalogx'
                        )}
                    </p>
                </div>
            ) : null}
        </>
    );
    
}

export default QuoteThankYou;
