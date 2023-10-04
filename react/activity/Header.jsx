import React, { useState, useEffect } from 'react'
import axios from 'axios'
import Loader from "/react/components/Loader.jsx"
import HeaderActivityButtons from "/react/activity/HeaderActivityButtons.jsx"

export default function Header ({ isLoading, featuredImage, activityData, session }) {

    const tempTitle = document.querySelector('#activity').dataset.title
    const tempFeaturedImageUrl = document.querySelector('#activity').dataset.featuredImageUrl
    if (!featuredImage) featuredImage = tempFeaturedImageUrl

    const [profilePicture, setProfilePicture] = useState(null)

    useEffect(() => {
        if (activityData.author && !profilePicture) axios.get('/api/riders/profile-picture.php?user=' + activityData.author.id).then(response => setProfilePicture(response.data))
    }, [activityData])

    const getPrivacyTag = (privacy) => {
        if (privacy == 'private') return <p style="background-color: #ff5555" className="tag-light text-light">非公開</p>
        else if (privacy == 'friends_only') return <p style="background-color: #ff5555" className="tag-light text-light">友達のみ</p>
    }

    const getBackgroundImageStyle = () => {
        if (featuredImage) return {backgroundImage: `url(` + featuredImage + `)`, backgroundSize: 'cover'}
        else return {}
    }

    return (
        <>
            {
                isLoading ?

                <div className="header bg-container" style={{...getBackgroundImageStyle(), height: 30 + 'vh'}}>
                    <div className="header-block" style={{marginLeft: 30, marginRight: 30, marginBottom: 20}}>
                        <div className="d-flex gap-20">
                            <div className="round-propic-container" style={{borderRadius: 60, overflow: 'hidden'}}>
                                { profilePicture ?
                                    <img className="round-propic-img" src={profilePicture} /> :
                                    <Loader type="placeholder" />
                                }
                            </div>
                            <div>
                                <div className="header-row">
                                    <h2>{tempTitle}</h2>
                                </div>
                                <div className="header-row">
                                    <Loader type="text-placeholder" />
                                </div>
                                <div className="header-row">
                                    <Loader type="text-placeholder" />
                                </div>
                                <div className="header-row mt-2">
                                    <HeaderActivityButtons activityData={activityData} session={session} /> 
                                </div>
                            </div>
                        </div>
                    </div>
                </div> :

                <div className="header" style={{...getBackgroundImageStyle(), height: 30 + 'vh'}}>
                    <div className="header-block" style={{marginLeft: 30, marginRight: 30, marginBottom: 20}}>
                        <div className="d-flex gap-20">
                            <a href={"/rider/" + activityData.author.id}>
                                <div className="round-propic-container">
                                    { profilePicture ?
                                        <img className="round-propic-img" src={profilePicture} /> :
                                        <Loader type="placeholder" />
                                    }
                                </div>
                            </a>
                            <div>
                                <div className="header-row">
                                    <h2>{activityData.title}</h2>
                                </div>
                                <div className="header-row">
                                    <p>{activityData.date}</p>
                                </div>
                                <div className="header-row">
                                    <div className="header-column">
                                        <p>{activityData.route.startplace.city + ', ' + (Math.round(activityData.route.distance * 10) / 10) + ' km - ' + 'by '}
                                        <a href={"/rider/" + activityData.author.id}>{activityData.author.login}</a></p>
                                    </div>
                                    <div className="header-column">
                                        {getPrivacyTag(activityData.privacy)}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div className="header-row mt-2">
                            <HeaderActivityButtons activityData={activityData} session={session} /> 
                        </div>
                    </div>
                </div>
            }
        </>
    )

}