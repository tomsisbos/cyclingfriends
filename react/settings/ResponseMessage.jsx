import React from "react"

export default function ResponseMessage ({ response }) {

    if (response.error) {
        var type = "error"
        var message = response.error
    } else if (response.success) {
        var type = "success"
        var message = response.success
    }

    return (
        <div className={type + '-block'} style={{margin: 0 + 'px'}}>
            <p className={type + '-message'} >
                {message}
            </p>
        </div>
    )
	

}