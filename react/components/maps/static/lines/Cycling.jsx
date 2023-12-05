
import React from 'react'
import {Source, Layer} from 'react-map-gl'

export default function Cycling () {

    const id = "cycling"
    const minzoom = 7
    const sourceUrl = 'mapbox://sisbos.9to38xqk'
    const sourceLayer = 'cycling-dpgl4s'
    
    const cycleLane = {
        'id': 'cycle-lane',
        'paint': {
            'line-color': [
                "interpolate",
                ["linear"],
                ["zoom"],
                9,
                "#6198ff",
                22,
                "#0d53d3"
            ],
            'line-width': [
                "interpolate",
                ["exponential", 1],
                ["zoom"],
                9,
                1,
                22,
                5
            ],
            'line-dasharray': [1, 1]
        },
        'layout': {
            'line-cap': 'butt',
            'line-miter-limit': 2
        },
        'filter': [
            "any", 
            ["match",
                ["get", "bicycle"],
                ["lane"],
                true,
                false
            ],
            ["match",
                ["get", "cycleway"],
                ["lane"],
                true,
                false
            ]
        ]
    }

    const walkPath = {
        'id': 'walk-path',
        'paint': {
            'line-color': [
                "interpolate",
                ["linear"],
                ["zoom"],
                9,
                "hsl(219, 34%, 79%)",
                22,
                "hsl(221, 63%, 62%)"
            ],
            'line-width': [
                "interpolate",
                ["linear"],
                ["zoom"],
                9,
                1,
                22,
                5
            ]
        },
        'filter': [
            "all",
            [
                "match",
                ["get", "highway"],
                ["cycleway"],
                false,
                true
            ],
            [
                "match",
                ["get", "cycleway"],
                ["lane"],
                false,
                true
            ],
            [
                "match",
                ["get", "bicycle"],
                ["lane"],
                false,
                true
            ]
        ]
    }

    const walkPathCase = {
        'id': 'walk-path-case',
        'paint': {
            'line-color': '#f2f0ee',
            'line-width': [
                "interpolate",
                ["linear"],
                ["zoom"],
                9,
                1,
                22,
                2
            ],
            'line-gap-width': [
                "interpolate",
                ["linear"],
                ["zoom"],
                9,
                1,
                22,
                5
            ]
        },
        'filter': [
            "all",
            [
                "match",
                ["get", "highway"],
                ["cycleway"],
                false,
                true
            ],
            [
                "match",
                ["get", "cycleway"],
                ["lane"],
                false,
                true
            ],
            [
                "match",
                ["get", "bicycle"],
                ["lane"],
                false,
                true
            ]
        ]
    }

    const cyclePath = {
        'id': 'cycle-path',
        'paint': {
            'line-color': [
                "interpolate",
                ["linear"],
                ["zoom"],
                9,
                "#6198ff",
                22,
                "#0d53d3"
            ],
            'line-width': [
                "interpolate",
                ["linear"],
                ["zoom"],
                9,
                1,
                22,
                5
            ]
        },
        'filter': [
            "match",
            ["get", "highway"],
            ["cycleway"],
            true,
            false
        ]
    }

    const cyclePathCase = {
        'id': 'cycle-path-case',
        'type': 'line',
        'source': 'cycling',
        'source-layer': 'cycling-dpgl4s',
        'minzoom': 7,
        'paint': {
            'line-color': "#f2f0ee",
            'line-width': [
                "interpolate",
                ["linear"],
                ["zoom"],
                9,
                0.5,
                22,
                2.5
            ],
            'line-gap-width': [
                "interpolate",
                ["linear"],
                ["zoom"],
                9,
                1,
                22,
                2
            ]
        },
        'filter': [
            "match",
            ["get", "highway"],
            ["cycleway"],
            true,
            false
        ]
    }

    const cyclePathCap = {
        'id': 'cycle-path-cap',
        'paint': {
            'line-color': '#fff',
            'line-width': 5,
            'line-color': '#ff5555'
        },
        'filter': ['in', 'name', 'default']
    }

    return (
        <Source id={id} type={"vector"} url={sourceUrl}>
            <Layer type="line" source={id} source-layer={sourceLayer} minzoom={minzoom} {...cycleLane} />
            <Layer type="line" source={id} source-layer={sourceLayer} minzoom={minzoom} {...walkPath} />
            <Layer type="line" source={id} source-layer={sourceLayer} minzoom={minzoom} {...walkPathCase} />
            <Layer type="line" source={id} source-layer={sourceLayer} minzoom={minzoom} {...cyclePath} />
            <Layer type="line" source={id} source-layer={sourceLayer} minzoom={minzoom} {...cyclePathCase} />
            <Layer type="line" source={id} source-layer={sourceLayer} minzoom={minzoom} {...cyclePathCap} />
        </Source>
    )

}