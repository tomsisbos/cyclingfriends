import React, {useState, useEffect} from 'react'
import axios from 'axios'
import Checkbox from '/react/settings/Checkbox.jsx'
import SaveButton from '/react/settings/SaveButton.jsx'

export default function Privacy () {

    // Settings default state
    const [settings, setSettings] = useState( {
        hide_realname: null,
        hide_age: null,
        hide_garmin_activities: null,
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
                    label="実名を公開しない"
                    value={settings.hide_realname}
                    id={'hide_realname'}
                    onChange={handleChange}
                />
			</div>
			<div className="tr-row gap-20 mb-3">
                <Checkbox
                    label="年齢／生年月日を公開しない"
                    value={settings.hide_age}
                    id={'hide_age'}
                    onChange={handleChange}
                />
			</div>
			<div className="tr-row gap-20 mb-3">
                <Checkbox
                    label="Garmin Connectとの接続で同期された新規アクティビティを非公開にする"
                    value={settings.hide_garmin_activities}
                    id={'hide_garmin_activities'}
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