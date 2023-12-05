import React, { useEffect, useState } from 'react'
import ReactDOM from 'react-dom/client'
import axios from "axios"
import { useWrapper } from '/react/world/MapProvider'
import SceneryMarkerElement from '/react/components/maps/scenery/SceneryMarkerElement'
import SceneryPopupElement from '/react/components/maps/scenery/SceneryPopupElement'

export default function SceneryLayer () {
    
    const wrapper = useWrapper()
    const [sceneries, setSceneries] = useState([])
    const [existingMarkerObjects, setExistingMarkerObjects] = useState([])

    const zoomMin = 7 // Scenery display minimum zoom level
    const minNumber = 20 // Number of sceneries displayed to try to reach at minimum
    const maxNumber = 40 // Maximum number of sceneries displayed at the same time

    /**
     * Create a marker for a scenery
     * @param {Object} scenery
     * @returns {mapboxgl.marker}
     */
    const createMarker = (scenery) => {

        // Render marker element with a SceneryMarkerElement inside
        const renderMarker = () => {
            const markerElement = document.createElement('div')
            const root = ReactDOM.createRoot(markerElement)
            root.render(<SceneryMarkerElement data={scenery} wrapper={wrapper} />)
            return new mapboxgl.Marker({
                element: markerElement,
                anchor: 'center',
                color: '#5e203c',
                draggable: false,
            })
        }

        // Build marker
        var marker = renderMarker()
        marker.setLngLat([scenery.lng, scenery.lat])

        // Render marker element with a SceneryMarkerElement inside
        const renderPopup = () => {
            const popupElement = document.createElement('div')
            const root = ReactDOM.createRoot(popupElement)
            root.render(<SceneryPopupElement data={scenery} wrapper={wrapper} />)
            return new mapboxgl.Popup({
                element: popupElement,
                anchor: 'center',
                color: '#5e203c',
                draggable: false,
            })
        }
        let popup = renderPopup()
        marker.setPopup(popup)

        return marker
    }

    /**
     * Fetch data of sceneries located inside current bounding box
     * @returns {Promise} scenery data
     */
    const fetchSceneries = () => {
        return new Promise((resolve, reject) => {

            if (wrapper.map.getZoom() > zoomMin) {

                const bounds = wrapper.map.getBounds()
    
                axios.get('api/sceneries/bounds', {
                    params: {
                        bounds: [bounds._ne.lng, bounds._ne.lat, bounds._sw.lng, bounds._sw.lat],
                        limit: maxNumber
                    }
                })
                .then(response => resolve(response.data))
                .catch(error => console.log(error))

            } else resolve([])

        })
    }

    /**
     * Update map markers according to provided sceneries data
     * @param {Object[]} sceneries 
     */
    const updateMapMarkers = (sceneries) => {
        const sceneryIds = sceneries.map((scenery) => scenery.id)
        const newMarkerObjects = []

        // Create a new markerObject for new sceneries
        sceneries.forEach((scenery) => {
            const existingMarkerObject = existingMarkerObjects.find((markerObject) => markerObject.id === scenery.id)
            if (!existingMarkerObject) newMarkerObjects.push({
                id: scenery.id,
                marker: createMarker(scenery)
            })
        })
        
        // Filter what markers to remove and keep
        const markersToKeep = []
        const markersToRemove = []
        for (const markerObject of existingMarkerObjects) {
            (sceneryIds.includes(markerObject.id) ? markersToKeep : markersToRemove).push(markerObject)
        }
        
        // Remove and add necessary markers
        markersToRemove.forEach((markerObject) => markerObject.marker.remove())
        newMarkerObjects.forEach((markerObject) => markerObject.marker.addTo(wrapper.map))
        
        // Update the existingMarkers state with the markers to keep and the ones to add
        setExistingMarkerObjects([ ...markersToKeep, ...newMarkerObjects ])
    }
    
    const handleMoveEnd = async () => setSceneries(await fetchSceneries())

    // On map load, set moveend listener
    useEffect(() => {
        if (wrapper) {
            wrapper.map.on('moveend', handleMoveEnd)
            return () => wrapper.map.off('moveend', handleMoveEnd)
        }
    }, [wrapper])
    
    // On sceneries change, update map markers
    useEffect(() => updateMapMarkers(sceneries), [sceneries])

    return (
        <></>
    )
}