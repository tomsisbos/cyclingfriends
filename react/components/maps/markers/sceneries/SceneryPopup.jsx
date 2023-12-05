import React, { useState, useEffect } from 'react'
import { Popup } from 'react-map-gl'
import axios from 'axios'
import Loader from '/react/components/Loader'
import Rating from '/react/components/Rating'
import Tag from '/react/components/Tag'

export default function SceneryPopup ({ data, onClose }) {

    console.log(data)
    
    return (
        <Popup
            longitude={data.lng}
            latitude={data.lat}
            closeOnClick={false}
            onClose={onClose}
            className='marker-popup'
        >
            <div className="popup-img-container" style={{background: `url(${data.thumbnail})`, backgroundPosition: 'center', backgroundSize: 'cover'}}>
                { <Loader />}
                <div className="popup-icons">
                    <div id="target-button" title="この絶景スポットに移動する。">
                        <span className="iconify" data-icon="icomoon-free:target" data-width="20" data-height="20"></span>
                    </div>
                </div>
            </div>
            <div id="popup-content" className="popup-content">
                <div className="d-flex gap">
                    <div className="round-propic-container">
                        <a href={"/rider/" + data.user_id}>
                            <img className="round-propic-img" />
                        </a>
                    </div>
                    <div className="popup-properties">
                        <div className="popup-properties-reference">
                            <div className="popup-properties-name">
                                <a href={"/scenery/" + data.id} target="_blank">{data.name}</a>
                            </div>
                            <div className="popup-properties-location"></div>
                            <Rating />
                            <div className="popup-tags js-tags">
                                {
                                    data.tags && data.tags.map((tag, index) => <Tag key={index} tag={tag} />)
                                }
                            </div>
                        </div>
                    </div>
                </div>
                <div className="popup-description">
                    { data.description}
                </div>
            </div>
        </Popup>
    )
}