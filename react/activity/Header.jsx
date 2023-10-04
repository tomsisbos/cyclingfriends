import React from 'react'
import Loader from "/react/components/Loader.jsx"
import HeaderActivityButtons from "/react/activity/HeaderActivityButtons.jsx"

export default function Header ({ isLoading, featuredImage, title, date, author, privacy, id }) {

    const getPrivacyTag = (privacy) => {
        if (privacy == 'private') return <p style="background-color: #ff5555" className="tag-light text-light">非公開</p>
        else if (privacy == 'friends_only') return <p style="background-color: #ff5555" className="tag-light text-light">友達のみ</p>
    }

    const tempTitle = document.querySelector('#activity').dataset.title
    const tempFeaturedImageUrl = document.querySelector('#activity').dataset.featuredImageUrl

    if (!featuredImage) featuredImage = tempFeaturedImageUrl

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
                            <HeaderActivityButtons id={id} /> 
                        </div>
                    </div>
                </div> :

                <div className="header" style={{...getBackgroundImageStyle(), height: 30 + 'vh'}}>
                    <div className="header-block" style={{marginLeft: 30, marginRight: 30, marginBottom: 20}}>
                        <div className="header-row">
                            <h2>{title}</h2>
                        </div>
                        <div className="header-row">
                            <p>{date}</p>
                        </div>
                        <div className="header-row">
                            <div className="header-column">
                                <p>{'by '}
                                <a href={"/rider/" + author.id}>{author.login}</a></p>
                            </div>
                            <div className="header-column">
                                {getPrivacyTag(privacy)}
                            </div>
                        </div>
                        <div className="header-row mt-2">
                            <HeaderActivityButtons id={id} /> 
                        </div>
                    </div>
                </div>
            }
        </>
    )

}