import React from 'react'

export default function PhotoThumbnail ({ data, map, activityMap }) {
    
    var $map = document.querySelector('#activityMap')

    if ($map) var growMarker = () => {
        window.scrollTo(0, $map.offsetTop)
        map.easeTo( {
            offset: [0, $map.offsetHeight / 2 - 40],
            center: activityMap.getPhotoLocation(data),
            zoom: 12
        } )
        data.marker.grow()
    }
    else growMarker = () => <></>

    return (
        <img
            className="pg-ac-photo"
            src={data.url}
            onClick={growMarker}
        ></img>
    )

}

