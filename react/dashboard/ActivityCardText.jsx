import React from 'react'

export default function ActivityCardText ({checkpoints}) {
    
    return (
        <div className="activity-card-text">
            {checkpoints.map((checkpoint) => {
                return <p key={checkpoint.id}>{checkpoint.story}</p>
            })}
        </div>
    )

}