import React from 'react'
import ReactDOM from 'react-dom/client'
import MyActivities from '/react/myactivities/MyActivities.jsx'

function App () {
  
    return (
        <>
            <MyActivities />
        </>
    )

}

const root = ReactDOM.createRoot(document.querySelector('#root'))
root.render(<App />)