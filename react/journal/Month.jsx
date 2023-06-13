import React, { useRef } from 'react'
import useIntersection from '/react/hooks/useIntersection.jsx'
import Day from '/react/journal/Day.jsx'

export default function Month ({data, load, activities, yearNumber, monthNumber, daysInMonth}) {
    
    const ref = useRef();
    const inViewport = useIntersection(ref, '0px')
    if (inViewport) {
        load(data, yearNumber, monthNumber)
        console.log(ref.current)
    }

    const getDays = () => {
        var elements = [];
        for (let dayNumber = daysInMonth; dayNumber > 0; dayNumber--) {
            var dayActivities = []
            Object.values(activities).forEach(activity => {
                if (new Date(activity.datetime).getDate() == dayNumber) dayActivities.push(activity)
            })
            elements.push(<Day key={dayNumber} dayNumber={dayNumber} dayActivities={dayActivities} />)
        }
        return elements
    }

    return (
        <div ref={ref}>
            <div className="journal-month-name">{monthNumber}月</div>
            <div className="journal-month">{getDays()}</div>
        </div>
    )

}