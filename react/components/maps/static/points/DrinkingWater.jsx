
import React from 'react'
import {Source, Layer} from 'react-map-gl'
import geojson from '/public/map/sources/compressed_sources/drinking.geojson'

export default function DrinkingWater () {

    const id = "drinking-water"
    const minzoom = 12.5

    const layout = {
        'icon-image': '_icon-water',
        'icon-size': [
            "interpolate",
            ["linear"],
            ["zoom"],
            12.5,
            0.6,
            22.5,
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

    const filter = [
        "match",
        ["get", "amenity"],
        ["drinking_water"],
        true,
        false
    ]

    return (
        <Source id={id} type={"geojson"} data={geojson}>
            <Layer type={"symbol"} minzoom={minzoom} layout={layout} paint={paint} filter={filter} />
        </Source>
    )

}