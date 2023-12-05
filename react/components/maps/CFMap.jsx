
import React, { useState, useEffect, createRef } from 'react'
import { useDispatch, useSelector } from 'react-redux'
import { fetchSession, selectUser } from '/react/redux/slices/sessionSlice'
import Env from "/src/Env.js"
import mapboxgl from 'mapbox-gl'
import { MapProvider } from 'react-map-gl'
import Map, { AttributionControl } from 'react-map-gl'
import Toilets from "./static/points/Toilets"
import VendingMachineDrinks from './static/points/VendingMachineDrinks'
import DrinkingWater from './static/points/DrinkingWater'
import BicycleRentals from './static/points/BicycleRentals'
import Onsens from './static/points/Onsens'
import Konbinis from './static/points/Konbinis'
import MichiNoEki from './static/points/MichiNoEki'
import Rindos from './static/lines/Rindos'
import Cycling from './static/lines/Cycling'
import NoBicycle from './static/lines/NoBicycle'

export default function CFMap ({ children }) {
    
    const dispatch = useDispatch()
    const user = useSelector(selectUser)

    const [center, setCenter] = useState({
        lng: 139.7673068,
        lat: 35.6809591
    })

    // Load user data and set center
    useEffect(() => {
        console.log(user)
        if (!user) dispatch(fetchSession()) // If user doesn't exist, fetch the session data
        else setCenter({
            lng: user.lngLat.lng,
            lat: user.lngLat.lat
        })
    }, [user])

    return (
        <MapProvider>
            <Map
                mapboxAccessToken={Env.mapboxApiKey}
                initialViewState={{
                    longitude: center.lng,
                    latitude: center.lat,
                    zoom: 10
                }}
                mapStyle="mapbox://styles/sisbos/cl07xga7c002616qcbxymnn5z"
                terrain={{
                    source: "mapbox-raster-dem",
                    exaggeration: 1
                }}
                attributionControl={false}
            >

                <Toilets />
                <DrinkingWater />
                <VendingMachineDrinks />
                <BicycleRentals />
                <Onsens />
                <Konbinis />
                <MichiNoEki />

                <Rindos />
                <Cycling />
                <NoBicycle />

                {children}

                <AttributionControl
                    compact={true}
                    customAttribution="© CyclingFriends"
                />
            </ Map>
        </ MapProvider>
    )
}