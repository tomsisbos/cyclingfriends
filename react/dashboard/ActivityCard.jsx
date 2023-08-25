import React, { useState } from 'react'
import ActivityCardHeader from '/react/dashboard/ActivityCardHeader.jsx'
import ActivityCardMedia from '/react/dashboard/ActivityCardMedia.jsx'
import ActivityCardText from '/react/dashboard/ActivityCardText.jsx'
import ActivityCardTimeline from '/react/dashboard/ActivityCardTimeline.jsx'

export default function ActivityCard ({activity}) {

    console.log(activity)

    const storageUrl = document.querySelector('#dashboard').dataset.storageurl
    const containerName = 'user-profile-pictures'

    const [data, setData] = useState(activity)

    // Only display timeline if populated
    if (data.checkpoints.length == 2 && data.checkpoints[0].story == '') var timeline = ''
    else var timeline = (
        <ActivityCardTimeline
            checkpoints={data.checkpoints}
        />
    )
    
    // If no photos data, add static map to it
    if (data.photos.length == 0) {
        var newData = { ...data }
        newData.photos.push(data.thumbnail)
        setData(newData)
    }

    // Only append text and timeline if there is data to display
    if (data.checkpoints.length > 2 || data.checkpoints[0].story != '' || data.sceneries.length > 0) var textAndTimeline = (
        <>
            <ActivityCardText
                checkpoints={data.checkpoints}
                sceneries={data.sceneries}
            />
            {timeline}
        </>
    )
    else var textAndTimeline = ''

    // If comment exists, append it
    if (data.comments.length > 0) {
    
        var firstComment = data.comments[0]
        // Define propic src
        if (firstComment.propic) var propicSrc = storageUrl + containerName + '/' + firstComment.propic
        else var propicSrc = '/media/default-profile-' + firstComment.default_propic_id + '.jpg'
        // Define other comments number
        if (data.comments.length > 1) var otherComments = '他' + (data.comments.length - 1) + '件のコメント'
        else var otherComments = ''
        var comments = (
            <div className="dashboard-activity-card-comments">
                <div className="dashboard-activity-card-comment">
                    <a href={"/rider/" + firstComment.user_id}>
                        <img className="activity-card-propic" src={propicSrc}></img>
                    </a>
                    <p dangerouslySetInnerHTML={{__html: firstComment.content}}></p>
                </div>
                {otherComments}
            </div>
        )
    } else var comments = ''
    
    return (
        <div className="dashboard-activity-card bg-container">
            <ActivityCardHeader
                id={data.id}
                title={data.title}
                author_id={data.author_id}
                author_login={data.author_login}
                default_propic_id={data.default_propic_id}
                author_propic={data.author_propic}
                date={data.date}
                distance={data.distance}
                city={data.city}
                prefecture={data.prefecture}
            />
            <div className="dashboard-activity-card-body">
                <ActivityCardMedia
                    id={data.id}
                    photos={data.photos}
                />
                {textAndTimeline}
            </div>
            {comments}
        </div>
    )

}