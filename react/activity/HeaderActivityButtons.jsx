import React from 'react'
import axios from 'axios' 
import Button from "/react/components/Button.jsx"

export default function HeaderActivityButtons ({ id }) {

    const handleDelete = async (setIsLoading) => {
        setIsLoading(true)
        var answer = await openConfirmationPopup('このアクティビティを削除します。宜しいですか？')
        if (answer) {
            setIsLoading(false)
            axios.get('/api/activity.php' + "?delete=" + id, async (login) => {
                window.location.replace('/' + login + '/activities')
            } )
        }
    }

    return (
        <>
            <a href={"/activity/" + id + "/edit"}>
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
    )

}