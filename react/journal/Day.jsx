
import React from 'react'
import Record from '/react/journal/Record.jsx'

export default function Day ({dayNumber, weekDay, dayActivities}) {

    // Set a special class to the first day
    const dayClass = () => {
        return ' day-' + weekDay
    }

    const getRecords = () => {
        var records = []
        for (let number = 0; number < dayActivities.length; number++) {
            records.push(
                <div key={number} className="journal-record-box">
                    <Record key={number} data={dayActivities[number]} />
                </div>
            )
        }

        if (records.length > 0) return (
            <div className={"journal-day worked" + dayClass()}>
                <div className="journal-day-number">{dayNumber}日</div>
                {records}
            </div>
        )
        else return (
            <div className={"journal-day rested" + dayClass()}>
                <div className="journal-day-number">{dayNumber}日</div>
            </div>
        )
    }
    
    return getRecords()

}