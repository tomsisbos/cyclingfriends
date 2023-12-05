
import React from 'react'
import {Source, Layer} from 'react-map-gl'

export default function Rindos () {

    const id = "rindos"
    const minzoom = 11
    const sourceUrl = 'mapbox://sisbos.c9kguqvi'
    const sourceLayer = 'rindos-b4dkbp'
    
    const rindosPaint = {
        'line-color': '#fff',
        'line-opacity': [
            'interpolate',
            ['linear'],
            ['zoom'],
            11,
            0,
            12,
            1
        ],
        'line-width': [
            'interpolate',
            ['exponential', 1.5],
            ['zoom'],
            11,
            1,
            20,
            6
        ],
        'line-dasharray': [4, 1, 2, 1]
    }

    const rindosCasePaint = {
        'line-opacity': 0,
        'line-width': [
            'interpolate',
            ['exponential', 1.5],
            ['zoom'],
            11,
            5,
            20,
            15
        ]
    }

    const rindosCapPaint = {
        'line-color': '#fff',
        'line-width': 5,
        'line-color': '#ff5555'
    }

    const rindosCapFilter = ['in', 'name', 'default']

    const rindosLabelLayout = {
        'text-field': ["to-string", ["get", "name"]],
        'text-size': [
            "interpolate",
            ["linear"],
            ["zoom"],
            10,
            10,
            20,
            20
        ],
        'text-line-height': 1.2,
        'symbol-placement': 'line',
        'symbol-spacing': 30,
        'text-max-angle': 30,
        //'text-padding': 2
    }

    const rindosLabelsPaint = {
        'text-color': "#000",
        'text-halo-color': "#d6d6d6",
        'text-halo-width': 1,
        'text-halo-blur': 2
    }

    return (
        <Source id={id} type={"vector"} url={sourceUrl}>
            <Layer id='rindos-case' type="line" source={id} source-layer={sourceLayer} minzoom={minzoom} paint={rindosCasePaint} />
            <Layer id='rindos' type="line" source={id} source-layer={sourceLayer} minzoom={minzoom} paint={rindosPaint} />
            <Layer id='rindos-cap' type="line" source={id} source-layer={sourceLayer} minzoom={minzoom} paint={rindosCapPaint} filter={rindosCapFilter} />
            <Layer id='rindos-labels' type="symbol" source={id} source-layer={sourceLayer} minzoom={minzoom} paint={rindosLabelsPaint} layout={rindosLabelLayout} />
        </Source>
    )

}