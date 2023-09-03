import React, { useEffect, useRef } from 'react'
import useIntersection from '/react/hooks/useIntersection.jsx'

export default function InfiniteLoader ({onReach}) {
    
    // Define constants
    const ref = useRef()
    var isVisible = useIntersection(ref, '0px')

    const handleClick = () => {
        onReach()
    }
    
    // Load data when enters viewport
    useEffect(() => {
        if (isVisible) onReach()
    }, [isVisible])

    // Render component
    return <div className="infinite-loader" ref={ref} onClick={handleClick}>更に表示する...</div>

}