import React from 'react'

export default function Loader ({ type = 'spinner' }) {

    const getClass = () => {
        switch (type) {
            case 'spinner': return 'loader-center'
            case 'placeholder': return 'loading-placeholder'
        }
    }

    return <div className={getClass()}></div>
}