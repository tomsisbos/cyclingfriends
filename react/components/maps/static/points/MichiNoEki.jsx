
import React from 'react'
import {Layer} from 'react-map-gl'

export default function MichiNoEki () {

    const id = "michi-no-eki"
    const minzoom = 10
    const source = 'composite'
    const sourceLayer = 'poi_label'

    const michiNoEki = {
        'id': 'michi-no-eki',
        'layout': {
            'icon-image': '_icon-' + id,
            'icon-size': [
                "interpolate",
                ["linear"],
                ["zoom"],
                minzoom,
                0.8,
                20,
                3
            ]
        },
        'paint': {
            'icon-opacity': [
                "case",
                ["boolean", ["feature-state", "hover"], false],
                0.5,
                1
            ]
        },
        'filter': [
            "match",
                [
                    "slice",
                    ["get", "name"],
                    0,
                    3
                ],
                [
                    "any",
                    "道の駅",
                    "mic",
                    "Mic",
                    "MIC"
                ],
            true,
            false
        ]
    }

    return (
        <Layer id={id} type='symbol' source={source} source-layer={sourceLayer} minzoom={minzoom} {...michiNoEki} />
    )

}