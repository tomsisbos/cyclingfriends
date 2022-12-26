import CFUtils from "/map/class/CFUtils.js"
import RideMap from "/map/class/RideMap.js"
import RidePickMap from "/map/class/RidePickMap.js"
import RideDrawMap from "/map/class/RideDrawMap.js"

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
        ajaxGetRequest ('/api/map.php' + "?get-session=true", (session) => {
            
            // Update map instance properties
            ridePickMap.session = session

            // Get course infos
            var course = session.course
            if (course) {
                if (course.options) ridePickMap.options = course.options
                ridePickMap.data.checkpoints = course.checkpoints

                // Display checkpoints
                if (ridePickMap.data.checkpoints) ridePickMap.displayCheckpoints()
                
            }
            if (ridePickMap.options.sf === true) {
                document.querySelector('.newpickmap-controller-checkbox').querySelector('input').checked = true
            }
            ridePickMap.setToSF()
        } )
        
        ridePickMap.setMode(ridePickMap.mode)

        ridePickMapIsLoaded = true // Prevents from multiple loading

        // Validating course before going to next step
        document.querySelector('#js-pick button#next').addEventListener('click', validateCoursePick, 'capture')


    } else if (method === 'draw' && !rideDrawMapIsLoaded) { // Only load if draw mode selected and if map have not been already loaded once
    
        var rideDrawMap = new RideDrawMap()
        console.log(rideDrawMap)
        var $map = document.getElementById('newDrawMap')

        // Set default layer according to current season
        var map = await rideDrawMap.load($map, 'mapbox://styles/sisbos/cl07xga7c002616qcbxymnn5z')

        rideDrawMapIsLoaded = true

        // Get session information from the server
        ajaxGetRequest ('/api/map.php' + "?get-session=true", async (session) => {

            // Update map instance properties
            rideDrawMap.session = session

            // Get course infos
            var course = session.course
            if (course) {
                if (course.options) {
                    rideDrawMap.options = course.options
                }
                // Update checkpoints and cursor value to the map instance
                rideDrawMap.data.checkpoints = course.checkpoints
                if (course.checkpoints) rideDrawMap.cursor = course.checkpoints.length
            }
            // Display data
            if (rideDrawMap.session.course.myRoutes) {

                // Display route
                await rideDrawMap.loadRoute(rideDrawMap.session.course.myRoutes)

                // Display checkpoints
                rideDrawMap.displayCheckpoints()

            }

            // On change of the select input, display the route
            var selectRoute = document.querySelector('#selectRoute')
            selectRoute.onchange = async () => {
                rideDrawMap.clearMarkers()
                rideDrawMap.hideMkpoints()
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
                // Prevent from adding a marker if a mkpoint or another marker is on the path
                var markerIncludedOnPath = false
                console.log(e)
                e.originalEvent.composedPath().forEach( (element) => {
                    if (element.classList && (element.classList.contains('mapboxgl-marker') || element.classList.contains('mkpoint-marker'))) markerIncludedOnPath = true
                } )
                // Add checkpoint
                if (!markerIncludedOnPath) {
                    var coords = CFUtils.closestLocation([e.lngLat.lng, e.lngLat.lat], map.getSource('route')._data.geometry.coordinates)
                    var lngLat = {lng: coords[0], lat: coords[1]}
                    rideDrawMap.addMarkerOnRoute(lngLat)
                }
            } )

            // Validating course before going to next step
            document.querySelector('#js-draw button#next').addEventListener('click', validateCourseDraw, 'capture')
        } )

    }

    async function validateCoursePick (e) {

        // If no checkpoint have been set
        var $firstMarker = document.querySelector('.checkpoint-marker')
        var markersNumber = map._markers.length
        if (!$firstMarker || (markersNumber === 1 && $firstMarker.innerText == 'S')) {
            e.preventDefault()
            var error = '少なくとも、ライドのスタート地点とゴール地点（又はスタート＆ゴール地点）を設定しなければなりません。'
            if(document.querySelector('.error-block')){
                document.querySelector('.error-block').remove()
            }
            var errorDiv = document.createElement('div')
            errorDiv.classList.add('error-block', 'fullwidth', 'm-0', 'p-3')

            var errorMessage = document.createElement('p')
            errorMessage.innerHTML = error
            errorMessage.classList.add('error-message')
            errorDiv.appendChild(errorMessage)
            document.querySelector('.container').before(errorDiv)
            errorDiv.scrollIntoView()
        
            // Else, validate, send data to API and go to next page
        } else {
            e.preventDefault()
            await ridePickMap.updateMeetingFinishPlace()
            ridePickMap.updateSession( {
                method: 'pick',
                data: {
                    'options': ridePickMap.options
                }
            })
            document.getElementById('form').submit()

        }
        $map.addEventListener('click', removeError, 'once')
    }

    async function validateCourseDraw (e) {
        e.preventDefault()

        // If no route have been selected, display an error message
        if (rideDrawMap.route == undefined) {
            var error = 'ルートが選択されていません。下記のリストからルートを選択してください。'
            if (document.querySelector('.error-block')) {
                document.querySelector('.error-block').remove()
            }
            var errorDiv = document.createElement('div')
            errorDiv.classList.add('error-block', 'fullwidth', 'm-0', 'p-3')
            var errorMessage = document.createElement('p')
            errorMessage.innerHTML = error
            errorMessage.classList.add('error-message')
            errorDiv.appendChild(errorMessage)
            document.querySelector('.container').before(errorDiv)
            errorDiv.scrollIntoView()

        // Else, validate, send data to API and go to next page
        } else {
            // Update meeting place and finish place information (only if not set or having changed)
            const coursedata = {
                'myRoutes': rideDrawMap.route.id,
                'terrain': terrainDiv.innerText,
                'distance': parseFloat(distanceDiv.innerText.substring(0, distanceDiv.innerText.length - 2)),
                'meetingplace': rideDrawMap.route.meetingplace,
                'finishplace': rideDrawMap.route.finishplace,
                'course-description': document.querySelector('#courseDescriptionTextarea').value
            }
            rideDrawMap.updateSession( {
                method: 'draw',
                data: coursedata
            } )
            document.getElementById('form').submit()
        }
        $map.addEventListener('click', removeError, 'once')
    }
    
    function removeError () {
            if(document.querySelector('.error-block')){
                document.querySelector('.error-block').remove()
            }
        }

}