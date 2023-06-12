import React from 'react'
import ReactDOM from 'react-dom/client'
import Board from "/react/journal/Board.jsx"

function App () {
  
    return (
        <Board />
    )

}

const root = ReactDOM.createRoot(document.querySelector('#journal'))
root.render(<App />)