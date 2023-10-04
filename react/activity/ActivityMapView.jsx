import React, { useState, useEffect } from 'react'
import Loader from "/react/components/Loader.jsx"
import ActivityMap from "/public/class/maps/activity/ActivityMap.js"
import Twitter from '/public/class/social/Twitter.js'

export default function ActivityMapView ({ isLoading, activityData, photos, setPhotos, setMap, setActivityMap }) {

    const [isMapLoading, setIsMapLoading] = useState(false)

    useEffect(() => {
        if (!isLoading && !isMapLoading) {

            setIsMapLoading(true)

            var $map = document.querySelector('#activityMap')

            // Instantiate activity map
            var newActivityMap = new ActivityMap(activityData.id)

            // Clean route data architecture to match geojson format
            newActivityMap.routeData = {
                geometry: {
                    coordinates: activityData.route.coordinates,
                    type: 'LineString'
                },
                properties: {
                    time: activityData.route.time,
                },
                type: 'Feature'
            }

            // Add distance to photos
            setPhotos(photos.sort((a, b) => a.datetime > b.datetime ? 1 : -1).map(ph => {
                ph.distance = newActivityMap.getPhotoDistance(ph, newActivityMap.routeData)
                return ph
            }))

            // Load activity data into map instance
            newActivityMap.data = activityData

            // Set month property to activity month
            newActivityMap.month = new Date(activityData.route.time[0]).getMonth() + 1
            newActivityMap.setSeason()

            // If user is connected to twitter
            var buttonTwitter = document.querySelector('#buttonTwitter')
            if (buttonTwitter && buttonTwitter.dataset.username) {
                var twitter = new Twitter(activityData)
                buttonTwitter.addEventListener('click', () => twitter.openTwitterModal())
            }

            setActivityMap(newActivityMap)
            
            // Set default layer according to current season
            newActivityMap.load($map, 'mapbox://styles/sisbos/cl07xga7c002616qcbxymnn5z', newActivityMap.routeData.geometry.coordinates[0]).then(async mapInstance => {

                setMap(mapInstance)

                mapInstance.setPitch(40)

                // Build controls
                newActivityMap.addStyleControl()
                newActivityMap.addOptionsControl()
                newActivityMap.addRouteControl()

                // Add route layer and paint route properties
                newActivityMap.setGrabber()
                newActivityMap.addSources()
                newActivityMap.addLayers()
                newActivityMap.addRouteLayer(newActivityMap.routeData)
                newActivityMap.profile.generate()
                ///newActivityMap.displayStartGoalMarkers(activityMap.routeData)
                newActivityMap.updateDistanceMarkers()
                newActivityMap.focus(newActivityMap.routeData).then(() => {
                    newActivityMap.profile.generate({
                        poiData: {
                            activityCheckpoints: newActivityMap.data.checkpoints
                        }
                    })
                })
                newActivityMap.displayCheckpointMarkers()
                await newActivityMap.displayPhotoMarkers()

                // On click on a photo on the map, grow the photo
                document.querySelectorAll('.pg-ac-map-img').forEach( (img) => {
                    img.addEventListener('click', (e) => {
                        var photoId = e.target.parentElement.dataset.id
                        var photo
                        photos.forEach( (photoData) => {
                            if (photoData.id == photoId) photo = photoData
                        } )
                        mapInstance.easeTo( {
                            offset: [0, $map.offsetHeight / 2 - 40],
                            center: newActivityMap.getPhotoLocation(photo),
                            zoom: 12
                        } )
                        photo.marker.grow()
                    } )
                } )

                // On click on the map, elsewhere than on a photo, reset default marker size
                mapInstance.on('click', (e) => {
                    var isImageOnPath
                    e.originalEvent.composedPath().forEach(entry => {
                        if (entry.className == 'pg-ac-map-img') isImageOnPath = true
                    } )
                    if (!isImageOnPath) {
                        newActivityMap.unselectPhotos()
                    }
                } )
            })
        }
    }, [isLoading])


    return (
        <>
            {
                isLoading ?

                <>
                    <div id="activityMapContainer">
                        <Loader type="placeholder" />
                    </div>
                    <div id="profileBox" className="p-0 bg-white" style={{height: 22 + 'vh'}}>
                        <canvas id="elevationProfile"></canvas>
                    </div>
                </> :

                <>
                    <div id="activityMapContainer">
                        <div className="cf-map" id="activityMap" loading="lazy"></div>
                        <div className="grabber"></div>
                    </div>
                    <div id="profileBox" className="p-0 bg-white" style={{height: 22 + 'vh'}}>
                        <canvas id="elevationProfile"></canvas>
                    </div>
                </>
            }
        </>
    )

}