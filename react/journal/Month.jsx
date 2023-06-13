import React, { useState, useRef, useContext } from 'react'
import AppContext from "/react/journal/AppContext.js"
import useIntersection from '/react/hooks/useIntersection.jsx'
import Day from '/react/journal/Day.jsx'
import Loader from '/react/components/loader.jsx'

export default function Month ({data, load, activities, yearNumber, monthNumber, daysInMonth}) {
    
    // Define constants
    const ref = useRef()
    const inViewport = useIntersection(ref, '0px')
    const fullyInViewport = useIntersection(ref, '50%')
    
    // Display a loader if activities have not finished loading
    const [loading, setLoading] = useState(false)
    if (activities == null && loading == false) setLoading(true)
    else if (activities != null && loading == true) setLoading(false)
    
    // Load activity data when enters viewport
    if (inViewport) load(data, yearNumber, monthNumber)

    // Change displayed year in footer in accordance with currently displayed month
    const setCurrentYear = useContext(AppContext)
    if (fullyInViewport) setCurrentYear(yearNumber)

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