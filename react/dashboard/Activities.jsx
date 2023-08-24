import React, { useState, useEffect } from 'react'
import axios from 'axios'
import Loader from '/react/components/Loader.jsx'
import ActivityCard from '/react/dashboard/ActivityCard.jsx'

export default function Activities () {

    const activitiesNumber = 12
    const photosNumber = 4
    
    const [loading, setLoading] = useState(false)
    const [activities, setActivities] = useState([])

    const initialize = async () => {
        return new Promise((resolve, reject) => {
            setLoading(true)
            axios('/api/dashboard.php?task=activities&activities_number=' + activitiesNumber + '&photos_number=' + photosNumber).then(response => {
                setActivities(response.data)
                setLoading(false)
                resolve(response.data)
            })
        })
    }

    // Get user activities data at component loading
    useEffect(() => {
        initialize().then((data) => {
        })
    }, [])
  
    if (loading) return <Loader />
    else return (
        <div className="dashboard-activities">
            {activities.map((activity) => {
                return <ActivityCard key={activity.id} data={activity} />
            })}
        </div>
    )

}