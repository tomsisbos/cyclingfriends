import { useState, useEffect } from 'react'

export default function useIntersection (element, rootMargin) {
    
    const [isVisible, setState] = useState(false)

    useEffect(() => {
        const observer = new IntersectionObserver(
            ([entry]) => {
                setState(entry.isIntersecting)
            }, { rootMargin }
        )

        element.current && observer.observe(element.current)
        
        return () => element.current && observer.unobserve(element.current)
    }, [element, rootMargin])

    return isVisible
}