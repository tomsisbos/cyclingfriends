import React from 'react'

export default function ChangePassword () {

    return (
        <form className="container d-flex flex-column" method="post">
		
		<h2>Change password</h2>
	
			<div className="tr-row gap-20">
				<div className="col form-floating">
					<input type="password" className="form-control" id="floatingInput" placeholder="Current Password" name="current-password" />
					<label className="form-label" htmlFor="floatingInput">Current Password</label>
				</div>
				<div className="col form-floating mb-3">
					<input type="password" className="form-control" id="floatingPassword" placeholder="New Password" name="new-password" />
					<label className="form-label" htmlFor="floatingPassword">New Password</label>
				</div>
			</div>
			<div>
				<button type="submit" className="btn button btnright button-primary" name="password-submit">Change password</button>
			</div>
				
		</form>
    )

}