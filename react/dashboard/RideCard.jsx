import React from 'react'

export default function RideCard ({id, name, date, text, src, entry_start, entry_end}) {

    const getEntryWarning = (entry_start, entry_end) => {
        if (new Date() > new Date(entry_start)) {
            var remainingDays = Math.ceil(Math.abs((new Date(entry_end)) - (new Date())) / (1000 * 60 * 60 * 24))
            if (remainingDays > 5) var tagColor = 'tag-darkgreen'
            else var tagColor = 'tag-darkred'
            return <div className={tagColor}>{'残り' + remainingDays + '日'}</div>
        }
        else return ''
    }
    
    return (
        <a href={"/ride/" + id}>
            <div className="dashboard-ride-card" style={{ 
                backgroundImage: `url(${src})`
            }}>
                <div className="dashboard-ride-card-block">
                    {getEntryWarning(entry_start, entry_end)}
                    <div className="dashboard-ride-date">{date}</div>
                    <div className="dashboard-ride-title">{name}</div>
                    <div className="dashboard-ride-text" dangerouslySetInnerHTML={{__html: text}}></div>
                </div>
            </div>
        </a>
    )

}