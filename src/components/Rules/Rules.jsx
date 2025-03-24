import React, { useState } from "react";
import { useModules } from "../../contexts/ModuleContext";

import "./Rules.scss";
import Dialog from "@mui/material/Dialog";
import Popoup from "../PopupContent/PopupContent";
import Modulepopup from "../PopupContent/ModulePopup";
import "../AdminLibrary/CustomTable/table.scss";

/**
 * Render rule page
 * @returns 
 */
const Rules = () => {

    // Check pro is active and module is active or not.
    const { modules } = useModules();

    // State variable declearation
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
						className="dynamic-rule-img"
						onClick={() => {
							setOpenDialog(true);
						}}>
					</div>
				</>
			) : !modules.includes('rules') ? (
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
						<Modulepopup name='Rules' />
					</Dialog>
					<div
						className="dynamic-rule-img"
						onClick={() => {
							setOpenDialog(true);
						}}>
					</div>
				</>
			) : (
                <main className="catalog-rules-main-container" id="rules-list-table">
                </main>
            )}      
        </>
    );
}

export default Rules;