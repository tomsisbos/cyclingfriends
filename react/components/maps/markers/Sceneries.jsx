
import React, { useEffect, useState } from 'react'
import { useMap } from 'react-map-gl'
import axios from 'axios'
import Scenery from './sceneries/SceneryMarker'

export default function Sceneries () {

    const {current: map} = useMap()
    const [sceneries, setSceneries] = useState([])
    const [openPopup, setOpenPopup] = useState(null)

    const zoomMin = 7 // Scenery display minimum zoom level
    const minNumber = 20 // Number of sceneries displayed to try to reach at minimum
    const maxNumber = 40 // Maximum number of sceneries displayed at the same time

    const togglePopup = (sceneryId) => {

        if (openPopup == sceneryId) setOpenPopup(null)
        else setOpenPopup(sceneryId)

    }

    useEffect(() => {

        if (map) {

            updateSceneries()

            map.on('moveend', () => {
                updateSceneries()
            })
        }
        
    }, [])

    /**
     * Fetch data of sceneries located inside current bounding box
     * @returns {Promise} scenery data
     */
    const updateSceneries = () => {

        console.log(map.getZoom())

        if (map.getZoom() > zoomMin) {
            console.log('INZOOM')

            const bounds = map.getBounds()

            axios.get('api/sceneries/bounds', {
                params: {
                    bounds: [bounds._ne.lng, bounds._ne.lat, bounds._sw.lng, bounds._sw.lat],
                    limit: maxNumber
                }
            })
            .then(response => setSceneries(response.data))
            .catch(error => console.log(error))

        } else setSceneries([])
    }
    
    return (
        sceneries.map(scenery => {
            return (
                <Scenery
                    key={scenery.id}
                    data={scenery}
                    isOpen={openPopup == scenery.id}
                    togglePopup={() => togglePopup(scenery.id)}
                />
            )
        })
    )
}