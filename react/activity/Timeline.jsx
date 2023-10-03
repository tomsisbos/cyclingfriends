import React from 'react'
import Loader from "/react/components/Loader.jsx"
import Checkpoint from "/react/activity/Checkpoint.jsx"
import PhotoThumbnail from "/react/activity/PhotoThumbnail.jsx"

export default function Timeline ({ isLoading, photos, checkpoints, map, activityMap }) {

    var checkpointIndex = 0
    
    console.log(photos)

    const getCheckpointElements = (photos) => checkpoints.map(checkpoint => {

        // Filter photos to append to this checkpoint
        var photoNumber = 0
        var photosToAppend = photos.filter(photo => {
            return photo.datetime > checkpoint.datetime && photo.datetime < checkpoints[checkpointIndex + 1].datetime
        })

        checkpointIndex++

        return (
            <div
                className='pg-ac-checkpoint-container'
                key={checkpoint.number}
            >
                <Checkpoint
                    key={checkpoint.number}
                    data={checkpoint}
                />
                <div className="pg-ac-photos-container">
                    {photosToAppend.map(photoToAppend => {
                        photoNumber++
                        return (
                            <div className="pg-ac-photo-container" key={photoNumber}>
                                <div className="pg-ac-photo-specs">
                                    <div className="pg-ac-photo-number">{photoNumber}</div>
                                    <div className="pg-ac-photo-distance">km {Math.round(photoToAppend.distance * 10) / 10}</div>
                                </div>
                                <PhotoThumbnail
                                    key={photoToAppend.url}
                                    data={photoToAppend}
                                    map={map}
                                    activityMap={activityMap}
                                />
                            </div>
                        )
                    })}
                </div>

            </div>)
    })

    return (
        <div className="bg-container p-4">
            {
                isLoading ?
                <Loader /> :
                <div className='pg-ac-summary-container'>
                    <div className='pg-ac-timeline'></div>
                    <div className="pg-ac-checkpoints-container">
                        {getCheckpointElements(photos)}
                    </div>
                </div>
            }
        </div>
    )

}