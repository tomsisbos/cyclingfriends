import React from 'react'

export default function Footer ({date}) {
    
    return (
        <div className="journal-footer">
            <div className="journal-footer-year">{date.year + '年'}</div>
            <div className="journal-footer-month">{date.month + '月'}</div>
        </div>
    )

}