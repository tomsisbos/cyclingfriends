import React, { useState, useEffect } from 'react'
import Loader from "/react/components/Loader.jsx"
import ActivityMap from "/public/class/maps/activity/ActivityMap.js"
import MapView from '/react/components/MapView.jsx'

export default function ActivityMapView ({ isLoading, activityData, photos, setPhotos, setMap, setMapInstance }) {

    const [isMapLoading, setIsMapLoading] = useState(false)

    useEffect(() => {
        if (!isLoading && !isMapLoading) {

            setIsMapLoading(true)

            var $map = document.querySelector('#activityMap')

            // Instantiate activity map
            var activityMap = new ActivityMap(activityData.id)

            // Clean route data architecture to match geojson format
            activityMap.routeData = {
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
                ph.distance = activityMap.getPhotoDistance(ph, activityMap.routeData)
                return ph
            }))

            // Load activity data into map instance
            activityMap.data = activityData

            // Set month property to activity month
            activityMap.month = new Date(activityData.route.time[0]).getMonth() + 1
            activityMap.setSeason()
            
            // Set default layer according to current season
            activityMap.load($map, 'mapbox://styles/sisbos/cl07xga7c002616qcbxymnn5z', activityMap.routeData.geometry.coordinates[0]).then(async map => {

                setMap(map)

                map.setPitch(40)

                // Build controls
                activityMap.addStyleControl()
                activityMap.addOptionsControl()
                activityMap.addRouteControl()

                // Add route layer and paint route properties
                activityMap.setGrabber()
                activityMap.addSources()
                activityMap.addLayers()
                activityMap.addRouteLayer(activityMap.routeData)
                activityMap.profile.generate()
                ///activityMap.displayStartGoalMarkers(activityMap.routeData)
                activityMap.updateDistanceMarkers()
                activityMap.focus(activityMap.routeData).then(() => {
                    activityMap.profile.generate({
                        poiData: {
                            activityCheckpoints: activityMap.data.checkpoints
                        }
                    })
                })
                activityMap.displayCheckpointMarkers()
                await activityMap.displayPhotoMarkers()

                // On click on a photo on the map, grow the photo
                document.querySelectorAll('.pg-ac-map-img').forEach( (img) => {
                    img.addEventListener('click', (e) => {
                        var photoId = e.target.parentElement.dataset.id
                        var photo
                        photos.forEach( (photoData) => {
                            if (photoData.id == photoId) photo = photoData
                        } )
                        map.easeTo( {
                            offset: [0, $map.offsetHeight / 2 - 40],
                            center: activityMap.getPhotoLocation(photo),
                            zoom: 12
                        } )
                        photo.marker.grow()
                    } )
                } )

                // On click on the map, elsewhere than on a photo, reset default marker size
                map.on('click', (e) => {
                    var isImageOnPath
                    e.originalEvent.composedPath().forEach(entry => {
                        if (entry.className == 'pg-ac-map-img') isImageOnPath = true
                    } )
                    if (!isImageOnPath) {
                        activityMap.unselectPhotos()
                    }
                } )

                setMapInstance(activityMap)
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
                        <MapView id="activityMap" />
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