import React, { useState, useEffect } from 'react'
import axios from 'axios'
import Loader from '/react/components/Loader.jsx'
import RideCard from '/react/dashboard/RideCard.jsx'
import RideThumbnail from '/react/dashboard/RideThumbnail.jsx'

export default function Rides () {

    const ridesNumber = 3
    const storageUrl = document.querySelector('#dashboard').dataset.storageurl
    const containerName = 'checkpoint-images'
    
    const [loading, setLoading] = useState(false)
    const [rides, setRides] = useState([])
    const [highlightRide, setHighlightRide] = useState(null)

    var number = 0

    const initialize = async () => {
        return new Promise((resolve, reject) => {
            setLoading(true)
            axios('/api/dashboard.php?task=rides&number=' + ridesNumber).then(response => {
                setRides(response.data)
                setHighlightRide(response.data[0].id)
                setLoading(false)
                resolve(response.data)
            })
        })
    }

    const handleClick = (e) => {
        setHighlightRide(e.target.id)
    }

    // Get user activities data at component loading
    useEffect(() => {
        initialize().then((data) => {
        })
    }, [])
  
    if (loading) return <Loader type="placeholder" />
    else return (
        <div className="dashboard-rides">
            {
                rides.map((ride) => {
                    if (ride.id == highlightRide) return <RideCard key={ride.id} id={ride.id} name={ride.name} date={ride.date} text={ride.description} entry_start={ride.entry_start} entry_end={ride.entry_end} src={storageUrl + containerName + '/' + ride.featured_image} />
                    else {
                        number++
                        return <RideThumbnail key={ride.id} number={number} id={ride.id} src={storageUrl + containerName + '/' + ride.featured_image} onClick={handleClick} />
                    }
                })
            }
        </div>
    )

}