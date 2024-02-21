import React, { useEffect, useRef } from 'react'
import Loader from './Loader'
import useIntersection from '/react/hooks/useIntersection.jsx'

export default function InfiniteLoader ({ onReach, onClick }) {
    
    // Define constants
    const ref = useRef()
    var isVisible = useIntersection(ref, '0px')

    const handleClick = () => {
        if (onClick) onClick()
        else onReach()
    }
    
    // Load data when enters viewport
    useEffect(() => {
        if (isVisible) onReach()
    }, [isVisible])

    // Render component
    return (
        <div className="infinite-loader" ref={ref} onClick={handleClick}>更に表示する...</div>
    )

}