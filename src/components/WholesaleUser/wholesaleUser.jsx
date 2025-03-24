import React, { useState } from 'react';
import { __ } from "@wordpress/i18n";
import Dialog from "@mui/material/Dialog";
import Popoup from "../PopupContent/PopupContent.jsx";
import Modulepopup from "../PopupContent/ModulePopup.jsx";
import { useModules } from '../../contexts/ModuleContext.jsx';
import './wholesaleUser.scss';
import '../AdminLibrary/CustomTable/table.scss';
import 'react-date-range/dist/styles.css'; // main style file
import 'react-date-range/dist/theme/default.css'; // theme css file

const WholesaleUser = () => {
	// Check pro is active and module is active or not.
    const { modules } = useModules();
	const [openDialog, setOpenDialog] = useState(false);

	return (
		<>
			{!appLocalizer.khali_dabba ? (
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
							className="admin-font adminLib-cross"
							onClick={() => {
								setOpenDialog(false);
							}}
						></span>
						<Popoup />
					</Dialog>
					<div
						className="wholesale-user-image"
						onClick={() => {
							setOpenDialog(true);
						}}>
					</div>
				</>
			) : !modules.includes('wholesale') ? (
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
							className="admin-font adminLib-cross stock-manager-popup-cross"
							onClick={() => {
								setOpenDialog(false);
							}}
						></span>
						<Modulepopup name='Wholesale' />
					</Dialog>
					<div
						className="wholesale-user-image"
						onClick={() => {
							setOpenDialog(true);
						}}>
					</div>
				</>
			  ) : (
				<div className="admin-wholesale-list" id="wholesale-list-table">
				</div>
		 )}
		</>

	);
}
export default WholesaleUser;