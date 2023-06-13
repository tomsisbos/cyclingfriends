
import React from 'react'

export default function Record ({data}) {

    const style = () => {
        if (data.filename != null) return {backgroundImage: 'url(' + data.url + ')'}
        else return {}
    }

    const getSizeClass = () => {
        if (data.distance < 25) return 'journal-record x-small'
        else if (data.distance < 50) return 'journal-record small'
        else if (data.distance < 80) return 'journal-record medium'
        else if (data.distance < 110) return 'journal-record large'
        else if (data.distance < 160) return 'journal-record x-large'
        else if (data.distance >= 160) return 'journal-record xx-large'
    }

    return (
        <div className={getSizeClass()} style={style()}>
            <div className="journal-activity-title">{data.title}</div>
            <div>{(Math.round(data.distance * 10) / 10)} km</div>
            <a href={'/activity/' + data.id}>ストーリーはこちら</a>
        </div>
    )
}