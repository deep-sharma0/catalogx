/* global appLocalizer */
import React, { Component } from 'react';
import DialogContent from "@mui/material/DialogContent";
import DialogContentText from "@mui/material/DialogContentText";
import './popupContent.scss';
import { __ } from "@wordpress/i18n";

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
                                    <strong>{ __('Unlock revenue-boosting features with CatalogX Pro today!', 'catalogx') }</strong>
                                    <p>&nbsp;</p>
                                    <p>{ __('1. Speed up sales with personalized quotes.', 'catalogx') }</p>
                                    <p>{ __('2. Boost bulk sales with exclusive pricing and wholesale order forms.', 'catalogx') }</p>
                                    <p>{ __('3. Enable multiple product enquiries at once to boost customer engagement.', 'catalogx') }</p>
                                    <p>{ __('4. Advanced enquiry messaging with file uploads, tagging, etc.', 'catalogx') }</p>
                                    <p>{ __('5. Increase revenue with tailored pricing for different user roles.', 'catalogx') }</p>
                                    <p>{ __('6. Drive higher sales with customized pricing for product categories.', 'catalogx') }</p>
                                </div>
                                <a className="admin-go-pro-btn" target="_blank" href={appLocalizer.pro_url}>{__("Upgrade to Pro", "catalogx")}</a>
                            </div>
                        </div>
                    </div>
                </DialogContentText>
            </DialogContent>
        </>
    );
}

export default Propopup;