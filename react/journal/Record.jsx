
import React from 'react'

export default function Record ({data}) {

    return (
        <div className="journal-record" style={{backgroundImage: 'url(' + data.featured_photo.url + ')'}}>
            <div class="journal-activity-title">{data.title}</div>
            <div>{(Math.round(data.route.distance * 10) / 10)} km</div>
        </div>
    )
}