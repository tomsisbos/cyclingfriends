import React, { useState } from 'react'
import ReactDOM from 'react-dom/client'
import Board from "/react/journal/Board.jsx"
import Footer from "/react/journal/Footer.jsx"

function App () {

    const [date, setDate] = useState({
        year: new Date().getFullYear(),
        month: new Date().getMonth() + 1
    })
  
    return (
        <>
            <Board setDate={setDate} />
            <Footer date={date}/>
        </>
    )

}

const root = ReactDOM.createRoot(document.querySelector('#journal'))
root.render(<App />)