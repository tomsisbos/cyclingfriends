import React, { useState, useEffect } from 'react'
import axios from 'axios'
import Loader from '/react/components/Loader.jsx'

export default function Rides () {
    
    const [loading, setLoading] = useState(false)
    const [post, setPost] = useState({})

    const initialize = async () => {
        return new Promise((resolve, reject) => {
            setLoading(true)
            axios('/api/dashboard.php?task=news').then(response => {
                setPost(response.data)
                setLoading(false)
                resolve(response.data)
            })
        })
    }

    // Get user activities data at component loading
    useEffect(() => {
        initialize().then((data) => {
        })
    }, [])
  
    if (loading) return <Loader type="placeholder" />
    else return (
        <div className="dashboard-news">
            <div className="dashboard-news-header">
                <div className="post-datetime">{post.date}</div>
                <div className="post-type"><div className={post.type}>{post.typestring}</div></div>
                <div className="post-title">{post.title}</div>
            </div>
            <div className="post-content" dangerouslySetInnerHTML={{__html: post.content}}></div>
            <div className="dashboard-news-link">詳細は<a href="/news">こちら</a></div>
        </div>
    )

}