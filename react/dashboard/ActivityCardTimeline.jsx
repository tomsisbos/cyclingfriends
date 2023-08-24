import React from 'react'

export default function ActivityCardTimeline ({checkpoints}) {
    
    return (
        <div className="activity-card-timeline">
            <div className="activity-card-timeline-stroke"></div>
            <div className="activity-card-timeline-list">
                {checkpoints.map((checkpoint, index) => {
                    if (index == checkpoints.length - 1) var lastDot = ' last-dot'
                    else var lastDot = ''
                    return (
                        <div className={"activity-card-tileline-line" + lastDot} key={checkpoint.id}>
                            <div className="activity-card-timeline-dot"></div>
                            <div className="dashboard-activity-card-tldist">{Math.round(checkpoint.distance * 10) / 10}</div>
                            <div className="dashboard-activity-card-tlname">{checkpoint.name}</div>
                        </div>
                    )
                })}
            </div>
        </div>
    )

}