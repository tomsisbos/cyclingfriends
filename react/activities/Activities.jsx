import React, { useState, useEffect } from 'react'
import axios from 'axios'
import Loader from '/react/components/Loader.jsx'
import InfiniteLoader from '/react/components/InfiniteLoader.jsx'
import ActivityCard from './ActivityCard.jsx'

export default function Activities () {

    const activitiesNumber = 12
    const photosNumber = 4
    
    const [loading, setLoading] = useState(false)
    const [activities, setActivities] = useState([])

    const loadData = async () => {
        return new Promise((resolve, reject) => {
            setLoading(true)
            axios('/api/activitiess', {
                params: {
                    activities_number: activitiesNumber,
                    photos_number: photosNumber,
                    offset: activities.length,
                    activity_min_distance: 15
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
                    return <ActivityCard key={activity.id} activity={activity} />
                })}
            </div>
            {loader}
            <InfiniteLoader onReach={loadData} />
        </>
    )

}