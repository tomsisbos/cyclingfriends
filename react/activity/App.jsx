import React, { useState, useEffect } from 'react'
import ReactDOM from 'react-dom/client'
import axios from 'axios'
import CFSession from '../../public/class/utils/CFSession'
import Header from "/react/activity/Header.jsx"
import ActivityMapView from "/react/activity/ActivityMapView.jsx"
import Timeline from "/react/activity/Timeline.jsx"

const activity_id = document.querySelector('#activity').dataset.activity

const defaultActivityData = {
    altitude_max: null,
    altitude_min: null,
    author: null,
    bike: null,
    checkpoints: [],
    datetime: {
        date: null
    },
    duration: null,
    duration_running: null,
    id: null,
    notes: null,
    notes_privacy: null,
    photos: [],
    privacy: null,
    route: null,
    slope_max: null,
    speed_max: null,
    temperature_avg: null,
    temperature_max: null,
    temperature_min: null,
    title: null,
    user_id: null
}

function App () {

    const [isLoading, setIsLoading] = useState(true)
    const [activityData, setActivityData] = useState(defaultActivityData)
    const [photos, setPhotos] = useState(activityData.photos)
    const [map, setMap] = useState(null)
    const [activityMap, setActivityMap] = useState(null)
    const [session, setSession] = useState({})

    console.log(session)

    // Load activity data on first component rendering
    useEffect(() => {
        axios.get('/api/activity.php?load=' + activity_id).then(response => {
            setIsLoading(false)
            setActivityData(response.data)
            setPhotos(response.data.photos)
            console.log(response.data)
            console.log(activityData)
        })
    }, [])

    // Load session data on first component rendering
    useEffect(() => {
        CFSession.getSession().then(session => {
            console.log(session)
            setSession(session)
        })
    }, [])

    const getFeaturedImage = () => {
        if (activityData.photos.some(e => e.featured)) return activityData.photos.filter(photo => photo.featured)[0]
        else if (activityData.photos.length > 0) return activityData.photos[0]
        else return null
    }
  
    return (
        <>
            <Timeline
                isLoading={isLoading}
                checkpoints={activityData.checkpoints}
                photos={photos}
                map={map}
                activityMap={activityMap}
            />
            <Header
                isLoading={isLoading}
                featuredImage={getFeaturedImage()}
                activityData={activityData}
                session={session}
            />
            <ActivityMapView
                isLoading={isLoading}
                activityData={activityData}
                photos={photos}
                setPhotos={setPhotos}
                setMap={setMap}
                setActivityMap={setActivityMap}
            />
        </>
    )

}

const root = ReactDOM.createRoot(document.querySelector('#activity'))
root.render(<App />)