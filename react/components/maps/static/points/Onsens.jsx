
import React from 'react'
import {Source, Layer} from 'react-map-gl'
import geojson from '/public/map/sources/compressed_sources/onsens.geojson'

export default function Onsens () {

    const id = "onsens"

    const onsensLayout = {
        'icon-image': '_icon-onsen',
        'icon-size': [
            "interpolate",
            ["linear"],
            ["zoom"],
            13,
            1,
            20,
            2
        ]
    }
    const onsensPaint = {
        'icon-opacity': [
            "case",
            ["boolean", ["feature-state", "hover"], false],
            0.5,
            1
        ]
    }
    
    const onsensFilter = [
        "match",
        ["get", "bath:type"],
        ["foot_bath"],
        false,
        true
    ]

    const footbathsLayout = {
        'icon-image': '_icon-footbath'
    }

    const footbathsPaint = {
        'icon-opacity': [
            'case',
            ['boolean', ['feature-state', 'hover'], false],
            0.5,
            1
        ]
    }
    
    const footbathsFilter = [
        "match",
        ["get", "bath:type"],
        ["foot_bath"],
        true,
        false
    ]

    return (
        <Source id={id} type={"geojson"} data={geojson}>
            <Layer id={'onsens'} type={"symbol"} minzoom={12} layout={onsensLayout} paint={onsensPaint} filter={onsensFilter} />
            <Layer id={'footbaths'} type={"symbol"} minzoom={13} layout={footbathsLayout} paint={footbathsPaint} filter={footbathsFilter} />
        </Source>
    )

}