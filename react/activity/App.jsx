import React, { useState, useEffect } from 'react'
import ReactDOM from 'react-dom/client'
import axios from 'axios'
import Header from "/react/activity/Header.jsx"
import Loader from "/react/components/Loader.jsx"

const activity_id = document.querySelector('#activity').dataset.activity

function App () {

    const [isLoading, setIsLoading] = useState(true)

    useEffect(() => {
        axios.get('/api/activity.php?load=' + activity_id).then(response => {
            setIsLoading(false)
            console.log(response)
        })
    }, [])
  
    return (
        <>
            { isLoading ?
                <Loader /> :
                <Header />
            }
        </>
    )

}

const root = ReactDOM.createRoot(document.querySelector('#activity'))
root.render(<App />)