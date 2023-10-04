import React from 'react'
import Loader from "/react/components/Loader.jsx"
import Checkpoint from "/react/activity/Checkpoint.jsx"
import PhotoThumbnail from "/react/activity/PhotoThumbnail.jsx"

export default function Timeline ({ isLoading, photos, checkpoints, map, activityMap }) {

    var checkpointIndex = 0

    const getTimeFromStart = (checkpoint) => {
        return new Date((checkpoint.datetime - checkpoints[0].datetime) * 1000)
    }

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
                id={'checkpoint' + checkpoint.number}
                datanumber={checkpoint.number}
                key={checkpoint.number}
            >
                <Checkpoint
                    key={checkpoint.number}
                    data={checkpoint}
                    map={map}
                    getTimeFromStart={getTimeFromStart}
                />
                <div className="pg-ac-photos-container">
                    {photosToAppend.map(photoToAppend => {
                        photoNumber++
                        return (
                            <div className="pg-ac-photo-container" key={photoNumber}>
                                <div className="pg-ac-photo-specs">
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
        <div className="bg-container">
            {
                isLoading ?
                <Loader /> :
                <div className='pg-ac-summary-container p-4'>
                    <div className='pg-ac-timeline'></div>
                    <div className="pg-ac-checkpoints-container">
                        {getCheckpointElements(photos)}
                    </div>
                </div>
            }
        </div>
    )

}