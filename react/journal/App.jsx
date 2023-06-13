import React, { useState } from 'react'
import ReactDOM from 'react-dom/client'
import AppContext from "/react/journal/AppContext.js"
import Board from "/react/journal/Board.jsx"
import Footer from "/react/journal/Footer.jsx"

function App () {

    const [footer, setFooter] = useState()
    
    const setCurrentYear = (year) => setFooter(<Footer year={year} />)
  
    return (
        <AppContext.Provider value={setCurrentYear}>
            <Board />
            {footer}
        </AppContext.Provider>
    )

}

const root = ReactDOM.createRoot(document.querySelector('#journal'))
root.render(<App />)