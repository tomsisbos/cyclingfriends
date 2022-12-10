import React from 'react'

export default function ChangeEmail () {

    return (
        <form className="container d-flex flex-column" method="post">
		
			<h2>Change email</h2>
	
			<div className="tr-row gap-20">
				<div className="col form-floating">
					<input type="email" className="form-control" id="floatingInput" placeholder="Email" name="email" value="defaultEmail" />
					<label className="form-label" htmlFor="floatingInput">Email address</label>
				</div>
				<div className="col form-floating mb-3">
					<input type="password" className="form-control" id="floatingPassword" placeholder="Password" name="password" />
					<label className="form-label" htmlFor="floatingPassword">Password</label>
				</div>
			</div>
			<div>
				<button type="submit" className="btn button btnright button-primary" name="email-submit">Change email</button>
			</div>
				
		</form>
    )

}