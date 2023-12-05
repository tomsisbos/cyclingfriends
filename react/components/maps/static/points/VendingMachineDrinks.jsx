
import React from 'react'
import {Source, Layer} from 'react-map-gl'
import geojson from '/public/map/sources/compressed_sources/vending-machine-drinks.geojson'

export default function VendingMachineDrinks () {

    const id = "vending-machine-drinks"
    const minzoom = 12.5

    const layout = {
        'icon-image': '_icon-vending-machine',
        'icon-size': [
            "interpolate",
            ["linear"],
            ["zoom"],
            13.5,
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

    const filter = [
        "match",
        ["get", "amenity"],
        ["vending_machine"],
        true,
        false
    ]

    return (
        <Source id={id} type={"geojson"} data={geojson}>
            <Layer type={"symbol"} minzoom={minzoom} layout={layout} paint={paint} filter={filter} />
        </Source>
    )

}