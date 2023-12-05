import React from "react"
import ReactDOM from 'react-dom/client'
import { Provider } from 'react-redux'
import store from '/react/redux/store.js'
import WorldMap from './WorldMap'

function App () {
    return (
        <Provider store={store}>
            <WorldMap />
        </Provider>
    )
}

const root = ReactDOM.createRoot(document.querySelector('#root'))
root.render(<App />)