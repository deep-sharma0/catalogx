import React, { useState, useEffect, useRef } from 'react';
import { __ } from "@wordpress/i18n";
import CustomTable, { TableCell } from "../AdminLibrary/CustomTable/CustomTable";
import { getApiLink } from "../../services/apiService";
import axios from 'axios';
import Dialog from "@mui/material/Dialog";
import { DateRangePicker } from 'react-date-range';
import 'react-date-range/dist/styles.css'; // main style file
import 'react-date-range/dist/theme/default.css'; // theme css file
import Popoup from "../PopupContent/PopupContent.jsx";
import AddRole from './addRole.jsx';
import './roles.scss';

const Roles = () => {
	const [data, setData] = useState(null);
	const [totalRows, setTotalRows] = useState();
	const [openDialog, setOpenDialog] = useState(false);
	const [submitData, setSubmitData] = useState({});

	useEffect(() => {
		// if (appLocalizer.khali_dabba) {
			requestData();
		// }
	}, []);

    useEffect(() => {
		// if (appLocalizer.khali_dabba) {
			axios({
				method: "post",
				url: getApiLink('get-roles'),
				headers: { "X-WP-Nonce": appLocalizer.nonce },
				data: {
					counts: true
				},
			}).then((response) => {
				setTotalRows(response.data);
			});
		// }
	}, []);

	function requestData(
		rowsPerPage = 10,
		currentPage = 1,
	) {

		//Fetch the data to show in the table
		axios({
			method: "post",
			url: getApiLink('get-roles'),
			headers: { "X-WP-Nonce": appLocalizer.nonce },
			data: {
				page: currentPage,
				row: rowsPerPage,
			},
		}).then((response) => {
			setData(response.data);
		});
	}

	const requestApiForData = (rowsPerPage, currentPage) => {
		setData(null);
		requestData(
			rowsPerPage,
			currentPage,
		);
	};

	const realtimeFilter = [
		
	];

    useEffect(() => {
		axios({
			method: "post",
			url: getApiLink('edit-role'),
			headers: { "X-WP-Nonce": appLocalizer.nonce },
			data: { ...submitData },
		}).then((response) => {
			// console.log(response.data);
		});
	}, [submitData]);

    const handleSubmit = (id, key, value) => {
        setSubmitData({
            id: id,
            key: key,
            value: value
        });

		setData((data) => 
			data.map((role) =>
				role.id === id ? { ...role, [key]: value } : role
			)
		);
    }

	//columns for the data table
	const columns = [
		{
			name: __("Role", "catalogx"),
			cell: (row) => (
				<TableCell title="Role">
					<div className='action-section'>
                        <p>{row.role_name}</p>
					</div>
				</TableCell>
			),
		},
		{
			name: __("Discount Type", "catalogx"),
			cell: (row) => (
				<TableCell title="Discount type">
					<div className='action-section'>
                        <select onChange={(e) => handleSubmit(row.id, 'discount_type', e.target.value)}>
							<option value="">Select </option>
							<option selected={ row.discount_type == 'fixed' } value="fixed">Fixed Amount</option>
							<option selected={ row.discount_type == 'percentage' } value="percentage">Percentage Amount</option>
                    	</select>
					</div>
				</TableCell>
			),
		},
		{
			name: __("Discount Amount", "catalogx"),
			cell: (row) => (
				<TableCell title="Discount Amount">
					<div className='action-section'>
                        <input value={ row.discount_amount } type="number" onChange={(e) => handleSubmit(row.id, 'discount_amount', e.target.value)}></input>
					</div>
				</TableCell>
			),
		},
        {
			name: __("Minimum Quantity", "catalogx"),
			cell: (row) => (
				<TableCell title="Minimum Quantity">
					<div className='action-section'>
                        <input value={ row.minimum_quantity } type="number" onChange={(e) => handleSubmit(row.id, 'minimum_quantity', e.target.value)}></input>
					</div>
				</TableCell>
			),
		}

	];

    const handleAddRole = () => {
        setOpenDialog(true);
    }
	return (
		<>
		{
			<Dialog
				className="admin-roles-popup"
				open={openDialog}
				onClose={() => {
					setOpenDialog(false);
				}}
				aria-labelledby="form-dialog-title"
			>
				<AddRole
					setOpenDialog = {setOpenDialog}
				/>
			</Dialog>
		}
			{/* {!appLocalizer.khali_dabba ? (
				<>
					<Dialog
						className="admin-module-popup"
						open={openDialog}
						onClose={() => {
							setOpenDialog(false);
						}}
						aria-labelledby="form-dialog-title"
					>
						<span
							className="admin-font font-cross stock-manager-popup-cross"
							onClick={() => {
								setOpenDialog(false);
							}}
						></span>
						<Popoup />
					</Dialog>
					<div
						className="enrollment-img"
						onClick={() => {
							setOpenDialog(true);
						}}>
					</div>
				</>
			) : ( */}
				<div className="admin-roles-list">
					<div className="admin-page-title">
						<p>{__("Roles", "catalogx")}</p>
                        <div className="add-to-quotation-button">
                            <button onClick={handleAddRole}>
                                Add Role
                            </button>
                        </div>
					</div>
					{
						<CustomTable
							data={data}
							columns={columns}
							selectable={true}
							handlePagination={requestApiForData}
							defaultRowsParPage={10}
							defaultTotalRows={totalRows}
							perPageOption={[10, 25, 50]}
							// realtimeFilter={realtimeFilter}
							// typeCounts={wholesaleUserStatus}
							// autoLoading={false}
						/>
					}
				</div>
			{/* )} */}
		</>

	);
}
export default Roles;