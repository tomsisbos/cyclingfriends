import React, { useState, useRef } from 'react'
import useIntersection from '/react/hooks/useIntersection.jsx'
import Day from '/react/journal/Day.jsx'
import Loader from '/react/components/Loader.jsx'

export default function Month ({data, load, activities, yearNumber, monthNumber, daysInMonth, currentDate, setDate}) {
    
    // Define constants
    const ref = useRef()
    const inViewport = useIntersection(ref, '0px')
    const fullViewport = useIntersection(ref, '50%')
    
    // Display a loader if activities have not finished loading
    const [loading, setLoading] = useState(false)
    if (activities == null && loading == false) setLoading(true)
    else if (activities != null && loading == true) setLoading(false)
    
    // Load activity data when enters viewport
    if (inViewport) load(data, yearNumber, monthNumber)

    if (fullViewport) {
        if (yearNumber != currentDate.year || monthNumber != currentDate.month) {
            console.log('date set')
            setDate({year: yearNumber, month: monthNumber + 1})
        }
    }

    // Build day components in accordance with activities held on this day
    const getDays = () => {
        var elements = []
        for (let dayNumber = daysInMonth; dayNumber > 0; dayNumber--) {
            var dayActivities = []
            if (activities != null) Object.values(activities).forEach(activity => {
                if (new Date(activity.datetime).getDate() == dayNumber) dayActivities.push(activity)
            })
            elements.push(<Day key={dayNumber} dayNumber={dayNumber} dayActivities={dayActivities} />)
        }
        return elements
    }

    // Render component
    if (loading) return (
        <div className={"journal-month-" + monthNumber}>
            <div className="journal-month-name" ref={ref}>{monthNumber}月</div>
            <Loader />
        </div>
    )
    else return (
        <div className={"journal-month-" + monthNumber}>
            <div className="journal-month-name" ref={ref}>{monthNumber}月</div>
            <div className="journal-month">{getDays()}</div>
        </div>
    )

}