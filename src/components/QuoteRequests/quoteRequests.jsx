import { __ } from "@wordpress/i18n";
import Dialog from "@mui/material/Dialog";
import React, { useState } from "react";
import Popoup from "../PopupContent/PopupContent";
import Modulepopup from "../PopupContent/ModulePopup";
import { useModules } from '../../contexts/ModuleContext.jsx';
import './quoteRequests.scss';
import '../AdminLibrary/CustomTable/table.scss';
import 'react-date-range/dist/styles.css'; // main style file
import 'react-date-range/dist/theme/default.css'; // theme css file

export default function QuotesList() {
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
							className="admin-font adminLib-cross stock-manager-popup-cross"
							onClick={() => {
								setOpenDialog(false);
							}}
						></span>
						<Popoup />
					</Dialog>
					<div
						className="quote-img"
						onClick={() => {
							setOpenDialog(true);
						}}>
					</div>
				</>
			) : !modules.includes('quote') ? (
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
						<Modulepopup name='quote' />
					</Dialog>
					<div
						className="quote-img"
						onClick={() => {
							setOpenDialog(true);
						}}>
					</div>
        </>
      ) : (
        <>
			<div>
				<div className="admin-subscriber-list" id="quote-list-table">
				</div>
			</div>
        </>
      )}
    </>
   
  );
}