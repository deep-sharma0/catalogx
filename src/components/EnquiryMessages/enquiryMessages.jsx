import React, { useState } from 'react';
import "./enquiryMessages.scss";
import { useModules } from '../../contexts/ModuleContext.jsx';
import Dialog from "@mui/material/Dialog";
import Popoup from "../PopupContent/PopupContent";
import Modulepopup from "../PopupContent/ModulePopup";

const EnquiryMessages = (props) => {

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
                    className="enquiry-img"
                    onClick={() => {
                        setOpenDialog(true);
                    }}>
                </div>
            </>
        ) : !modules.includes('enquiry') ? (
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
                    <Modulepopup name='Enquiry' />
                </Dialog>
                <div
                    className="enquiry-img"
                    onClick={() => {
                        setOpenDialog(true);
                    }}>
                </div>
            </>
          ) : (
            <div className="container" id="enquiry-messages">
            </div>
        )}
        </>
    );
}

export default EnquiryMessages;