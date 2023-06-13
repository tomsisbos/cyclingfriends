
import React, {useState, useEffect} from 'react'
import Record from '/react/journal/Record.jsx'

export default function Day ({dayActivities, dayNumber}) {

    const getRecords = () => {
        var records = []
        for (let number = 0; number < dayActivities.length; number++) {
            records.push(
                <Record key={number} data={dayActivities[number]} />
            )
        }

        if (records.length > 0) return (
            <div className="journal-day worked">
                <div className="journal-day-number">{dayNumber}日</div>
                {records}
            </div>
        )
        else return (
            <div className="journal-day rested">
                <div className="journal-day-number">{dayNumber}日</div>
            </div>
        )
    }
    
    return getRecords()

}