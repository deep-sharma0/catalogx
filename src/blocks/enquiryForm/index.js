import { render } from '@wordpress/element';
import { BrowserRouter} from 'react-router-dom';
import EnquiryForm from './EnquiryForm';

// Render the App component into the DOM

// render(<BrowserRouter><EnquiryForm/></BrowserRouter>, document.getElementById('catalog-modal'));
document.addEventListener("DOMContentLoaded", () => {
    const element = document.getElementById("catalog-modal");
    if (element) {
      render(
        <BrowserRouter>
          <EnquiryForm />
        </BrowserRouter>,
        element
      );
    }
  });