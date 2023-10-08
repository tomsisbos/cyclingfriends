import React from 'react'
import LikeButton from '/react/components/LikeButton.jsx'

const connectedUserId = parseInt(document.querySelector('#root').dataset.connectedUserId)

export default function ActivityCardHeader ({id, title, author_id, author_login, default_propic_id, author_propic, date, distance, city, prefecture, likes}) {

    const storageUrl = document.querySelector('#root').dataset.storageurl
    const containerName = 'user-profile-pictures'

    // Define profile picture src
    if (author_propic) var propicSrc = storageUrl + containerName + '/' + author_propic
    else var propicSrc = '/media/default-profile-' + default_propic_id + '.jpg'
    
    return (
        <div className="activity-card-header-container">
            <div className="activity-card-header">
                <a href={"/rider/" + author_id}>
                    <img className="activity-card-propic" src={propicSrc}></img>
                </a>
                <a href={"/activity/" + id}><div className="activity-card-title">{title}</div></a>
                <div className="activity-card-header-details">
                    <div className="activity-card-date">{date}・{prefecture}{city}</div>
                    <div className="activity-card-login">{Math.round(distance * 10) / 10}km by <a href={"/rider/" + author_id}>{author_login}</a></div>
                </div>
            </div>
            <LikeButton id={id} likes={likes} canLike={connectedUserId != author_id} />
        </div>
    )

}