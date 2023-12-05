import React, { useState, useEffect } from 'react'
import axios from 'axios'
import SaveButton from '/react/settings/SaveButton.jsx'

export default function ChangeLogin () {
	
    // Login default state
    const [login, setLogin] = useState('')
    const [password, setPassword] = useState('')
	
    const handleLoginChange = (e) => {
        var newLogin = e.target.value
        setLogin(newLogin)
    }
	
    const handlePasswordChange = (e) => {
        var newPassword = e.target.value
        setPassword(newPassword)
    }

    // Get current login data from database once at component loading
    useEffect( () => {
        axios.get('/api/settings.php', {
            params: {
                login: true
            }
        }).then(response => {
            setLogin(response.data)
        } )
    }, [])

    return (
        <form className="stg-board container d-flex flex-column" method="post">
		
			<h2>ユーザーネーム変更</h2>
	
			<div className="tr-row gap-20">
				<div className="col form-floating">
					<input type="login" className="form-control" id="login" placeholder="Login" value={login} onChange={handleLoginChange} />
					<label className="form-label" htmlFor='floatingInput'>新しいユーザーネーム</label>
				</div>
				<div className="col form-floating mb-3">
					<input type="password" className="form-control" id="password" placeholder="Password" onChange={handlePasswordChange} />
					<label className="form-label" htmlFor='password'>パスワード</label>
				</div>
			</div>
			<div>
				<SaveButton
                    settings={{ login, password }}
					type={'login'}
					text={'変更'}
                />
			</div>
				
		</form>
    )

}