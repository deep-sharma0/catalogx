import { render } from '@wordpress/element';
import { BrowserRouter } from 'react-router-dom';
import QuoteThankYou from './QuoteThankYou';

// Render the App component into the DOM
const rootElement = document.getElementById('quote-thank-you-page');
if (rootElement) {
    render(
        <BrowserRouter>
            <QuoteThankYou />
        </BrowserRouter>,
        rootElement
    );
}
