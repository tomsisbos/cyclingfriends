import React from 'react'

export default function RideThumbnail ({id, number, src, onClick}) {
    
    return (
        <div onClick={onClick} className={"dashboard-ride-thumbnail ride-thumb-" + number} id={id} style={{ 
            backgroundImage: `url(` + src + `)`
        }}>
        </div>
    )

}