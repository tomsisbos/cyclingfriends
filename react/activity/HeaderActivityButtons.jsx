import React from 'react'
import axios from 'axios' 
import { Icon } from '@iconify/react'
import Twitter from '/public/class/social/Twitter.js'
import Button from "/react/components/Button.jsx"

const twitterUsername = document.querySelector('#activity').dataset.twitterUsername
const twitterName = document.querySelector('#activity').dataset.twitterName
const twitterProfileImage = document.querySelector('#activity').dataset.twitterProfileImage
const twitterAuthUrl = document.querySelector('#activity').dataset.twitterAuthUrl

export default function HeaderActivityButtons ({ activityData, session }) {

    const handleDelete = async (setIsLoading) => {
        var answer = await openConfirmationPopup('このアクティビティを削除します。宜しいですか？')
        if (answer) {
            setIsLoading(true)
            axios.get('/api/activity.php' + "?delete=" + activityData.id).then(response => {
                window.location.href = '/myactivities'
            } )
        }
    }

    const postTweet = () => {
        var twitter = new Twitter(activityData, twitterName, twitterUsername, twitterProfileImage)
        twitter.openTwitterModal()
    }

    const photoAlbumUrl = document.querySelector("#activity").dataset.photoAlbumUrl

    return (
        <>
            { activityData.author && session.id == activityData.author.id &&
                <>
                    <a href={"/activity/" + activityData.id + "/edit"}>
                        <Button
                            text="編集"
                            type="admin"
                            onClick={() => <></>}
                        />
                    </a>
                    <Button
                        text="削除"
                        type="danger"
                        onClick={handleDelete}
                    />
                </>
            }

            { session.id &&
                (activityData.author && twitterAuthUrl ?
                    <a href={twitterAuthUrl}>
                        <Button
                            text={<Icon icon='mdi:twitter' />}
                            type="twitter"
                            onClick={() => <></>}
                        />
                    </a> :
                    <Button
                        text={<Icon icon='mdi:twitter' height={18} />}
                        type="twitter"
                        onClick={postTweet}
                    />)
            }

            { photoAlbumUrl && <a href={photoAlbumUrl}><Button text={'フォトアルバム'} onClick={() => {return <></>}} /></a>}
        </>
    )

}