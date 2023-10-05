import React from 'react'
import axios from 'axios'
import Button from "/react/components/Button.jsx"

export default function MyActivityButtons ({ activityId, setActivities }) {

    const handleDelete = async (setIsLoading) => {
        var answer = await openConfirmationPopup('このアクティビティを削除します。宜しいですか？')
        if (answer) {
            setIsLoading(true)
            axios.get('/api/activity.php' + "?delete=" + activityId).then(() => {
                setIsLoading(false)
                setActivities(previousActivities => previousActivities.filter(activity => {
                    if (activity.id != activityId) return activity
                }))
            })
        }
    }

    return (
        <div className="append-buttons">
            <a href={"/activity/" + activityId + "/edit"}>
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
        </div>
    )

}