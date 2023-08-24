import React from 'react'

export default function RideThumbnail ({id, src, onClick}) {
    
    return (
        <div onClick={onClick} className="dashboard-ride-thumbnail" id={id} style={{ 
            backgroundImage: `url(` + src + `)` 
        }}>
        </div>
    )

}