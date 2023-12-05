
import React from 'react'
import {Source, Layer} from 'react-map-gl'
import geojson from '/public/map/sources/compressed_sources/toilets.geojson'

export default function Toilets () {

    const id = "toilets"
    const minzoom = 12

    const layout = {
        'icon-image': "_icon-toilets",
        'icon-size': [
            "interpolate",
            ["linear"],
            ["zoom"],
            12,
            0.6,
            22,
            2
        ]
    }

    const paint = {
        'icon-opacity': [
            "case",
            ["boolean", ["feature-state", "hover"], false],
            0.5,
            1
        ]
    }

    return (
        <Source id={id} type={"geojson"} data={geojson}>
            <Layer type={"symbol"} minzoom={minzoom} layout={layout} paint={paint} />
        </Source>
    )

}