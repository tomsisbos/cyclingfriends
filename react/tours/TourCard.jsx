import React from 'react'

export default function TourCard ({ data }) {

    const date = new Date(data.date)

    console.log(data)

    const getWeekDay = (number) => {
        switch (number) {
            case 0: return '日'
            case 1: return '月'
            case 2: return '火'
            case 3: return '水'
            case 4: return '木'
            case 5: return '金'
            case 6: return '土'
        }
    }

    const getTerrainIcon = (number) => {
        switch (number) {
            case 1: return "/media/flat.svg";
            case 2: return '/media/smallhills.svg';
            case 3: return '/media/hills.svg';
            case 4: return '/media/mountain.svg';
        }
    }
    
    return (
        <div className="rd-cd-card">

            <div className="first">
                <div className="cd-status">
                    <div className={'inner ' + data.status_class}>
                        {data.status}
                        {(new Date(data.entry_start).getTime() < Date.now() && new Date(data.entry_end).getTime() > Date.now()) && <div className="cd-participants">{'現在' + data.participants_number + '名'}</div>}
                    </div>
                </div>
                <div className="cd-year">{date.getFullYear()}.</div>
                <div className="cd-month">{date.getMonth() + 1}.</div>
                <div className="cd-day">{date.getDate() + '（' + getWeekDay(date.getDay()) + '）' }</div>
            </div>

            <a className={
                data.featured_image ?
                "cd-title with-img" :
                "cd-title"
            } 
            style={ data.featured_image && { backgroundImage: "url(" + data.featured_image + ")"} }
                href={"/ride/" + data.id}>
                { data.map_thumbnail && <img className="cd-map-thumbnail" src={data.map_thumbnail}/> }
                <div className="cd-title-text">{data.name}</div>
            </a>

            <div className='third'>
                <div className="cd-place">{data.meeting_place}</div>
                <div className="cd-distance">{data.distance + ' km'}</div>
                <div className="cd-terrain"><img className="terrain-icon" src={getTerrainIcon(data.terrain)} /></div>
                <div className="cd-price">¥{data.price}</div>
            </div>
        </div>
    )

}