import React, {useState, useEffect} from 'react'
import axios from 'axios'
import Checkbox from '/react/settings/Checkbox.jsx'
import SaveButton from '/react/settings/SaveButton.jsx'

export default function Privacy () {

    // Settings default state
    const [settings, setSettings] = useState( {
        hide_on_riders: false,
        hide_on_neighbours: false,
        hide_on_chat: false,
    } )

    // On change, set changed value (=id) to opposite one
    const handleChange = (id) => {
        let newSettings = { ...settings }
        newSettings[id] = !settings[id]
        setSettings(newSettings)
    }

    // Fetch current settings data from database once at component loading
    useEffect( () => {
        axios('/api/settings.php' + '?privacy-settings=true').then(response => {
            setSettings({ ...response.data })
        } )
    }, [])

    return (
        <form className="stg-board container d-flex flex-column" method="post">
		
		<h2 className="mb-4">プライバシー設定</h2>
	
			<div className="tr-row gap-20 mb-3">
                <Checkbox
                    label="Communityページでアカウント情報を表示しない"
                    value={settings.hide_on_riders}
                    id={'hide_on_riders'}
                    onChange={handleChange}
                />
			</div>
			<div className="tr-row gap-20 mb-3">
                <Checkbox
                    label="Neighboursページでアカウント情報を表示しない"
                    value={settings.hide_on_neighbours}
                    id={'hide_on_neighbours'}
                    onChange={handleChange}
                />
			</div>
			<div className="tr-row gap-20 mb-3">
                <Checkbox
                    label="友達以外のダイレクトメッセージを受け付けない"
                    value={settings.hide_on_chat}
                    id={'hide_on_chat'}
                    onChange={handleChange}
                />
			</div>
            <div>
                <SaveButton
                    settings={settings}
                />
            </div>
				
		</form>
    )

}