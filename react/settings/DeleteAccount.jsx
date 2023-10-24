import React, { useState, useEffect } from 'react'
import axios from 'axios'
import SaveButton from '/react/settings/SaveButton.jsx'

export default function DeleteAccount () {
	
    // Login default state
    const [password, setPassword] = useState('')
	
    const handlePasswordChange = (e) => {
        var newPassword = e.target.value
        setPassword(newPassword)
    }

    return (
        <form className="stg-board container d-flex flex-column" method="post">
		
			<h2>アカウント削除</h2>
	
			<div className="tr-row gap-20">
				<div className="col form-floating mb-3">
					<input type="password" className="form-control" id="password" placeholder="Password" onChange={handlePasswordChange} />
					<label className="form-label" htmlFor='password'>パスワード</label>
				</div>
			</div>
			<div>
				<SaveButton
                    settings={{ password }}
					type={'deleteAccount'}
					text={'削除'}
                    confirmation={'これ以上進むと、このアカウントに付随されている全てのデータ（アクティビティ、ルート、写真や絶景スポット等も含む）が削除されます。削除されたデータの復旧は出来ません。宜しいですか？'}
                />
			</div>
				
		</form>
    )

}