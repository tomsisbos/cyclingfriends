import React from 'react'
import ReactDOM from 'react-dom/client'
import Rides from '/react/dashboard/Rides.jsx'
import News from '/react/dashboard/News.jsx'
import Activities from '/react/dashboard/Activities.jsx'

function App () {
  
    return (
        <>
            <div className="dashboard-header">
                <Rides />
                <News />
            </div>
            <Activities />
        </>
    )

}

const root = ReactDOM.createRoot(document.querySelector('#dashboard'))
root.render(<App />)