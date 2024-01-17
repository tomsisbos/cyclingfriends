import React, { useState, useEffect } from 'react'
import ReactDOM from 'react-dom/client'
import axios from 'axios'
import CFSession from '../../public/class/utils/CFSession'
import Header from "./Header.jsx"
import ActivityMapView from "./ActivityMapView.jsx"
import Timeline from "./Timeline.jsx"

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
    const [mapInstance, setMapInstance] = useState(null)
    const [session, setSession] = useState({})

    // Load activity data on first component rendering
    useEffect(() => {
        axios.get('/api/activity.php?load=' + activity_id).then(response => {
            setIsLoading(false)
            setActivityData(response.data)
            setPhotos(response.data.photos)
        })
    }, [])

    // Load session data on first component rendering
    useEffect(() => {
        CFSession.getSession().then(session => setSession(session))
    }, [])

    const getFeaturedImage = () => {
        if (activityData.photos.some(e => e.featured)) return activityData.photos.filter(photo => photo.featured)[0]
        else if (activityData.photos.length > 0) return activityData.photos[0]
        else return null
    }

    var youtubeId = document.querySelector("#activity").dataset.youtubeElement

    const getVideoIframe = () => {
        var width = 720
        var height = 405
        return `<iframe class="responsive-iframe" id="ytplayer" type="text/html" width="` + width + `" height="` + height + `" src="https://www.youtube.com/embed/` + youtubeId + `?autoplay=1&list=PLh5FHR57HS40VebyT_ZD5acIwlcJto-b4&listType=playlist&loop=1&modestbranding=1&color=white" frameborder="0" allowfullscreen></iframe>`
    }
  
    return (
        <>
            <Header
                isLoading={isLoading}
                featuredImage={getFeaturedImage()}
                activityData={activityData}
                session={session}
            />
            { // Load youtube video if there is one
            youtubeId && <div className="bg-white text-center pt-1" dangerouslySetInnerHTML={{ __html: getVideoIframe() }}></div>}
            <Timeline
                isLoading={isLoading}
                checkpoints={activityData.checkpoints}
                photos={photos}
                map={map}
                mapInstance={mapInstance}
            />
            <ActivityMapView
                isLoading={isLoading}
                activityData={activityData}
                photos={photos}
                setPhotos={setPhotos}
                setMap={setMap}
                setMapInstance={setMapInstance}
            />
        </>
    )

}

const root = ReactDOM.createRoot(document.querySelector('#activity'))
root.render(<App />)