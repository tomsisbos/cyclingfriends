import CFUtils from "/class/utils/CFUtils.js"
import CFSession from "/class/utils/CFSession.js"
import RideMap from "/class/maps/ride/RideMap.js"
import RidePickMap from "/class/maps/ride/RidePickMap.js"
import RideDrawMap from "/class/maps/ride/RideDrawMap.js"
import FadeLoader from "/class/loaders/FadeLoader.js"

var ridePickMapIsLoaded = false
var rideDrawMapIsLoaded = false
var formMethodSelect = document.getElementById('formMethodSelect')
var jsPick = document.getElementById('js-pick')
var jsDraw = document.getElementById('js-draw')
// var jsImport = document.getElementById('js-import')

// Prevent any change before data has loaded        
var loader = new FadeLoader('データを取得中...')
loader.start()

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
})
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

        // Disable save&next button before complete loading of data
        var nextButtonPick = document.querySelector('#js-pick button#next')
        nextButtonPick.disabled = true

        var ridePickMap = new RidePickMap ()
        ridePickMap.edit = true
        var $map = document.getElementById('newPickMap')

        // Set default layer according to current season
        var map = await ridePickMap.load($map, 'mapbox://styles/sisbos/cl07xga7c002616qcbxymnn5z')
        map.setTerrain({'source': 'mapbox-dem', 'exaggeration': 0}) // Disable 3D

        // Set cursor to crosshair on first load
        map._canvas.style.cursor = 'crosshair'

        // Set controller menu
        ridePickMap.setController()
            
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

        // Get session information from the server
        CFSession.getSession().then(session => {

            if (loader) loader.stop()
            
            // Update map instance properties
            ridePickMap.session = session

            // Get course infos
            var course = ridePickMap.session['edit-forms'][2]
            ridePickMap.options = course.options
            // Don't repeat the last checkpoint if SF option is true & has the same coordinates as the first checkpoint
            if (course.options.sf == true && CFUtils.compareCoords(course.checkpoints[course.checkpoints.length - 1].lngLat, course.checkpoints[0].lngLat, 3)) course.checkpoints.pop()
            // Update checkpoints to the map instance
            ridePickMap.data.checkpoints = course.checkpoints

            // Prepare data for updating session variable
            var data = {
                checkpoints: course.checkpoints
            }
            // If raw server data, build meetingplace and finishplace geolocation data
            if (typeof course.meetingplace !== 'object') {
                var meetingplace = {
                    geolocation: CFUtils.buildGeolocationFromString(course.meetingplace),
                    lngLat: course.checkpoints[0].lngLat
                }
                if (course.options.sf == false) var finishplace = {
                    geolocation: CFUtils.buildGeolocationFromString(course.finishplace),
                    lngLat: course.checkpoints[course.checkpoints.length - 1].lngLat
                }
                else var finishplace = meetingplace
                data.meetingplace = meetingplace
                data.finishplace = finishplace
            }
            // Update session variable
            ridePickMap.updateSession( {
                method: ridePickMap.method,
                data: data
            } )

            // Display checkpoints
            if (ridePickMap.data.checkpoints) ridePickMap.displayCheckpoints()

            // Check SF option box if necessary
            if (ridePickMap.options.sf === true) {
                document.querySelector('.newpickmap-controller-checkbox').querySelector('input').checked = true
            }
            ridePickMap.setToSF()

            // Validating course before going to next step
            nextButtonPick.disabled = false
            nextButtonPick.addEventListener('click', (e) => ridePickMap.validateCourse(e), 'capture')
        } )

        ridePickMapIsLoaded = true // Prevents from multiple loading


    } else if (method === 'draw' && !rideDrawMapIsLoaded) { // Only load if draw mode selected and if map have not been already loaded once
    
        // Disable save&next button before complete loading of data
        var nextButtonDraw = document.querySelector('#js-draw button#next')
        nextButtonDraw.disabled = true

        var rideDrawMap = new RideDrawMap()
        rideDrawMap.edit = true
        var $map = document.getElementById('newDrawMap')

        // Set default layer according to current season
        var map = await rideDrawMap.load($map, 'mapbox://styles/sisbos/cl07xga7c002616qcbxymnn5z')

        rideDrawMapIsLoaded = true

        // Get session information from the server
        CFSession.getSession().then(async session => {
            
            if (loader) loader.stop()

            // Update map instance properties
            rideDrawMap.session = session
            
            // Get course infos
            var course = rideDrawMap.session['edit-forms'][2]
            rideDrawMap.options = course.options
            // Don't repeat the last checkpoint if SF option is true & has the same coordinates as the first checkpoint
            if (course.options.sf == true && CFUtils.compareCoords(course.checkpoints[course.checkpoints.length - 1].lngLat, course.checkpoints[0].lngLat, 3)) course.checkpoints.pop()
            // Update checkpoints to the map instance
            rideDrawMap.data.checkpoints = course.checkpoints

            // Set select input to currently used route
            var selectRoute = document.querySelector('#selectRoute')
            selectRoute.value = course['route-id']
            rideDrawMap.updateSession( {
                method: rideDrawMap.method,
                data: {
                    'route-id': course['route-id']
                }
            } )
            // On change of the select input, display the route
            selectRoute.onchange = async () => {
                rideDrawMap.clearMarkers()
                rideDrawMap.hideSceneries()
                await rideDrawMap.loadRoute(selectRoute.value, {loader: 'ルート読込中...'})
                await rideDrawMap.sortCheckpoints()
                rideDrawMap.treatRouteChange()
                rideDrawMap.updateSession( {
                    method: rideDrawMap.method,
                    data: {
                        'route-id': selectRoute.value
                    }
                } )
            }

            // Display route
            await rideDrawMap.loadRoute(selectRoute.value, {loader: 'ルート読込中...'})

            // Display checkpoints
            if (rideDrawMap.data.checkpoints) rideDrawMap.displayCheckpoints()
            await rideDrawMap.sortCheckpoints()
            rideDrawMap.treatRouteChange()

            // Add checkpoint on click
            map.on('click', 'route', (e) => {
                // Prevent from adding a marker if a scenery or another marker is on the path
                var markerIncludedOnPath = false
                e.originalEvent.composedPath().forEach( (element) => {
                    if (element.classList && element.classList.contains('mapboxgl-marker')) markerIncludedOnPath = true
                } )
                // Add checkpoint
                if (!markerIncludedOnPath) {
                    var coords = CFUtils.closestLocation([e.lngLat.lng, e.lngLat.lat], map.getSource('route')._data.geometry.coordinates)
                    var lngLat = {lng: coords[0], lat: coords[1]}
                    rideDrawMap.addMarkerOnRoute(lngLat)
                }
            } )

            // Validating course before going to next step
            nextButtonDraw.addEventListener('click', (e) => rideDrawMap.validateCourse(e), 'capture')
            nextButtonDraw.disabled = false
        } )

    }

}