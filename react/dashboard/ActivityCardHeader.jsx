import React from 'react'

export default function ActivityCardHeader ({id, title, author_id, author_login, author_propic, date, city, prefecture}) {

    const storageUrl = document.querySelector('#dashboard').dataset.storageurl
    const containerName = 'user-profile-pictures'
    
    return (
        <div className="activity-card-header">
            <a href={"/activity/" + id}><div className="activity-card-title">{title}</div></a>
            <a href={"/rider/" + author_id}>
                <img className="activity-card-propic" src={storageUrl + containerName + '/' + author_propic}></img>
            </a>
            <div className="activity-card-header-details">
                <div className="activity-card-date">{date}・{prefecture}{city}</div>
                <div className="activity-card-login">by {author_login}</div>
            </div>
        </div>
    )

}