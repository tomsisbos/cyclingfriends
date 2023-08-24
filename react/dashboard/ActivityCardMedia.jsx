import React from 'react'

export default function ActivityCardMedia ({photos}) {

    const storageUrl = document.querySelector('#dashboard').dataset.storageurl
    const containerName = 'activity-photos'
    
    return (
        <div className="activity-card-media">
            {photos.map((photo, index) => {
                if (index == 0) return <img key={index} className="activity-card-media-featured" src={storageUrl + containerName + '/' + photo}></img>
                else return <img key={index} className={"activity-card-media-sub-" + index} src={storageUrl + containerName + '/' + photo}></img>
            })}
        </div>
    )

}