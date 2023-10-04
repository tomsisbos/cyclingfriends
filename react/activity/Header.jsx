import React from 'react'
import Loader from "/react/components/Loader.jsx"

export default function Header ({ isLoading, featuredImage, title, date, author, privacy }) {

    const getPrivacyTag = (privacy) => {
        if (privacy == 'private') return <p style="background-color: #ff5555" className="tag-light text-light">非公開</p>
        else if (privacy == 'friends_only') return <p style="background-color: #ff5555" className="tag-light text-light">友達のみ</p>
    }

    const tempTitle = document.querySelector('#activity').dataset.title
    const tempFeaturedImageUrl = document.querySelector('#activity').dataset.featuredImageUrl

    return (
        <>
            {
                isLoading ?

                <div className="header bg-container" style={{height: 30 + 'vh', backgroundImage: `url(` + tempFeaturedImageUrl + `)`, backgroundSize: 'cover', height: 250}}>
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
                            <Loader type="text-placeholder" />
                        </div>
                    </div>
                </div> :

                <div className="header" style={{backgroundImage: `url(` + featuredImage.url + `)`, backgroundSize: 'cover', height: 250}}>
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
                            [ActivityButtons]
                        </div>
                    </div>
                </div>
            }
        </>
    )

}