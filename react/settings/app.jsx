import React, { useState, useEffect } from 'react'
import ReactDOM from 'react-dom/client'
import ChangeEmail from "/react/settings/ChangeEmail.jsx"
import ChangePassword from "/react/settings/ChangePassword.jsx"
import Privacy from "/react/settings/Privacy.jsx"
import Sidebar from "/react/settings/Sidebar.jsx"

class Settings extends React.Component {

    constructor (props) {
        super(props)
        this.state = {
            board: 'privacy'
        }
    }

    getPage = (page) => {
        switch (page) {
            case 'changeEmail': var component = <ChangeEmail />; break;
            case 'changePassword': var component = <ChangePassword />; break;
            case 'privacy': var component = <Privacy />; break;
        }
        this.setState( (prevState) => ( {
            ...prevState,
            board: component
        } ) )
        this.forceUpdate()
    }
  
    render () {
        return (
            <div className="container d-flex gap end">
                <Sidebar changePage={this.getPage} />
                {this.state.board}
            </div>
        )
    }

}

const root = ReactDOM.createRoot(document.querySelector('#settings'))
root.render(<Settings />)