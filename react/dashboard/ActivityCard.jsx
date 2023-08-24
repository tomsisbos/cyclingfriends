import React from 'react'
import ActivityCardHeader from '/react/dashboard/ActivityCardHeader.jsx'
import ActivityCardMedia from '/react/dashboard/ActivityCardMedia.jsx'
import ActivityCardText from '/react/dashboard/ActivityCardText.jsx'
import ActivityCardTimeline from '/react/dashboard/ActivityCardTimeline.jsx'

export default function ActivityCard ({data}) {

    // Only display timeline if populated
    if (data.checkpoints.length == 2 && data.checkpoints[0].story == '') var timeline = ''
    else var timeline = (
        <ActivityCardTimeline
            checkpoints={data.checkpoints}
        />
    )
    
    return (
        <div className="dashboard-activity-card">
            <ActivityCardHeader
                id={data.id}
                title={data.title}
                author_id={data.author_id}
                author_login={data.author_login}
                author_propic={data.author_propic}
                date={data.date}
                distance={data.distance}
                city={data.city}
                prefecture={data.prefecture}
            />
            <div className="dashboard-activity-card-body">
                <ActivityCardMedia
                    photos={data.photos}
                />
                <ActivityCardText
                    checkpoints={data.checkpoints}
                />
                {timeline}
            </div>
        </div>
    )

}