import React, { useState } from 'react'
import Loader from '/react/components/Loader.jsx'

export default function Button ({ text, type, onClick }) {

    const [isLoading, setIsLoading] = useState(false)

    return (
        <button className={"mp-button " + type} onClick={() => onClick(setIsLoading)}>
            { isLoading ?
                <Loader /> :
                text
            }
        </button>
    )

}