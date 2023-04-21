import CFUtils from "/map/class/CFUtils.js"
import CFSession from "/map/class/CFSession.js"
import RideCourseHelper from "/scripts/helpers/rides/course.js"
import RideMap from "/map/class/ride/RideMap.js"
import RidePickMap from "/map/class/ride/RidePickMap.js"
import RideDrawMap from "/map/class/ride/RideDrawMap.js"

var ridePickMapIsLoaded = false
var rideDrawMapIsLoaded = false
var formMethodSelect = document.getElementById('formMethodSelect')
var jsPick = document.getElementById('js-pick')
var jsDraw = document.getElementById('js-draw')
// var jsImport = document.getElementById('js-import')

// On method change
formMethodSelect.addEventListener('change', () => {
    // Clear course data
    var rideMap = new RideMap()
    rideMap.clearSession()
    // Update method
    rideMap.updateSession( {
        method: formMethodSelect.value,
        data: {}
    } )
    displayForm()
} )
displayForm()

async function displayForm () {
    
    var method = formMethodSelect.options[formMethodSelect.selectedIndex].value

    if (method === 'pick') {
        jsPick.style.display   = 'block'
        jsDraw.style.display = 'none'
        // jsImport.style.display = 'none'
    } else if (method === 'draw') {
        jsPick.style.display   = 'none'
        jsDraw.style.display = 'block'
        // jsImport.style.display = 'none'
    } else if (method === 'import') {
        jsPick.style.display   = 'none'
        jsDraw.style.display = 'none'
        // jsImport.style.display = 'block'
    }

    if (method === 'pick' && !ridePickMapIsLoaded) { // Only load if pick mode selected and if map have not been already loaded once

        var ridePickMap = new RidePickMap ()
        var $map = document.getElementById('newPickMap')

        // Set default layer according to current season
        var map = await ridePickMap.load($map, 'mapbox://styles/sisbos/cl07xga7c002616qcbxymnn5z')
        map.setTerrain({'source': 'mapbox-dem', 'exaggeration': 0}) // Disable 3D

        // Set cursor to crosshair on first load
        map._canvas.style.cursor = 'crosshair'

        // Set controller menu
        ridePickMap.setController()

        // Get session information from the server
        CFSession.getSession().then(async session => {
            
            // Update map instance properties
            ridePickMap.session = session

            // Get course infos
            var course = session.course
            if (course) {
                if (course.options) ridePickMap.options = course.options
                ridePickMap.data.checkpoints = course.checkpoints

                // Display checkpoints
                if (ridePickMap.data.checkpoints) ridePickMap.displayCheckpoints()
                
                // Display a helper
                await RideCourseHelper.startGuidance(ridePickMap.method)
                
            }
            if (ridePickMap.options.sf === true) {
                document.querySelector('.newpickmap-controller-checkbox').querySelector('input').checked = true
            }
            ridePickMap.setToSF()

            // Add checkpoint on click
            map.on('click', (e) => {
                // Prevent from adding a marker if a scenery or another marker is on the path
                var markerIncludedOnPath = false
                e.originalEvent.composedPath().forEach( (element) => {
                    if (element.classList && (element.classList.contains('mapboxgl-marker') || element.classList.contains('scenery-marker'))) markerIncludedOnPath = true
                } )
                // Add checkpoint
                if (!markerIncludedOnPath) ridePickMap.addMarker(e.lngLat)
            } )
        } )

        ridePickMapIsLoaded = true // Prevents from multiple loading

        // Validating course before going to next step
        document.querySelector('#js-pick button#next').addEventListener('click', (e) => ridePickMap.validateCourse(e), 'capture')


    } else if (method === 'draw' && !rideDrawMapIsLoaded) { // Only load if draw mode selected and if map have not been already loaded once
    
        var rideDrawMap = new RideDrawMap()
        var $map = document.getElementById('newDrawMap')

        // Set default layer according to current season
        var map = await rideDrawMap.load($map, 'mapbox://styles/sisbos/cl07xga7c002616qcbxymnn5z')
        rideDrawMapIsLoaded = true

        // Get session information from the server
        CFSession.getSession().then(async session => {

            // Update map instance properties
            rideDrawMap.session = session

            // Get course infos
            var course = session.course
            if (course) {
                if (course.options) {
                    rideDrawMap.options = course.options
                }
                // Update checkpoints value to the map instance
                rideDrawMap.data.checkpoints = course.checkpoints
            }
            // Display data
            if (rideDrawMap.session.course.myRoutes) {

                // Display route
                await rideDrawMap.loadRoute(rideDrawMap.session.course.myRoutes)

                // Display checkpoints
                rideDrawMap.displayCheckpoints()
                rideDrawMap.treatRouteChange()
                
                // Display a helper
                await RideCourseHelper.startGuidance(rideDrawMap.method)
            }

            // On change of the select input, display the route
            var selectRoute = document.querySelector('#selectRoute')
            selectRoute.onchange = async () => {
                rideDrawMap.clearMarkers()
                rideDrawMap.hideSceneries()
                await rideDrawMap.loadRoute(selectRoute.value)
                rideDrawMap.updateSession( {
                    method: rideDrawMap.method,
                    data: {
                        'route-id': selectRoute.value
                    }
                } )
            }

            // Add checkpoint on click
            map.on('click', 'route', (e) => {
                // Prevent from adding a marker if a scenery or another marker is on the path
                var markerIncludedOnPath = false
                e.originalEvent.composedPath().forEach( (element) => {
                    if (element.classList && (element.classList.contains('mapboxgl-marker') || element.classList.contains('scenery-marker'))) markerIncludedOnPath = true
                } )
                // Add checkpoint
                if (!markerIncludedOnPath) {
                    var coords = CFUtils.closestLocation([e.lngLat.lng, e.lngLat.lat], map.getSource('route')._data.geometry.coordinates)
                    var lngLat = {lng: coords[0], lat: coords[1]}
                    rideDrawMap.addMarkerOnRoute(lngLat)
                }
            } )

            // Validating course before going to next step
            document.querySelector('#js-draw button#next').addEventListener('click', (e) => rideDrawMap.validateCourse(e), 'capture')
        } )

    }

}