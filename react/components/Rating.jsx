import React from "react"

export default function Rating () {

    const getStars = () => {
        var stars = []
        for (let i = 0; i < 5; i++) stars.push(<div key={i} id={i} className="star">☆</div>)
        return stars
    }

    return (
        <div className="popup-rating">
            {getStars()}
        </div>
    )

}