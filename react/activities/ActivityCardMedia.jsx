import React from 'react'

export default function ActivityCardMedia ({id, photos}) {

    const storageUrl = document.querySelector('#root').dataset.storageurl
    const photosContainerName = 'activity-photos'
    const routesContainerName = 'route-thumbnails'
    
    return (
        <a href={"/activity/" + id} className="activity-card-media">
            {photos.map((photo, index) => {
                if (photo.includes('img')) var url = storageUrl + photosContainerName + '/' + photo
                else var url = storageUrl + routesContainerName + '/' + photo
                if (index == 0) return <img key={index} className="activity-card-media-featured" src={url}></img>
                else return <img key={index} className={"activity-card-media-sub n-" + index} src={url}></img>
            })}
        </a>
    )

}