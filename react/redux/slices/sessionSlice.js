import { createSlice, createAsyncThunk } from '@reduxjs/toolkit'
import axios from 'axios'

// Create an async thunk to fetch user session data
export const fetchSession = createAsyncThunk('session/fetchSession', async () => {
    const response = await axios.get('/api/session') // Replace with your API endpoint
    return response.data
})

// Create a user session slice
const sessionSlice = createSlice({
    name: 'session',
    initialState: { user: null },
    reducers: {},
    extraReducers: (builder) => {
        builder.addCase(fetchSession.fulfilled, (state, action) => {
            state.user = action.payload
        })
    },
})

export const selectUser = (state) => state.session.user

export default sessionSlice.reducer