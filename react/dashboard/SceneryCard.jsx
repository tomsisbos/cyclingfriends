import React from 'react'

export default function SceneryCard ({ data }) {

    const storageUrl = document.querySelector('#root').dataset.storageurl
    const containerName = 'scenery-photos'
    
    return (
        <div className="scenery-card">
            <div className="scenery-name">{data.name}</div>
            <div className="scenery-place">{data.city + ', ' + data.prefecture}</div>
            <div className="scenery-image">
                <img src={storageUrl + containerName + '/' + data.uri}/>
            </div>
        </div>
    )

}