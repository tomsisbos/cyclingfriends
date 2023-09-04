import React from 'react'

export default function ActivityCardText ({checkpoints, sceneries}) {

    const getText = () => {

        if (checkpoints.length == 2 && checkpoints[0].story == '') return <ul>{sceneries.map(scenery => {
            return <li key={scenery.id}><a href={"/scenery/" + scenery.id}>{scenery.name}</a></li>
        })}</ul>
        else return checkpoints.map((checkpoint) => {
            return <p key={checkpoint.id} dangerouslySetInnerHTML={{__html: checkpoint.story}}></p>
        })
    }
    
    return (
        <div className="activity-card-text">
            {getText()}
        </div>
    )

}