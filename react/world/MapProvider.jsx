import React, { createContext, useContext, useRef, useState, useEffect } from 'react'
import { useDispatch, useSelector } from 'react-redux'
import { fetchSession, selectUser } from '/react/redux/slices/sessionSlice'
import MapWrapper from '/src/class/MapWrapper'

const WrapperContext = createContext()

export function MapProvider({ children }) {
    
    const dispatch = useDispatch()
    const user = useSelector(selectUser)
    const mapContainer = useRef(null)

    const [wrapper, setWrapper] = useState(null)

    const defaultCenter = [139.7673068, 35.6809591]

    console.log('USER', user)

    // Initialize the map instance when this component mounts
    useEffect(() => {

        let instance

        if (!user) dispatch(fetchSession()) // If user doesn't exist, fetch the session data

        if (mapContainer.current) {

            if (user) var center = [user.lngLat.lng, user.lngLat.lat]
            else var center = defaultCenter

            instance = new MapWrapper({
                container: mapContainer.current,
                center,
                zoom: 10,
                preserveDrawingBuffer: true,
                attributionControl: false
            })

            // Dispatch the map instance to the Redux store
            setWrapper(instance)
        }

        // Cleanup when this component unmounts
        return () => {
            instance.unmount()
        }
    }, [user])

    return (
        <WrapperContext.Provider value={wrapper}>
            <div ref={mapContainer} className="mp-map cf-map mapboxgl-map">{children}</div>
        </WrapperContext.Provider>
    )
}

export function useWrapper () {
    return useContext(WrapperContext)
}