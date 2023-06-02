import React from 'react'
import Loader from '/react/settings/Loader.jsx'

export default function Checkbox ({ id, label, value, onChange }) {
    if (value === null) return <Loader />
    if (value === true) var checked = 'checked'
    else var checked = ''
    return (
        <label>
            <input type="checkbox" id={id} checked={checked} onChange={(e) => onChange(e.target.id)} />
            {label}
        </label>
    )
}