
import React from 'react'

export default function Record ({data}) {

    const getSizeClass = () => {
        if (data.route.distance < 25) return 'journal-record x-small'
        else if (data.route.distance < 50) return 'journal-record small'
        else if (data.route.distance < 80) return 'journal-record medium'
        else if (data.route.distance < 110) return 'journal-record large'
        else if (data.route.distance < 160) return 'journal-record x-large'
        else if (data.route.distance >= 160) return 'journal-record xx-large'
    }

    return (
        <div className={getSizeClass()} style={{backgroundImage: 'url(' + data.featured_photo.url + ')'}}>
            <div className="journal-activity-title">{data.title}</div>
            <div>{(Math.round(data.route.distance * 10) / 10)} km</div>
        </div>
    )
}