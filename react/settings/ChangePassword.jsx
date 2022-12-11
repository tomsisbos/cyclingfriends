import React, { useState, useEffect } from 'react'
import axios from 'axios'
import SaveButton from '/react/settings/SaveButton.jsx'

export default function ChangePassword () {

	// Password default state
    const [currentPassword, setCurrentPassword] = useState('')
    const [newPassword, setNewPassword] = useState('')
	
    const handleCurrentPasswordChange = (e) => {
        var newCurrentPassword = e.target.value
        setCurrentPassword(newCurrentPassword)
    }
	
    const handleNewPasswordChange = (e) => {
        var newNewPassword = e.target.value
        setNewPassword(newNewPassword)
    }

    return (
        <form className="container d-flex flex-column" method="post">
		
		<h2>Change password</h2>
	
			<div className="tr-row gap-20">
				<div className="col form-floating">
					<input type="password" className="form-control" id="floatingInput" placeholder="Current Password" onChange={handleCurrentPasswordChange} />
					<label className="form-label" htmlFor="floatingInput">Current Password</label>
				</div>
				<div className="col form-floating mb-3">
					<input type="password" className="form-control" id="floatingPassword" placeholder="New Password" onChange={handleNewPasswordChange} />
					<label className="form-label" htmlFor="floatingPassword">New Password</label>
				</div>
			</div>
			<div>
				<SaveButton
                    settings={{ currentPassword, newPassword }}
					type={'password'}
					text={'Change Password'}
                />
			</div>
				
		</form>
    )

}