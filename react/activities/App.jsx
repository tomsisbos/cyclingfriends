import React from 'react'
import ReactDOM from 'react-dom/client'
import Activities from '/react/activities/Activities.jsx'

function App () {
  
    return (
        <>
            <Activities />
        </>
    )

}

const root = ReactDOM.createRoot(document.querySelector('#root'))
root.render(<App />)