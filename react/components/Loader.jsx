import React from 'react'

export default function Loader ({ height = null, type = 'spinner' }) {

    const getClass = () => {
        switch (type) {
            case 'spinner': return 'loader-center'
            case 'placeholder': return 'loading-placeholder'
            case 'text-placeholder': return 'loading-text-placeholder'
        }
    }

    const getHeightStyle = () => {
        if (height) return {height: height}
        else return {}
    }

    return (
        <div
            className={getClass()}
            style={{...getHeightStyle()}}
        >
        </div>
    )
}