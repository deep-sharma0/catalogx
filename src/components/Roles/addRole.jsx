import React, { useState, useEffect, useRef } from 'react';
import { __ } from "@wordpress/i18n";
import { getApiLink } from "../../services/apiService";
import axios from 'axios';
import './roles.scss';

const AddRole = (props) => {
    const { setOpenDialog } = props;
    const [name, setName] = useState('');
    const [inheritRole, setInheritRole] = useState('');

    const addRole = () => {
        axios({
			method: "post",
			url: getApiLink('add-role'),
			headers: { "X-WP-Nonce": appLocalizer.nonce },
			data: {
				name: name,
				inheritRole: inheritRole,
			},
		}).then((response) => {
            setOpenDialog(false);

		});
    }
    
    return(
        <main className='add-new-role-wrapper'>
            <h1 className='popup-heading'>Add new role</h1>
            <div className='popup-content-wrapper'>
                <label>Name:</label>
                <input 
                    type="text" 
                    id="display-name" 
                    value={name}
                    onChange={(e) => setName(e.target.value)}/>
            </div>
            <div className='popup-content-wrapper'>
                <label>Inherit role:</label>
                <select
                    id="inherit-role"
                    value={inheritRole}
                    onChange={(e) => setInheritRole(e.target.value)}
                >
                    {appLocalizer.role_array.map((role) => (
                        <option key={role.key} value={role.value}>
                            {role.label}
                        </option>
                    ))}
                </select>
            </div>
            <div className='popup-content-wrapper right-alignment'>
                <button className='modal-close' onClick={()=> setOpenDialog(false)}>Close</button>
                <button className='add-roles-btn' onClick={addRole}>Add Role</button>
            </div>
        </main> 
    );
}
export default AddRole;