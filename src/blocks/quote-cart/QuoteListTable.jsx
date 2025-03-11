import React, { useState, useEffect } from 'react';
import { __ } from "@wordpress/i18n";
import CustomTable, { TableCell } from "../../components/AdminLibrary/CustomTable/CustomTable";
import axios from 'axios';
import QuoteThankYou from './QuoteThankYou';
import './QuoteListTable.scss';
import '../../style/fonts.scss';

const QuoteList = () => {
	const [data, setData] = useState(null);
	const [selectedRows, setSelectedRows] = useState([]);
	const [productQuantity, setProductQuantity] = useState([]);
	const [loading, setLoading] = useState(false);
	const [responseContent, setResponseContent] = useState(false);
	const [responseStatus, setResponseStatus] = useState('');
	const [totalRows, setTotalRows] = useState();
	const [showThankYou, setShowThankYou] = useState(false);
	const [status, setStatus] = useState('');
	const [formData, setFormData] = useState({
        name: quoteCart.name || '',
        email: quoteCart.email || '',
        phone: '',
        message: '',
    });

	useEffect(() => {
		  const params = new URLSearchParams(location.search);
		  const orderIdParam = params.get('order_id');
		  const statusParam = params.get('status');
		  setShowThankYou(orderIdParam);
		  setStatus(statusParam || '');
		}, [location]);

	const handleInputChange = (e) => {
        const { name, value } = e.target;
        setFormData(prevState => ({
            ...prevState,
            [name]: value
        }));
    };

	useEffect(() => {
		requestData();
	}, []);

	const handleQuantityChange = (e, id, key) => {
		setProductQuantity(prev => ({
            ...prev,
            [id]: {
                quantity: e.target.value,
                key: key
            }
        }));
		
	}

	function requestData(rowsPerPage = 10, currentPage = 1,) {
		//Fetch the data to show in the table
		axios({
			method: "post",
			url: `${quoteCart.apiUrl}/${quoteCart.restUrl}/quote-cart`,
			headers: { "X-WP-Nonce": quoteCart.nonce },
			data: {
				page: currentPage,
            	row: rowsPerPage,
			}
		}).then((response) => {
			setTotalRows(response.data.count);
			setData(response.data.response);
		});

	}

	const handleRemoveCart = (e, id, key) => {
		axios({
			method: "delete",
			url: `${quoteCart.apiUrl}/${quoteCart.restUrl}/quote-cart`,
			headers: { "X-WP-Nonce": quoteCart.nonce },
			data: {
				productId : id,
				key : key
			},
		}).then((response) => {
			requestData();
		});
	}

	const handleUpdateCart = () => {
		const newProductQuantity = selectedRows.length > 0 ? 
			selectedRows.map(row => {
				let id = row.id;
				let value = productQuantity[id].quantity || 1;
				return {
					key: row.key,
					id: id,
					quantity: value
				};
				})
			: Object.entries(productQuantity).map(([id, value]) => ({
				id: id,
				quantity: value.quantity,
    			key: value.key
    	}));

		axios({
			method: "put",
			url: `${quoteCart.apiUrl}/${quoteCart.restUrl}/quote-cart`,
			headers: { "X-WP-Nonce": quoteCart.nonce },
			data: {
				products : newProductQuantity
			},
		}).then((response) => {
			requestData();
			window.location.reload();
		});
	}

	const handleSendQuote = () => {
		const sendBtn = document.getElementById('SendQuote');
		sendBtn.style.display = 'none';
		setLoading(true);
		axios({
			method: "post",
			url: `${quoteCart.apiUrl}/${quoteCart.restUrl}/quotes`,
			headers: { "X-WP-Nonce": quoteCart.nonce },
			data: {
				formData : formData,
			},
		}).then((response) => {
			setLoading(false);
			setResponseContent(true);
			if(response.status === 200 ){
				setResponseStatus("success")
				setShowThankYou(response.data.order_id);
			} else{
				setResponseStatus("error");
				sendBtn.style.display = 'block';
				return;
			}
		});
	}

	const Loader = () =>{
		return(
			<section className='loader_wrapper'>
				<div className='loader'></div>
			</section>
		)
	}

	//columns for the data table
	const columns = [
		{
			name: __("Product", "catalogx"),
			cell: (row) =>
				<TableCell title="image" >
					<p dangerouslySetInnerHTML={{ __html: row.image }}></p>
					<p dangerouslySetInnerHTML={{ __html: row.name }}></p>
					<p className='adminLib-cross' onClick={(e) => handleRemoveCart(e, row.id, row.key)}></p>
				</TableCell>,
				
		},
        {
			name: __("Quantity", "catalogx"),
			cell: (row) => (
				<TableCell title="quantity">
						<input type="number" name="quantity" min="1" value={productQuantity[row.id]?.quantity ?? row.quantity} placeholder="1" onChange={(e) => handleQuantityChange(e, row.id, row.key)} />
				</TableCell>
			),
		},
		{
			name: __("Subtotal", "catalogx"),
			cell: (row) => (
				<TableCell title="subtotal">
						<p dangerouslySetInnerHTML={{ __html: row.total }}></p>
				</TableCell>
			),
		},

	];

	return (
		<>
			{showThankYou || status ? (
				<QuoteThankYou order_id={showThankYou} status={status} />
			) : (
				<>
					<div className="admin-enrollment-list QuoteListTable-main-wrapper">
						<div className="admin-page-title">
							<div className="add-to-quotation-button">
								<button onClick={handleUpdateCart}>
									{__("Update Cart", "catalogx")}
								</button>
							</div>
						</div>
						<CustomTable
							data={data}
							columns={columns}
							selectable={true}
							handleSelect={(selectRows) => {
								setSelectedRows(selectRows);
							}}
							handlePagination={requestData}
							defaultRowsParPage={10}
							defaultTotalRows={totalRows}
							perPageOption={[10, 25, 50]}
						/>
					</div>
	
					{data && Object.keys(data).length > 0 && (
						<div className="main-form">
							{loading && <Loader />}
							<p className="form-row form-row-first">
								<label htmlFor="name">{__("Name:", "catalogx")}</label>
								<input
									type="text"
									id="name"
									name="name"
									value={formData.name}
									onChange={handleInputChange}
								/>
							</p>
							<p className="form-row form-row-last">
								<label htmlFor="email">{__("Email:", "catalogx")}</label>
								<input
									type="email"
									id="email"
									name="email"
									value={formData.email}
									onChange={handleInputChange}
								/>
							</p>
							<p className="form-row form-row-wide">
								<label htmlFor="phone">{__("Phone:", "catalogx")}</label>
								<input
									type="tel"
									id="phone"
									name="phone"
									value={formData.phone}
									onChange={handleInputChange}
								/>
							</p>
							<p className="form-row form-row-wide">
								<label htmlFor="message">{__("Message:", "catalogx")}</label>
								<textarea
									id="message"
									name="message"
									rows="4"
									cols="50"
									value={formData.message}
									onChange={handleInputChange}
								></textarea>
							</p>
							<p>
								<button id="SendQuote" onClick={handleSendQuote}>
									{__("Send Quote", "catalogx")}
								</button>
							</p>
							{responseContent && (
								<section className={`response-message-container ${responseStatus}`}>
									<p>
										{responseStatus === "error"
											? __("Something went wrong! Try Again", "catalogx")
											: __("Form submitted successfully", "catalogx")}
									</p>
								</section>
							)}
						</div>
					)}
				</>
			)}
		</>
	);
	
}
export default QuoteList;