
import React from 'react'
import {Source, Layer} from 'react-map-gl'
import geojson from '/public/map/sources/bicycle-rentals.geojson'

export default function BicycleRentals () {

    const id = "bicycle-rentals"
    const minzoom = 12

    const layout = {
        'icon-image': '_icon-bicycle-rentals',
        'icon-size': [
            "interpolate",
            ["linear"],
            ["zoom"],
            12,
            0.45,
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