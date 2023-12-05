import React, { useState, useEffect } from 'react'
import axios from 'axios'
import Loader from '/react/components/Loader.jsx'
import InfiniteLoader from '/react/components/InfiniteLoader.jsx'
import ActivityCard from '/react/activities/ActivityCard.jsx'
import MyActivityButtons from '/react/myactivities/MyActivityButtons.jsx'

export default function MyActivities () {

    const activitiesNumber = 12
    const photosNumber = 4
    
    const [loading, setLoading] = useState(false)
    const [activities, setActivities] = useState([])

    const loadData = async () => {
        return new Promise((resolve, reject) => {
            setLoading(true)
            axios('/api/activitiess', {
                params: {
                    user_id: true,
                    activities_number: activitiesNumber,
                    photos_number: photosNumber,
                    offset: activities.length,
                    include_private: true
                }
            }).then(response => {
                var newActivities = activities.slice()
                response.data.forEach(activity => newActivities.push(activity))
                setActivities(newActivities)
                setLoading(false)
                resolve(response.data)
            })
        })
    }

    // Get user activities data at component loading
    useEffect(() => {
        loadData().then((data) => {
        })
    }, [])
  
    if (loading) var loader = <Loader />
    else var loader = ''

    return (
        <>
            <div className="dashboard-activities">
                {activities.map((activity) => {
                    return (
                        <div
                            className='myactivities-wrapper'
                            key={activity.id}
                        >
                            <ActivityCard activity={activity} />
                            <MyActivityButtons setActivities={setActivities} activityId={activity.id} />
                        </div>
                    )
                })}
            </div>
            {loader}
            <InfiniteLoader onReach={loadData} />
        </>
    )

}