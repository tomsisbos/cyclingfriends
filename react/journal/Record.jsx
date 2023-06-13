
import React from 'react'

export default function Record ({data}) {

    const getSizeClass = () => {
        if (data.distance < 25) return 'journal-record x-small'
        else if (data.distance < 50) return 'journal-record small'
        else if (data.distance < 80) return 'journal-record medium'
        else if (data.distance < 110) return 'journal-record large'
        else if (data.distance < 160) return 'journal-record x-large'
        else if (data.distance >= 160) return 'journal-record xx-large'
    }

    return (
        <div className={getSizeClass()} style={{backgroundImage: 'url(' + data.url + ')'}}>
            <div className="journal-activity-title">{data.title}</div>
            <div>{(Math.round(data.distance * 10) / 10)} km</div>
        </div>
    )
}