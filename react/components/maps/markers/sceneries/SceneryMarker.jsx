
import React, { useState } from 'react'
import { Marker } from 'react-map-gl'
import { useMap } from 'react-map-gl'
import SceneryPopup from './SceneryPopup'

export default function SceneryMarker ({ data, isOpen, togglePopup }) {

    const { current: map } = useMap()
    const [hovered, setHovered] = useState(false)

    // Scale element according to zoom
    const calculateSize = () => {
        if (map) {
            var zoom = map.getZoom()
            var size = zoom * 3 - 15
            if (size < 15) size = 15
            return size
        }
    }

    return (
        <>
            <Marker
                longitude={data.lng}
                latitude={data.lat}
                anchor="center"
                color="#5e203c"
                draggable={false}
                onClick={togglePopup}
            >
                <div
                    className="scenery-marker-before"
                    style={hovered ? { opacity: 1 } : { opacity: 0 }}
                >
                    {data.name}
                </div>
                <div
                    onMouseEnter={() => setHovered(true)}
                    onMouseLeave={() => setHovered(false)}
                    className="scenery-marker"
                    style={{
                        height: calculateSize(),
                        width: calculateSize(),
                        border: (calculateSize / 15) + 'px solid white',
                    }}
                >
                    <img
                        src={data.photos[0]}
                        className="scenery-icon"
                    />
                </div>
            </Marker>
            {isOpen && (
                <SceneryPopup
                    data={data}
                    onClose={() => {
                        console.log('close')
                        togglePopup(false)
                    }}
                />
            )}
        </>
    )
}