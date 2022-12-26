import React, { useState, useEffect } from 'react'
import axios from 'axios'
import SaveButton from '/react/settings/SaveButton.jsx'

export default function ChangeEmail () {
	
    // Email default state
    const [email, setEmail] = useState('')
    const [password, setPassword] = useState('')
	
    const handleEmailChange = (e) => {
        var newEmail = e.target.value
        setEmail(newEmail)
    }
	
    const handlePasswordChange = (e) => {
        var newPassword = e.target.value
        setPassword(newPassword)
    }

    // Get current email data from database once at component loading
    useEffect( () => {
        axios('/api/settings.php' + '?email=true').then(response => {
            setEmail(response.data)
        } )
    }, [])

    return (
        <form className="stg-board container d-flex flex-column" method="post">
		
			<h2>メールアドレス変更</h2>
	
			<div className="tr-row gap-20">
				<div className="col form-floating">
					<input type="email" className="form-control" id="email" placeholder="Email" value={email} onChange={handleEmailChange} />
					<label className="form-label" htmlFor='floatingInput'>メールアドレス</label>
				</div>
				<div className="col form-floating mb-3">
					<input type="password" className="form-control" id="password" placeholder="Password" onChange={handlePasswordChange} />
					<label className="form-label" htmlFor='password'>パスワード</label>
				</div>
			</div>
			<div>
				<SaveButton
                    settings={{ email, password }}
					type={'email'}
					text={'変更'}
                />
			</div>
				
		</form>
    )

}