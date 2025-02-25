/* global appLocalizer */
import React, { Component } from 'react';
import DialogContent from "@mui/material/DialogContent";
import DialogContentText from "@mui/material/DialogContentText";
import './popupContent.scss';
import { __ } from "@wordpress/i18n";

const Modulepopup = (props) => {
    return (
        <>
            <DialogContent>
                <DialogContentText>
                <div className="admin-module-dialog-content">
                        <div className="admin-image-overlay">
                            <div className="admin-overlay-content"><div className="admin-banner-content">
                                    {/* <h2>To activate please enable the {props.name} module first</h2> */}
                                    <h2>
                                        { sprintf(__('To activate please enable the %s module first', 'catalogx'), props.name) }
                                    </h2>
                            </div>
                                <a className="admin-go-pro-btn" href={appLocalizer.module_page_url}>{__("Enable Now", "catalogx")}</a>
                            </div>
                        </div>
                    </div>
                </DialogContentText>
            </DialogContent>
        </>
    );
}

export default Modulepopup;