import React from 'react'

export default function RideCard ({id, name, date, text, src}) {
    
    return (
        <a href={"/rides/" + id}>
            <div className="dashboard-ride-card" style={{ 
                backgroundImage: `url(${src})`
            }}>
                <div className="dashboard-ride-card-block">
                    <div className="dashboard-ride-date">{date}</div>
                    <div className="dashboard-ride-title">{name}</div>
                    <div className="dashboard-ride-text" dangerouslySetInnerHTML={{__html: text}}></div>
                </div>
            </div>
        </a>
    )

}