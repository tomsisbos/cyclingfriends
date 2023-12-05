import React, { useContext, useState, useEffect} from 'react'
import axios from 'axios'
import ConnectionButton from '/react/settings/ConnectionButton.jsx'
import AppContext from'/react/settings/AppContext.js'

export default function Connections () {

    // Settings default state
    // If false, data has not been loaded yet. If true, user is already connected. If string, user is not connected and needs to access the authenticate url provided.
    const [settings, setSettings] = useState( {
        twitter: {
            connected: false,
            authenticateUrl: null
        },
        garmin: {
            connected: false,
            authenticateUrl: null
        }
    } )

    const displayResponseMessage = useContext(AppContext)

    function updateSettings () {
        axios.get('/api/settings.php', {
            params: {
                'connection-settings': true
            },
        }).then(response => {
            setSettings({ ...response.data })
        } )
    }

    // Fetch current settings data from database once at component loading
    useEffect(() => {
        updateSettings()
    }, [])

    // On click on disconnect button, send a disconnection request for this connection type
    const handleDisconnect = (type) => {
        axios.post('/api/settings.php', {
            type: 'disconnections',
            api: type
        }).then(response => {
            displayResponseMessage(response.data)
            updateSettings()
        })
    }

    return (
        <form className="stg-board container d-flex flex-column" method="post">
		
		<h2 className="mb-4">接続設定</h2>

            <h3>Twitter</h3>
			<div className="tr-row align-items-center gap-20 mb-3">
                <ConnectionButton
                    type={'Twitter'}
                    connected={settings.twitter.connected}
                    authenticateUrl={settings.twitter.authenticateUrl}
                    handleDisconnect={handleDisconnect}
                />
			</div>
            
            <h3>Garmin Connect</h3>
            <div className="tr-row align-items-center gap-20 mb-3">
                <ConnectionButton
                    type={'Garmin Connect'}
                    connected={settings.garmin.connected}
                    authenticateUrl={settings.garmin.authenticateUrl}
                    handleDisconnect={handleDisconnect}
                />
            </div>
				
		</form>
    )

}