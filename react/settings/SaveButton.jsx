import React, { useContext } from 'react'
import axios from 'axios'
import AppContext from'/react/settings/AppContext.js'

export default function SaveButton ({ settings, type = 'settings', text = '保存' }) {

    const displayResponseMessage = useContext(AppContext)

    function saveSettings () {
        settings.type = type
        axios.post('/api/settings.php', settings).then(response => displayResponseMessage(response.data))
    }
    
    return (
        <div onClick={saveSettings} className="btn smallbutton btnright button-primary">
            {text}
        </div>
    )
}