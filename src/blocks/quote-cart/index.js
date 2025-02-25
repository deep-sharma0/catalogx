import { registerBlockType } from "@wordpress/blocks";
import { useBlockProps } from "@wordpress/block-editor";
import { render } from "@wordpress/element";
import { BrowserRouter } from 'react-router-dom';
import QuoteListTable from './QuoteListTable';

registerBlockType("catalogx/quote-cart", {
	apiVersion: 2,
	title: "Quote Cart",
	icon: "list-view",
	category: "catalogx",
	supports: {
		html: false,
	},
	edit() {
		const blockProps = useBlockProps();
		return (
		<div {...blockProps} id="request_quote_list">
			{QuoteListTable()}
		</div>
		);
	},
	save() {
		return (
		<div id="request_quote_list">
		</div>
		);
	},
});

document.addEventListener("DOMContentLoaded", () => {
  const element = document.getElementById("request_quote_list");
  if (element) {
    render(
      <BrowserRouter>
        <QuoteListTable />
      </BrowserRouter>,
      element
    );
  }
});
