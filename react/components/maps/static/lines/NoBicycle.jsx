
import React from 'react'
import {Source, Layer} from 'react-map-gl'

export default function NoBicycle () {

    const id = "no-bicycle"
    const minzoom = 10
    const sourceUrl = 'mapbox://sisbos.5qhdcfue'
    const sourceLayer = 'no-bicycle-2drz45'
    const sourceRindosUrl = 'mapbox://sisbos.c9kguqvi'
    const sourceRindosLayer = 'rindos-b4dkbp'
    
    const noBicycleMotorways = {
        'id': 'no-bicycle-motorways',
        'paint': {
            'line-color': "#c8ebc6",
            'line-width': [
                "interpolate",
                ["exponential", 1.5],
                ["zoom"],
                5,
                0.75,
                18,
                32
            ]
        },
        'filter': [
            "match",
            ["get", "highway"],
            ["motorway"],
            true,
            false
        ]
    }

    const noBicycleCase = {
        'id': 'no-bicycle-case',
        'paint': {
            'line-color': "#fff",
            'line-width': [
                "interpolate",
                ["linear"],
                ["zoom"],
                8,
                1,
                22,
                5
            ]
        },
        'filter': [
            "match",
            ["get", "highway"],
            ["motorway"],
            false,
            true
        ]
    }

    const noBicycle = {
        'id': 'no-bicycle',
        'paint': {
            'line-color': "#99ffca",
            'line-width': [
                "interpolate",
                ["linear"],
                ["zoom"],
                8,
                1,
                22,
                5
            ],
            'line-dasharray': [2, 2]
        },
        'filter': [
            "match",
            ["get", "highway"],
            ["motorway"],
            false,
            true
        ]
    }

    const noBicycleCap = {
        'id': 'no-bicycle-cap',
        'paint': {
            'line-color': '#fff',
            'line-width': 5,
            'line-color': '#ff5555'
        },
        'filter': ['in', 'id', 'default']
    }

    const noBicycleRindos = {
        'id': 'no-bicycle-rindos',
        'paint': {
            'line-color': "#99ffca",
            'line-width': [
                "interpolate",
                ["linear"],
                ["zoom"],
                8,
                1,
                22,
                5
            ],
            'line-dasharray': [2, 2]
        },
        'filter': [
            "match",
            ["get", "bicycle"],
            ["no"],
            true,
            false
        ]
    }

    const noBicycleRindosCap = {
        'id': 'no-bicycle-rindos-cap',
        'paint': {
            'line-color': "#fff",
            'line-width': [
                "interpolate",
                ["linear"],
                ["zoom"],
                8,
                1,
                22,
                5
            ]
        },
        'filter': [
            "match",
            ["get", "bicycle"],
            ["no"],
            true,
            false
        ]
    }

    return (
        <>
            <Source id={id} type={"vector"} url={sourceUrl}>
                <Layer type="line" source={id} source-layer={sourceLayer} minzoom={minzoom} {...noBicycleMotorways} />
                <Layer type="line" source={id} source-layer={sourceLayer} minzoom={minzoom} {...noBicycleCase} />
                <Layer type="line" source={id} source-layer={sourceLayer} minzoom={minzoom} {...noBicycle} />
                <Layer type="line" source={id} source-layer={sourceLayer} minzoom={minzoom} {...noBicycleCap} />
            </Source>
            <Source id={'rindos'} type={"vector"} url={sourceRindosUrl}>
                <Layer type="line" source={'rindos'} source-layer={sourceRindosLayer} minzoom={minzoom} {...noBicycleRindos} />
                <Layer type="line" source={'rindos'} source-layer={sourceRindosLayer} minzoom={minzoom} {...noBicycleRindosCap} />
            </Source>
        </>
    )

}