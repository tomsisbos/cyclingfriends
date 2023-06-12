import React, { useState, useEffect } from 'react'
import Loader from '/react/components/Loader.jsx'
import Month from '/react/journal/Month.jsx'
import axios from 'axios'

export default function Board () {

    const user_id = window.location.href.substring(window.location.href.lastIndexOf('/') + 1)

    const defaultYear = new Date().getFullYear()
    const defaultMonth = new Date().getMonth() + 1

    const [data, setData] = useState({})
    const [months, setMonths] = useState([])
	
    /**
     * Populate data state with activities data corresponding to a new year/month pair
     * @param {int} year The year to query activities for
     * @param {int} month The month to query activities for (!starts from 0)
     */
    const loadDate = (year, month) => {
        var skipUpdate = false
        var newData = data
        if (year in newData) {
            if (month in newData[year]) skipUpdate = true // Don't fetch new data if the year/month pair is already populated
        } else newData[year] = {} // Create a new year property if necessary
        // Query for year/month corresponding data and add it to data
        if (!skipUpdate) axios('/api/activities/journal.php' + '?user_id=' + user_id + '&year=' + year + '&month=' + month).then(response => {
            newData[year][month] = response.data
            setData(newData)
            console.log(data)
            
            var elements = [];
            let keyNumber = 0;
            Object.values(data).forEach(yearData => {
                Object.values(yearData).forEach(monthData => {
                    elements.push(<Month key={keyNumber} activities={monthData} monthNumber={month} daysInMonth={getDaysInMonth(year, month)} />)
                    keyNumber++
                })
            })
            setMonths(elements)
        })
    }

    const getDaysInMonth = (year, month) => {
        console.log(new Date(year, month, 0))
        console.log(new Date(year, month, 0).getDate())
        return new Date(year, month, 0).getDate()
    }

    // Get user activities data at component loading
    useEffect( () => {
        loadDate(defaultYear, defaultMonth)
    }, [])

    return (
        <div className="journal-board">{months}</div>
    )

}