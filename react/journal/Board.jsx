import React, { useState, useEffect } from 'react'
import Loader from '/react/components/loader.jsx'
import Month from '/react/journal/Month.jsx'
import axios from 'axios'

export default function Board () {

    const user_id = window.location.href.substring(window.location.href.lastIndexOf('/') + 1)
    const defaultYear = new Date().getFullYear()
    const defaultMonth = new Date().getMonth() + 1
    
    const [loading, setLoading] = useState(false)
    const [elements, setElements] = useState([])

    const initialize = async () => {
        return new Promise((resolve, reject) => {
            setLoading(true)
            axios('/api/activities/journal.php?task=first_activity_date&user_id=' + user_id).then(response => {
                var defaultData = {}
                var inscriptionDate = new Date(response.data)
                const inscriptionYear = inscriptionDate.getFullYear()
                const inscriptionMonth = inscriptionDate.getMonth() + 1
                var yearsPassed = defaultYear - inscriptionYear
                for (let i = 0; i <= yearsPassed; i++) {
                    defaultData[defaultYear - i] = {}
                    if (i == 0) for (let j = 1; j <= defaultMonth; j++) defaultData[defaultYear - i][j] = null // Only build first months for the first year
                    else if (yearsPassed - i == 0) for (let j = 12; j >= inscriptionMonth; j--) defaultData[defaultYear - i][j] = null // Only build last months for the last year
                    else for (let j = 1; j <= 12; j++) defaultData[defaultYear - i][j] = null // Build all months if a complete year
                }
                const newElements = prepareElements(defaultData)
                setElements(newElements)
                setLoading(false)
                resolve(defaultData)
            })
        })
    }
	
    /**
     * Populate data state with activities data corresponding to a new year/month pair
     * @param {int} year The year to query activities for
     * @param {int} month The month to query activities for (!starts from 0)
     */
    const loadDate = async (newData, year, month) => {
        return new Promise((resolve, reject) => {

            // Check for currently existing data
            var skipUpdate = false
            if (year in newData) {
                if (month in newData[year] && newData[year][month] != null) skipUpdate = true // Don't fetch new data if the year/month pair is already populated
            } else newData[year] = {} // Create a new year property if necessary

            // Query for year/month corresponding data and update elements accordingly
            if (!skipUpdate) {
                axios('/api/activities/journal.php?task=activity_data&user_id=' + user_id + '&year=' + year + '&month=' + month).then(response => {
                    newData[year][month] = response.data
                    const newElements = prepareElements(newData)
                    setElements(newElements)
                    resolve(true)
                })
            }
        })
    }
    
    /**
     * Prepare a list of months components to display
     * @param {Object} newData Data to prepare elements for
     * @returns {Month[]}
     */
    const prepareElements = (newData) => {
        var elements = []
        let keyNumber = 0
        for (const [year, yearData] of Object.entries(newData)) {
            for (const [month, monthData] of Object.entries(yearData)) {
                elements.push(<Month data={newData} load={loadDate} key={keyNumber} activities={monthData} yearNumber={year} monthNumber={month} daysInMonth={getDaysInMonth(year, month)} />)
                keyNumber++
            }
        }
        return elements.reverse()
    }

    const getDaysInMonth = (year, month) => {
        return new Date(year, month, 0).getDate()
    }

    // Get user activities data at component loading
    useEffect(() => {
        initialize().then((newData) => loadDate(newData, defaultYear, defaultMonth))
    }, [])

    if (loading) return <Loader />
    else return <div className="journal-board">{elements}</div>

}