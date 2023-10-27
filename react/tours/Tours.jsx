import React, { useState, useEffect } from 'react'
import axios from 'axios'
import Loader from '/react/components/Loader.jsx'
import InfiniteLoader from '/react/components/InfiniteLoader.jsx'
import TourCard from '/react/tours/TourCard.jsx'

export default function Tours () {

    useEffect(() => {

    }, [])

    const toursNumber = 8
    
    const [loading, setLoading] = useState(false)
    const [tours, setTours] = useState([])

    const loadData = async () => {
        console.log(loading)
        if (!loading) return new Promise((resolve, reject) => {
            setLoading(true)
            console.log('Offset :', tours.length)
            axios('/api/rides/calendar.php?task=rides&limit=' + toursNumber + '&offset=' + tours.length).then(response => {
                console.log(response.data)
                setTours(prevTours => [
                    ...prevTours,
                    ...response.data

                ])
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

    return (
        <div className="rd-calendar">
            {
                tours.map(tour => <TourCard
                    key={tour.id}
                    data={tour}
                />)
            }
            {
                loading &&
                <Loader height={200} width='100%' />
            }
            <InfiniteLoader onReach={loadData} />
        </div>
    )

}