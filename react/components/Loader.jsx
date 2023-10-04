import React from 'react'

export default function Loader ({ type = 'spinner' }) {

    const getClass = () => {
        switch (type) {
            case 'spinner': return 'loader-center'
            case 'placeholder': return 'loading-placeholder'
            case 'text-placeholder': return 'loading-text-placeholder'
        }
    }

    return <div className={getClass()}></div>
}