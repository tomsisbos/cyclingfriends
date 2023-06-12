
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
        console.log(records)
    }, [])

    const getElements = (records) => {
        if (records.length > 0) var dayType = 'journal-day worked'
        else dayType = 'journal-day rested'
        
        return (
            <div className={dayType}>
                <div className="journal-day-number">{dayNumber + 1}日</div>
                {records}
            </div>
        )
    }
    
    return getElements(records)

}