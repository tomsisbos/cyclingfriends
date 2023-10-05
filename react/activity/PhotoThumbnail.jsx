import React from 'react'

export default function PhotoThumbnail ({ data, map, mapInstance }) {

    if (map && map.getContainer()) var growMarker = () => {
        window.scrollTo(0, map.getContainer().offsetTop)
        map.easeTo( {
            offset: [0, map.getContainer().offsetHeight / 2 - 40],
            center: mapInstance.getPhotoLocation(data),
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

