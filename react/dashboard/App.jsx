import React from 'react'
import ReactDOM from 'react-dom/client'
import Rides from '/react/dashboard/Rides.jsx'
import News from '/react/dashboard/News.jsx'
import Activities from '/react/activities/Activities.jsx'
import AdvisedSceneries from './AdvisedSceneries'

function App () {
  
    return (
        <div className="dashboard" >
            <div className="dashboard-header">
                <Rides />
                <News />
            </div>
            <AdvisedSceneries />
            <Activities />
        </div>
    )

}

const root = ReactDOM.createRoot(document.querySelector('#root'))
root.render(<App />)