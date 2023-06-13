import React from 'react'

export default function Footer ({date}) {
    
    return (
        <div className="journal-footer">
            <div className="journal-subfooter journal-month">
                <div className="journal-day journal-footer-day">日</div>
                <div className="journal-day journal-footer-day">土</div>
                <div className="journal-day journal-footer-day">金</div>
                <div className="journal-day journal-footer-day">木</div>
                <div className="journal-day journal-footer-day">水</div>
                <div className="journal-day journal-footer-day">火</div>
                <div className="journal-day journal-footer-day">月</div>
            </div>
            <div className="journal-footer-date">
                <div className="journal-footer-year">{date.year + '年'}</div>
                <div className="journal-footer-month">{date.month + '月'}</div>
            </div>
        </div>
    )

}