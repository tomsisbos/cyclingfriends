import React, { useState, createContext } from 'react'
import ReactDOM from 'react-dom/client'
import ChangeEmail from "/react/settings/ChangeEmail.jsx"
import ChangePassword from "/react/settings/ChangePassword.jsx"
import Privacy from "/react/settings/Privacy.jsx"
import Sidebar from "/react/settings/Sidebar.jsx"
import ResponseMessage from "/react/settings/ResponseMessage.jsx"
import AppContext from "/react/settings/AppContext.js"

function App () {

    const [board, setBoard] = useState('default board')
    const [message, setMessage] = useState(false)

    const getPage = (page) => {
        switch (page) {
            case 'changeEmail': var component = <ChangeEmail />; break;
            case 'changePassword': var component = <ChangePassword />; break;
            case 'privacy': var component = <Privacy />; break;
        }
        setBoard(component)
    }

    const displayResponseMessage = (response) => {
        console.log(response)
        setMessage(<ResponseMessage response={response}/>)
    }
  
    return (
        <AppContext.Provider value={displayResponseMessage}>
            {message}
            <div className="container d-flex gap end">
                <Sidebar changePage={getPage} />
                {board}
            </div>
        </AppContext.Provider>
    )

}

const root = ReactDOM.createRoot(document.querySelector('#settings'))
root.render(<App />)