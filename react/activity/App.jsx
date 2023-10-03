import React, { useState, useEffect } from 'react'
import ReactDOM from 'react-dom/client'
import axios from 'axios'
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

    // Load activity data on first component rendering
    useEffect(() => {
        axios.get('/api/activity.php?load=' + activity_id).then(response => {
            setIsLoading(false)
            setActivityData(response.data)
            setPhotos(response.data.photos)
            console.log(activityData)
        })
    }, [])

    const getFeaturedImage = () => {
        if (activityData.photos.some(e => e.featured)) return activityData.photos.filter(photo => photo.featured)[0]
        else return activityData.photos[0]
    }
  
    return (
        <>
            <Header
                isLoading={isLoading}
                featuredImage={getFeaturedImage()}
                title={activityData.title}
                date={(new Date(activityData.datetime.date)).toLocaleDateString()}
                author={activityData.author}
                privacy={activityData.privacy} />
            <ActivityMapView
                isLoading={isLoading}
                activityData={activityData}
                photos={photos}
                setPhotos={setPhotos}
                setMap={setMap}
                setActivityMap={setActivityMap}
            />
            <Timeline
                isLoading={isLoading}
                checkpoints={activityData.checkpoints}
                photos={photos}
                map={map}
                activityMap={activityMap}
            />
        </>
    )

}

const root = ReactDOM.createRoot(document.querySelector('#activity'))
root.render(<App />)