import React, { useState, useRef, useEffect } from 'react'
import useIntersection from '/react/hooks/useIntersection.jsx'
import Day from '/react/journal/Day.jsx'
import Loader from '/react/components/Loader.jsx'

export default function Month ({data, load, activities, yearNumber, monthNumber, daysInMonth, setDate}) {
    
    // Define constants
    const ref = useRef()
    const isVisible = useIntersection(ref, '0px')
    
    // Display a loader if activities have not finished loading
    const [loading, setLoading] = useState(false)
    if (activities == null && loading == false) setLoading(true)
    else if (activities != null && loading == true) setLoading(false)
    
    // Load activity data when enters viewport
    useEffect(() => {
        if (isVisible) {
            load(data, yearNumber, monthNumber)
            setDate({year: yearNumber, month: monthNumber})
        }
    }, [isVisible])

    // Build day components in accordance with activities held on this day
    const getDays = () => {
        var elements = []
        for (let dayNumber = daysInMonth; dayNumber > 0; dayNumber--) {
            // Prepare day of week
            var weekDay = (new Date(yearNumber + '-' + monthNumber + '-' + dayNumber)).getDay()
            // Prepare activities
            var dayActivities = []
            if (activities != null) Object.values(activities).forEach(activity => {
                if (new Date(activity.datetime).getDate() == dayNumber) dayActivities.push(activity)
            })
            elements.push(<Day key={dayNumber} dayNumber={dayNumber} weekDay={weekDay} dayActivities={dayActivities} />)
        }
        return elements
    }

    // Render component
    if (loading) return (
        <div className={"journal-month-" + monthNumber} ref={ref}>
            <div className="journal-month-name">{monthNumber}月</div>
            <Loader />
        </div>
    )
    else return (
        <div className={"journal-month-" + monthNumber} ref={ref}>
            <div className="journal-month-name">{monthNumber}月</div>
            <div className="journal-month">{getDays()}</div>
        </div>
    )

}