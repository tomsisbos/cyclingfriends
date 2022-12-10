import React from 'react'
import axios from 'axios'

export default function SaveButton ({ settings }) {

    function saveSettings () {
        console.log(settings)
        axios.post('/api/settings.php', settings).then(response => {
            console.log(response.data)
        } )
    }
    
    return (
        <div onClick={saveSettings} className="btn smallbutton btnright button-primary">
            Save
        </div>
    )
}