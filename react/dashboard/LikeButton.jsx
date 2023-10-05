import React, { useState } from 'react'
import axios from 'axios'
import { Icon } from '@iconify/react'

export default function LikeButton ({id, likes}) {

    const connectedUserId = parseInt(document.querySelector('#root').dataset.connectedUserId)

    if (likes.includes(connectedUserId)) var isLike = true
    else isLike = false

    const [likesNumber, setLikesNumber] = useState(likes.length)
    const [liked, setLiked] = useState(isLike)

    const toggleLike = () => {

        // Update like button UI
        if (liked) setLikesNumber(likesNumber - 1)
        else setLikesNumber(likesNumber + 1)
        setLiked(!liked)

        // Update likes data
        axios('/api/dashboard.php?task=activity-like&activity_id=' + id).then(response => {
            console.log(response)
        })
    }

    if (liked) var likeClass = 'liked'
    else var likeClass = ''
    
    return (
        <div className="like-button-container">
            <div className={"like-button " + likeClass} onClick={toggleLike}>
                <Icon icon="icon-park-solid:like" />
            </div>
            <div className={"like-counter"}>
                {likesNumber}
            </div>
        </div>
    )

}