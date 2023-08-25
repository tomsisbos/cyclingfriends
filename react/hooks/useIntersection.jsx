import { useState, useEffect } from 'react'

export default function useIntersection (element, rootMargin) {
    
    const [isVisible, setState] = useState(false)

    useEffect(() => {
        const observer = new IntersectionObserver(
            ([entry]) => {
                setState(entry.isIntersecting)
            }, { rootMargin }
        )

        element && observer.observe(element)
        
        return () => observer.unobserve(element)
    }, [])

    return isVisible
}