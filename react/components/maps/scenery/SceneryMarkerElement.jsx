import React, { useState } from 'react'

export default function SceneryMarkerElement ({ data, wrapper }) {

    const [hovered, setHovered] = useState(false)
    
    // Scale element according to zoom
    const calculateSize = () => {
        var zoom = wrapper.map.getZoom()
        var size = zoom * 3 - 15
        if (size < 15) size = 15
        return size
    }
    
    return (
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
            <div
                className="scenery-marker-before"
                style={hovered ? {opacity: 1} : {opacity: 0}}
            >
                {data.name}
            </div>
            <img
                src={data.thumbnail}
                className="scenery-icon"
            />
        </div>
    )
}