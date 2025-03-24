import React, { useState } from 'react';
import { getApiLink } from "../../../services/apiService";
import axios from 'axios';
import Loading from './Loading';

const Quote = (props) => {
    const { onFinish, onPrev } = props;
    const [loading, setLoading] = useState(false);
    const [restrictUserQuote, setRestrictUserQuote] = useState([]);

    const handleRestrictUserQuoteChange = (event) => {
        const { checked, name } = event.target;
        setRestrictUserQuote((prevState) =>
            checked ? [...prevState, name] : prevState.filter(value => value !== name)
        );
    };

    const saveQuoteSettings = () => {
        setLoading(true);
        const data = {
            action: 'quote',
            restrictUserQuote: restrictUserQuote
        };

        axios({
            method: "post",
            url: getApiLink('settings'),
            headers: { "X-WP-Nonce": appLocalizer.nonce },
            data: data
        }).then((response) => {
            setLoading(false);
            onFinish();
        });
    };

    return (
        <section>
            <h2>Quote</h2>
            <article className='module-wrapper'>
                <div className="module-items">
                    <div className="module-details">
                        <h3>Restrict for logged-in user</h3>
                        <p className='module-description'>
                        If enabled, non-logged-in users cannot submit quotation requests.
                        </p>
                    </div>
                    <div className='toggle-checkbox'>
                        <input
                            type="checkbox"
                            id="logged_out"
                            name="logged_out"
                            checked={restrictUserQuote.includes('logged_out')}
                            onChange={handleRestrictUserQuoteChange}
                        />
                        <label htmlFor='logged_out'></label>
                    </div>
                </div>
            </article>

            <footer className='setup-footer-btn-wrapper'>
                <div>
                    <button className='footer-btn pre-btn' onClick={onPrev}>Prev</button>
                    <button className='footer-btn' onClick={onFinish}>Skip</button>
                </div>
                <button className='footer-btn next-btn' onClick={saveQuoteSettings}>Finish</button>
            </footer>
        </section>
    );
};

export default Quote;
