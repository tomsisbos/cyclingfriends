import React from 'react'
import { Icon } from '@iconify/react'

export default function Checkpoint ({ data, getTimeFromStart, map }) {
    
    var $map = document.querySelector('#activityMap')

    const focusMarker = () => {
        window.scrollTo(0, $map.offsetTop)
        map.flyTo( {
            center: data.lngLat,
            zoom: 12,
            pitch: 0,
            bearing: 0
        } )
        data.marker.togglePopup()
    }

    const getIcon = (type) => {
        switch (type) {
            case 'Start':
                return <Icon className="iconify" icon="material-symbols:play-circle" iconname="material-symbols:play-circle" style={{color: '#00e06e'}} />
            case 'Landscape':
                return <Icon className="iconify" icon="bxs:landscape" iconname="bxs:landscape" />
            case 'Break':
                return <Icon className="iconify" icon="ic:round-pause-circle" iconname="ic:round-pause-circle" />
            case 'Restaurant':
                return <Icon className="iconify" icon="ion:restaurant" iconname="ion:restaurant" />
            case 'Cafe':
                return <Icon className="iconify" icon="medical-icon:i-coffee-shop" iconname="medical-icon:i-coffee-shop" />
            case 'Attraction':
                return <Icon className="iconify" icon="gis:layer-poi" iconname="gis:layer-poi" />
            case 'Event':
                return <Icon className="iconify" icon="entypo:info-with-circle" iconname="entypo:info-with-circle" />
            case 'Goal':
                return <Icon className="iconify" icon="material-symbols:stop-circle" iconname="material-symbols:stop-circle" style={{color: '#ff5555'}} />
        }
    }

    const getTimeString = (dateInstance) => {
        if (dateInstance.getUTCHours() > 0) {
            if (dateInstance.getUTCMinutes() > 9) return getTimeFromStart(data).getUTCHours() + 'h' + getTimeFromStart(data).getUTCMinutes()
            else return getTimeFromStart(data).getUTCHours() + 'h0' + getTimeFromStart(data).getUTCMinutes()
        } else return getTimeFromStart(data).getUTCMinutes() + ' min'
    }

    return (
        <div>
            <div className="pg-ac-checkpoint-topline" onClick={focusMarker}>
                <div>
                    {getIcon(data.type)} km {' ' + (Math.round(data.distance * 10) / 10) + ' '}
                </div>
                <div className="pg-ac-checkpoint-time">
                    {'(' + getTimeString(getTimeFromStart(data)) + ')'}
                </div>
                
                {data.name && ' - ' + data.name}
            </div>
            {data.story}
        </div>
    )

}