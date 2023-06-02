import React from 'react'
import Loader from '/react/settings/Loader.jsx'

export default function ConnectionButton ({ type, connected, authenticateUrl = false, handleDisconnect}) {

    if (authenticateUrl) {
        
        // If user is connected, display disconnect button
        if (connected) {
            var text = "接続を解除"
            if (connected.length > 0) var label = "@" + connected + 'として接続中'
            else var label = "接続中"
            return (
                <>
                    <div className="btn smallbutton btnright button-primary" onClick={() => handleDisconnect(type)}>
                        {text}
                    </div>
                    <div>
                        {label}
                    </div>
                </>
            )

        // If user is not connected, display connect button
        } else {
            var text = "接続する"
            return (
                <a href={authenticateUrl}>
                    <div className="btn smallbutton btnright button-primary">
                        {text}
                    </div>
                </a>
            )
        }

    } else return <Loader />

}