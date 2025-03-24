import { render } from '@wordpress/element';
import { BrowserRouter } from 'react-router-dom';
import App from './app.js';


import './style/common.scss';

const MainApp = () => (

    <BrowserRouter>
        <App />
    </BrowserRouter>
);

render(<MainApp />, document.getElementById('admin-main-wrapper'));