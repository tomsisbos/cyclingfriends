
import React, {useState, useEffect} from 'react'
import Record from '/react/journal/Record.jsx'

export default function Day ({dayActivities, dayNumber}) {
    
    const [records, setRecords] = useState([])

    useEffect( () => {
        var elements = []
        for (let number = 0; number < dayActivities.length; number++) {
            elements.push(
                <Record key={number} data={dayActivities[number]} />
            )
        }
        setRecords(elements)
    }, [])

    const getElements = (records) => {
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
    
    return getElements(records)

}