
import React from 'react'
import {Source, Layer} from 'react-map-gl'
import geojson from '/public/map/sources/compressed_sources/konbinis.geojson'

export default function Konbinis () {

    const id = "konbinis"
    const minzoom = 11.5
    const type = 'symbol'
    
    const getExpressionArray = (keyword, name) => {

        var konbiniSearchNames = {
            'seven-eleven':  ["セブン", "sev", "7-E"],
            'family-mart': ["ファミリ",  "Fami", "Fimi", "サークル", "Circ"],
            'lawson': ["ローソン", "Laws", "LAWS"],
            'mini-stop': ["ミニスト", "Mini", "MINI"],
            'daily-yamazaki': ["Dail", "DAIL", "デイリー", "Yama", "ヤマザキ", "YAMA", "ニューヤ"]
        }

        return [keyword, ...konbiniSearchNames[name]]
    }

    const getLayout = (id) => {
        return {
            'icon-image': '_icon-' + id,
            'icon-size': [
                "interpolate",
                ["linear"],
                ["zoom"],
                11.5,
                0.6,
                20,
                3
            ]
        }
    }

    const paint = {
        'icon-opacity': [
            "case",
            ["boolean", ["feature-state", "hover"], false],
            0.5,
            1
        ]
    }

    const getFilter = (id, charNum) => {
        console.log()
        return [
            "match",
            [
                "slice",
                ["get", "name"],
                0,
                charNum
            ],
            getExpressionArray('any', id),
            true,
            false
        ]
    }

    const familyMartFilter = [
        'all',
        ["match",
            ["slice",
                ["get", "name"],
                0,
                4
            ],
            getExpressionArray('any', 'family-mart'),
            true,
            false
        ],
        ["match",
            ["get", "name"],
            ['any',
                "ロッジ",
                "lodge"
            ],
            false,
            true
        ]
    ]

    const compositeFamilyMart = {
        'source-layer': 'poi_label',
        'layout': {
            'icon-image': '_icon-family-mart',
            'icon-size': [
                'interpolate',
                ['linear'],
                ['zoom'],
                11.5,
                0.6,
                20,
                3
            ]
        },
        'paint': {
            'icon-opacity': [
                'case',
                ['boolean', ['feature-state', 'hover'], false],
                0.5,
                1
            ]
        },
        'filter': [
            'all',
            ["match",
                ["slice",
                    ["get", "name"],
                    0,
                    4
                ],
                getExpressionArray('any', 'family-mart'),
                true,
                false
            ],
            ["match",
                ["get", "name"],
                ['any',
                    "ロッジ",
                    "lodge"
                ],
                false,
                true
            ]
        ]
    }

    return (
        <>
            <Source id={id} type={"geojson"} data={geojson}>
                <Layer id={'seven-eleven'} type={type} minzoom={minzoom} layout={getLayout('seven-eleven')} paint={paint} filter={getFilter('seven-eleven', 3)} />
                <Layer id={'family-mart'} type={type} minzoom={minzoom} layout={getLayout('family-mart')} paint={paint} filter={familyMartFilter} />
                <Layer id={'lawson'} type={type} minzoom={minzoom} layout={getLayout('lawson')} paint={paint} filter={getFilter('lawson', 4)} />
                <Layer id={'mini-stop'} type={type} minzoom={minzoom} layout={getLayout('mini-stop')} paint={paint} filter={getFilter('mini-stop', 4)} />
                <Layer id={'daily-yamazaki'} type={type} minzoom={minzoom} layout={getLayout('daily-yamazaki')} paint={paint} filter={getFilter('daily-yamazaki', 4)} />
            </Source>
            <Layer id={'composite-family-mart'} source='composite' type={type} minzoom={minzoom} {...compositeFamilyMart}/>
        </>
    )

}