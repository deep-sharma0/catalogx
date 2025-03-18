import { registerBlockType } from '@wordpress/blocks';
import { useBlockProps } from '@wordpress/block-editor';
import { __ } from '@wordpress/i18n';

// Register the block
registerBlockType('catalogx/quote-button', {
    title: 'Quote Button',
    icon: {
        src: <span className="adminLib-icon adminLib-price-quote-icon"></span>,
    },
    category: 'catalogx',
    edit() {
        const blockProps = useBlockProps();
        return (
            <div {...blockProps}>
                <button className="catalogx-add-request-quote-button">{__('Add to Quote', 'catalogx')}</button>
            </div>
        );
    },
    save() {
        return (
            <div>
                <button className="catalogx-add-request-quote-button">{__('Add to Quote', 'catalogx')}</button>
                <div className="quote-message-container"></div>
            </div>
        );
    },
});

document.addEventListener('DOMContentLoaded', function() {
    const addQuoteButton = document.querySelector('.catalogx-add-request-quote-button');
    if (addQuoteButton) {
        addQuoteButton.addEventListener('click', function(event) {
            event.preventDefault();
            const productElement = document.querySelector('[data-block-name="woocommerce/single-product"]');
            const productId = productElement ? productElement.dataset.productId : null;

            const quantityElement = document.querySelector('.quantity .qty');
            const quantity = quantityElement ? quantityElement.value : 1;

            const requestData = new URLSearchParams({
                action: 'quote_added_in_list',
                product_id: productId,
                quantity: quantity,
                quote_action: 'add_item'
            });

            fetch(quoteButton.ajaxurl, {
                method: 'POST',
                body: requestData
            })
            .then(response => {
                return response.json();
            })
            .then(data => {
                console.log('Response:', data.message);
                const messageContainer = document.querySelector('.quote-message-container');
                if (messageContainer) {
                    messageContainer.textContent = data.message;
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        });
    }
});
