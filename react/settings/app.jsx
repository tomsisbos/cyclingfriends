import React, { useState, createContext } from 'react'
import ReactDOM from 'react-dom/client'
import ChangeLogin from "/react/settings/ChangeLogin.jsx"
import ChangeEmail from "/react/settings/ChangeEmail.jsx"
import ChangePassword from "/react/settings/ChangePassword.jsx"
import Privacy from "/react/settings/Privacy.jsx"
import Connections from "/react/settings/Connections.jsx"
import Sidebar from "/react/settings/Sidebar.jsx"
import ResponseMessage from "/react/settings/ResponseMessage.jsx"
import AppContext from "/react/settings/AppContext.js"

function App () {

    const defaultBoard = <Privacy />

    const [board, setBoard] = useState(defaultBoard)
    const [message, setMessage] = useState(false)

    const getPage = (page) => {
        switch (page) {
            case 'changeLogin': var component = <ChangeLogin />; break;
            case 'changeEmail': var component = <ChangeEmail />; break;
            case 'changePassword': var component = <ChangePassword />; break;
            case 'privacy': var component = <Privacy />; break;
            case 'connections': var component = <Connections />; break;
        }
        setBoard(component)
    }

    const displayResponseMessage = (response) => {
        setMessage(<ResponseMessage response={response}/>)
    }
  
    return (
        <AppContext.Provider value={displayResponseMessage}>
            {message}
            <div className="settings container-fluid p-0 d-flex gap end">
                <Sidebar changePage={getPage} />
                {board}
            </div>
        </AppContext.Provider>
    )

}

const root = ReactDOM.createRoot(document.querySelector('#settings'))
root.render(<App />)