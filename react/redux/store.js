import { configureStore } from '@reduxjs/toolkit'
import sessionReducer from '/react/redux/slices/sessionSlice'

export default configureStore({
    reducer: {
        session: sessionReducer
    },
})