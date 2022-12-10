import React from 'react'

export default function Checkbox ({ id, label, value, onChange }) {
    if (value === true) var checked = 'checked'
    else var checked = ''
    return (
        <label>
            <input type="checkbox" id={id} checked={checked} onChange={(e) => onChange(e.target.id)} />
            {label}
        </label>
    )
}