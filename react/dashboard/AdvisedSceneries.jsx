import React, { useState, useEffect } from 'react'
import axios from 'axios'
import Loader from '/react/components/Loader.jsx'
import SceneryCard from './SceneryCard'

export default function AdvisedSceneries () {

    const [sceneries, setSceneries] = useState([])
    const [loading, setLoading] = useState(true)

    useEffect(() => {
        axios.get('/api/sceneries/advised', {
            params: {
                user_id: 1
            }
        }).then(response => {
            setSceneries(response.data)
            setLoading(false)
        })
    }, [])

    useEffect(() => {
        console.log(sceneries)
    }, [sceneries])
    
    return (
        <div class="advised-sceneries">
            { loading ?
                <Loader /> :
                sceneries.map((scenery, index) => index < 6 && <SceneryCard key={scenery.id} data={scenery} />)
            }
        </div>
    )

}