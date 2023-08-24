import React, { useState } from 'react'
import ReactDOM from 'react-dom/client'
import Rides from '/react/dashboard/Rides.jsx'
import News from '/react/dashboard/News.jsx'

function App () {
  
    return (
        <>
            <Rides />
            <News />
        </>
    )

}

const root = ReactDOM.createRoot(document.querySelector('#dashboard'))
root.render(<App />)