/* global appLocalizer */
import React, { Component } from 'react';
import DialogContent from "@mui/material/DialogContent";
import DialogContentText from "@mui/material/DialogContentText";
import './popupContent.scss';

const Propopup = () => {
    return (
        <>
            <DialogContent>
                <DialogContentText>
                    <div className="admin-module-dialog-content">
                        <div className="admin-image-overlay">
                            <div className="admin-overlay-content">
                                <h1 className="banner-header">Unlock <span className="banner-pro-tag">Pro</span> </h1>
                                <div className="admin-banner-content list-popup">
                                    <strong>Unlock revenue-boosting features with CatalogX Pro today !</strong>
                                    <p>&nbsp;</p>
                                    <p>1. Speed up sales with personalized quotes.</p>
                                    <p>2. Boost bulk sales with exclusive pricing and wholesale order forms.</p>
                                    <p>3. Enable multiple product enquiries at once to boost customer engagement.</p>
                                    <p>4. Advanced enquiry messaging with file uploads, tagging etc..</p>
                                    <p>5. Increase revenue with tailored pricing for different user roles.</p>
                                    <p>6. Drive higher sales with customized pricing for product categories.</p>
                                </div>
                                <a className="admin-go-pro-btn" target="_blank" href={appLocalizer.pro_url}>Upgrade to Pro</a>
                            </div>
                        </div>
                    </div>
                </DialogContentText>
            </DialogContent>
        </>
    );
}

export default Propopup;