import React, { useState, useEffect } from 'react'
import Day from '/react/journal/Day.jsx'

export default function Month ({activities, monthNumber, daysInMonth}) {

    const [days, setDays] = useState([])

    useEffect( () => {
        var elements = [];
        for (let dayNumber = daysInMonth; dayNumber > 0; dayNumber--) {
            var dayActivities = []
            Object.values(activities).forEach(activity => {
                if (new Date(activity.datetime.date).getDate() == dayNumber) dayActivities.push(activity)
            })
            elements.push(<Day key={dayNumber} dayNumber={dayNumber} dayActivities={dayActivities} />)
        }
        setDays(elements)
    }, [])

    return (
        <>
            <div className="journal-month-name">{monthNumber}月</div>
            <div className="journal-month">{days}</div>
        </>
    )

}