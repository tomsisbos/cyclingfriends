import CFUtils from '../class/utils/CFUtils.js'
import cover from '../node_modules/@mapbox/tile-cover/index.js'
import Model from '../class/Model.js'
import CircleLoader from '../class/loaders/CircleLoader.js'

export default class Profile extends Model {

    constructor (map = null) {
        super()
        if (map) this.map = map
    }

    map
    data
    mapdata = {}
    canvas

    // Build profile data
    async getData (routeData, options = {}) { // options : remote = boolean

        var profileData = {}
        const routeDistance = turf.length(routeData)
        const tunnels = routeData.properties.tunnels
        // Get as many times of 100m distance as it fits inside route distance into an array
        var distances = []
        for (let i = 0; i < routeDistance; i += 0.1) {
            distances.push(i)
        }
        // Get an array of points to check for building route profile
        profileData.profilePoints = getPointsToCheck(routeData, distances)
        if (options.remote == true) {
            // If profile is displayed, set map bounds to route bounds and wait for map elevation data to load
            if (document.querySelector('.show-profile')) {
                var routeBounds = CFUtils.defineRouteBounds(routeData.geometry.coordinates)
                if (this.map) {
                    this.map.fitBounds(routeBounds)
                    await this.map.once('idle')
                }
            }
        }
        // Get an array of elevation data for each profile point
        profileData.pointsElevation = []
        if (this.map) {
            for (let i = 0; i < profileData.profilePoints.length; i++) {
                var thisPointElevation = Math.floor(this.map.queryTerrainElevation(profileData.profilePoints[i].geometry.coordinates, {exaggerated: false}))
                profileData.pointsElevation.push(thisPointElevation)
            }
        }
        // Cut tunnels
        profileData.profilePointsCoordinates = []
        profileData.profilePoints.forEach( (point) => {
            profileData.profilePointsCoordinates.push(point.geometry.coordinates)
        } )
        if (tunnels) profileData = this.cutTunnels(profileData, tunnels)
        // Average elevation
        profileData.averagedPointsElevation = this.averageElevation(profileData.pointsElevation, routeDistance)
        // Build labels
        var labels = []
        for (let i = 0; i < (profileData.averagedPointsElevation.length); i++) labels.push((i / 10) + ' km')
        // Build points at regular format
        profileData.pointData = []
        for (let i = 0; i < (profileData.profilePoints.length); i++) {
            profileData.pointData.push({x: distances[i], y: profileData.averagedPointsElevation[i]})
        }

        this.data = profileData

        return profileData

        function getPointsToCheck (lineString, distancesToCheck) {     
            let points = [] 
            distancesToCheck.forEach( (distance) => {
                let feature = turf.along(lineString, distance, {units: "kilometers"} )
                feature.properties.distanceAlongLine = distance * 1000
                points.push(feature)
            } )     
            return points
        }
    }

    /**
     * Clear all profile data
     */
    clearData () {
        this.data = undefined
        delete this.routeData
    }

    /**
     * Cut tunnel sections in profileData
     * @param {Object} profileData profileData object
     * @param {[][]} tunnels array of tunnels coordinate arrays
     * @returns updated profileData object
     */
    cutTunnels (profileData, tunnels) {
        var previousStart = 0
        tunnels.forEach( (tunnel) => {
            var startClosestSectionCoordinates = CFUtils.closestLocation(tunnel[0], profileData.profilePointsCoordinates)
            var startKey = parseInt(getKeyByValue(profileData.profilePointsCoordinates, startClosestSectionCoordinates)) - 1
            startKey = startKey < 0 ? 0 : startKey
            var endClosestSectionCoordinates = CFUtils.closestLocation(tunnel[tunnel.length - 1], profileData.profilePointsCoordinates)
            var endKey = parseInt(getKeyByValue(profileData.profilePointsCoordinates, endClosestSectionCoordinates)) + 1
            endKey = endKey >= profileData.profilePointsCoordinates.length ? profileData.profilePointsCoordinates.length - 1 : endKey
            ///if (startKey > endKey) [startKey, endKey] = [endKey, startKey] // Revert variables if found reverse order
            var toSlice = endKey - startKey + 1
            var toInsert = averageElevationFromTips(profileData.pointsElevation[startKey], profileData.pointsElevation[endKey], toSlice)
            var lengthToReplace = turf.length(turf.lineSlice(profileData.profilePointsCoordinates[startKey], profileData.profilePointsCoordinates[endKey], turf.lineString(profileData.profilePointsCoordinates)))
            var lengthOfTunnel = turf.length(turf.lineString(tunnel))

            // For preventing false positives, ignore if length to replace is too different from length of tunnel
            if ((lengthOfTunnel > 1 && lengthToReplace < (lengthOfTunnel * 2)) || lengthOfTunnel <= 1 && lengthToReplace < (lengthOfTunnel * 5)) {

                // Replace in array
                toInsert.reverse()
                profileData.pointsElevation.splice(startKey, toSlice)
                for (let i = 0; i < toInsert.length; i++) {
                    profileData.pointsElevation.splice(startKey, 0, toInsert[i])
                }
                if (startKey > previousStart) previousStart = startKey
            }
        } )
        return profileData

        function averageElevationFromTips (start, end, index) {
            var section = []
            for (let i = 0; i < index; i++) {
                var point = []
                for (let j = index; j > i; j--) point.push(start)
                for (let k = 0; k < i; k++) point.push(end)
                section.push(Math.floor(calculateAverage(point)))
            }
            return section
        }
    }

    /**
     * Average pointsElevation data
     * @param {[]} pointsElevation profileData pointsElevation
     * @param {int} routeDistance 
     * @returns {[]} corrected profileData pointsElevation
     */
    averageElevation (pointsElevation, routeDistance) {
            
        const basis = defineBasis(routeDistance)

        var averagedPointsElevation = []
        // Add [basis/2] first points at the start (deal with points which can't be averaged because not enough first points. For them, average will be calculated on available points)
        for (var i = 0; i < Math.ceil(basis / 2); i++) { // i = 1, 2, 3... basis/2
            var firstPoints = []
            for (let j = 0; j <= i; j++) { // i = 0 / j = 0... basis/2
                firstPoints[j] = pointsElevation[i - j]
            }
            let averagedPoint = Math.abs(Math.floor(calculateAverage(firstPoints)))
            averagedPointsElevation.push(averagedPoint)
        }
        // Add averaged points to averagedPointsElevation array
        for (var i = 0; i < (pointsElevation.length - basis); i++) {
            // Calculate the average of the next [basis] points
            var nextPoints = []
            for (let j = 0; j < basis; j++) { 
                nextPoints[j] = pointsElevation[i + j]
            }
            let averagedPoint = Math.abs(Math.floor(calculateAverage(nextPoints)))
            averagedPointsElevation.push(averagedPoint)
        }
        // Add [basis/2] last points at the end (deal with points which can't be averaged because not enough next points. For them, average will be calculated on remaining points)
        for (var i = Math.floor(basis / 2); i > 0; i--) { // i = 10, 9, 8, 7... 0
            var lastPoints = []
            for (let j = 0; j < i; j++) { // i = 10 / j = 0, 1, 2, 3... 10
                lastPoints[j] = pointsElevation[pointsElevation.length - i + j]
            }
            let averagedPoint = Math.abs(Math.floor(calculateAverage(lastPoints)))
            averagedPointsElevation.push(averagedPoint)
        }
        return averagedPointsElevation

        /**
         * Define profile averaging basis on a 100m unit (ex: for a basis of 5, take 5 next altitude points and average them)
         * @param {int} distance
         * @returns {int}
         */
        function defineBasis (distance) {
            if (distance < 5) return 4
            else if (distance >= 5 && distance < 30) return 5
            else if (distance >= 30 && distance < 80) return 6
            else if (distance >= 80) return 7
            else return 6
        }
    }

    /**
     * Retrieve precise profile data by analyzing elevation tiles of each road coordinate
     * @param {Object} profileData profile data object
     * @param {String} sourceName name of the source to generate profile for
     * @returns {Promise}
     */
    async queryPreciseData (profileData, sourceName) {

        return new Promise((resolve, reject) => {

            // Set tile query properties
            const profileDataGeometry = {
                coordinates: profileData.profilePointsCoordinates,
                type: 'LineString'
            }
            const limits = {
                min_zoom: 10,
                max_zoom: 12
            }
            var tilesAnalyzed = 0

            // Get route tiles info (tile = tile address, geom = corresponding bounding box coordinates)
            var tiles = cover.tiles(profileDataGeometry, limits)
            var geom = cover.geojson(profileDataGeometry, limits)

            // For each tile
            for (let i = 0; i < tiles.length; i++) {

                // Query raster image
                const [x, y, zoom] = tiles[i]
                const url = 'https://api.mapbox.com/v4/mapbox.terrain-rgb/' + zoom + '/' + x + '/' + y + '@2x.pngraw?access_token=' + this.apiKey
                const bbox = getTileBbox(geom.features[i].geometry.coordinates[0])
                
                // Retrieve tile as data url
                fetch(url)
                .then((response) => response.body)
                .then((body) => {
                    const reader = body.getReader()
                
                    return new ReadableStream({
                        start(controller) {
                            return pump()

                            function pump() {
                                return reader.read().then(({ done, value }) => {
                                    // When no more data needs to be consumed, close the stream
                                    if (done) {
                                        controller.close()
                                        return
                                    }

                                    // Enqueue the next data chunk into our target stream
                                    controller.enqueue(value)
                                    return pump()
                                })
                            }
                        },
                    })
                } )
                .then((stream) => new Response(stream))
                .then((response) => response.blob())
                .then((blob) => URL.createObjectURL(blob))
                .then((url) => {
                    // Draw tile on canvas
                    var canvas = document.createElement('canvas')
                    canvas.height = 512
                    canvas.width = 512
                    canvas.style.height = canvas.height + 'px'
                    canvas.style.width = canvas.width + 'px'
                    /// [DEBUG USE]document.body.prepend(canvas)
                    const ctx = canvas.getContext('2d', {willReadFrequently: true})
                    const img = new Image()
                    img.onload = async (event) => {
                        URL.revokeObjectURL(event.target.src)
                        ctx.drawImage(event.target, 0, 0)

                        // Find route coordinates inside this tile
                        for (let j = 0; j < profileData.pointData.length; j++) {
                            if (CFUtils.coordInsideBounds(profileData.profilePointsCoordinates[j], bbox)) {

                                // Get corresponding pixel
                                var pixel = getPixelPair(profileData.profilePointsCoordinates[j], bbox)                                
                                
                                /// [DEBUG USE] Color pixel on the canvas (Remember that red color will interfere with color-based elevation analysis)
                                /*ctx.fillStyle = '#ff0000'
                                ctx.fillRect(pixel[0], pixel[1], 3, 3)*/

                                // Get elevation for this pixel
                                const elevation = getPixelElevation(ctx, pixel)

                                profileData.pointData[j].y = elevation
                                profileData.pointsElevation[j] = elevation
                            }
                        }

                        tilesAnalyzed++

                        // When last tile have been analyzed
                        if (tilesAnalyzed == tiles.length) {

                            // Cut tunnels if data exists
                            if (!this.routeData) this.routeData = await this.getRouteData(sourceName)
                            const tunnels = this.routeData.properties.tunnels
                            if (tunnels) profileData = this.cutTunnels(profileData, tunnels)

                            // Average elevation
                            profileData.averagedPointsElevation = this.averageElevation(profileData.pointsElevation, turf.length(this.routeData))

                            // Update pointData (data to be drawn on profile)
                            for (let i = 0; i < (profileData.profilePoints.length); i++) {
                                profileData.pointData[i].y = profileData.averagedPointsElevation[i]
                            }

                            this.data = profileData

                            resolve(profileData)
                        }
                    }
                    img.src = url
                } )
                .catch((err) => console.error(err))
            }

            /**
             * Retrieve bbox from a geojson formatted tile
             * @param {float[][]} tileCoords array of coords
             * @returns {float[][]} bbox
             */
            function getTileBbox (tileCoords) {
                var lowestLng = 180
                var lowestLat = 180
                var highestLng = 0
                var highestLat = 0
                tileCoords.forEach(coord => {
                    if (coord[0] < lowestLng) lowestLng = coord[0]
                    if (coord[0] > highestLng) highestLng = coord[0]
                    if (coord[1] < lowestLat) lowestLat = coord[1]
                    if (coord[1] > highestLat) highestLat = coord[1]
                })
                return [[lowestLng, lowestLat], [highestLng, highestLat]]
            }

            /**
             * Get corresponding paix of pixels (x,y) on a tile from a pair of coordinates (lng,lat)
             * @param {float[]} coords coordinates to locate
             * @param {float[][]} bbox tile bouding box
             */
            function getPixelPair (coords, bbox) {
                var percentagePair = getPercentagePair(coords, bbox)
                return [Math.round(percentagePair[0] * 511 / 100), Math.round(percentagePair[1] * 511 / 100)]

                function getPercentagePair (coords, bbox) {
                    var xTotalDifference = bbox[1][0] - bbox[0][0]
                    var xLngDifference = bbox[1][0] - coords[0]
                    var yTotalDifference = bbox[1][1] - bbox[0][1]
                    var yLatDifference = bbox[1][1] - coords[1]
                    return [100 - ((xLngDifference / xTotalDifference) * 100), (yLatDifference / yTotalDifference) * 100]
                }
            }

            /**
             * Retrieve elevation of (x,y) pixel and retrieve elevation
             * @param {CanvasRenderingContext2D} ctx
             * @param {int[]} pixel [x,y] pixel position
             * @returns {int} elevation in meters
             */
            function getPixelElevation (ctx, pixel) {
                var pixelData = ctx.getImageData(pixel[0], pixel[1], 1, 1).data
                const r = pixelData[0]
                const g = pixelData[1]
                const b = pixelData[2]
                var elevation = -10000 + ((r * 256 * 256 + g * 256 + b) * 0.1)
                return Math.floor(elevation * 10) / 10
            }
        } )    
    }

    async calculateElevation (routeData) {
        var profileData = await this.getData(routeData)
        var elevation = 0
        for (let i = 1; i < profileData.averagedPointsElevation.length - 1; i++) {
            if (profileData.averagedPointsElevation[i] > profileData.averagedPointsElevation[i - 1]) {
                elevation += (profileData.averagedPointsElevation[i] - profileData.averagedPointsElevation[i - 1])
            }
        }
        return elevation
    }

    /**
     * Generate profile inside profileElement
     * @param {String} sourceName name of the source to generate profile for
     * @param {Array} poiData.sceneries sceneries to display as profile POI
     * @param {Array} poiData.rideCheckpoints ride checkpoints to display as profile POI
     * @param {Object} poiData.activityCheckpoints activity checkpoints to display as profile POI
     * @param {Boolean} precise define whether profile data is gotten from currently loaded map or beeply analyzed from map provider tileset
     */
    async generate ({sourceName = 'route', poiData = {}, precise = true} = {}) {

        const profileElement = document.getElementById('elevationProfile')
        const profileBox = document.getElementById('profileBox')
        if (!this.routeData) this.routeData = await this.getRouteData(sourceName)

        // If a route and a profile container is displayed
        if (this.routeData && profileElement) {

            var loader = new CircleLoader(profileBox, {absolute: true})
            loader.start()

            // Prepare profile data
            if (this.map && !this.data || this.data == undefined) this.data = await this.getData(this.routeData, {remote: true})
            if (precise) this.data = await this.queryPreciseData(this.data, sourceName)
            if (!profileElement) return // If profile element as been removed during process, stop here
            
            loader.stop()

            // Prepare profile settings
            const ctx = profileElement.getContext('2d')
            const downtwo = (ctx, value) => ctx.p0.parsed.y > ctx.p1.parsed.y + 2 ? value : undefined
            const flat = (ctx, value) => ctx.p0.parsed.y > ctx.p1.parsed.y - 2 ? value : undefined
            const uptwo = (ctx, value) => ctx.p0.parsed.y > ctx.p1.parsed.y - 6 ? value : undefined
            const upsix = (ctx, value) => ctx.p0.parsed.y > ctx.p1.parsed.y - 10 ? value : undefined
            const upten = (ctx, value) => ctx.p0.parsed.y > 0 ? value : undefined
            const data = {
                labels: this.data.labels,
                datasets: [ {
                    data: this.data.pointData,
                    fill: {
                        target: 'origin',
                        above: '#fffa9ccc'
                    },
                    borderColor: '#bbbbff',
                    tension: 0.1
                } ],
            }
            const backgroundColor = {
                id: 'backgroundColor',
                beforeDraw: (chart) => {
                    const ctx = chart.canvas.getContext('2d')
                    ctx.save()
                    ctx.globalCompositeOperation = 'destination-over'
                    var lingrad = ctx.createLinearGradient(0, 0, 0, 150);
                    lingrad.addColorStop(0, '#f9f9f9');
                    lingrad.addColorStop(0.5, '#fff');
                    ctx.fillStyle = lingrad
                    ctx.fillRect(0, 0, chart.width, chart.height)
                    ctx.restore()
                }
            }
            const displayPois = {
                id: 'displayPois',
                afterRender: (chart) => {
                    if (this.routeData) {
                        const ctx = chart.canvas.getContext('2d')
                        const routeDistance = turf.length(this.routeData)
                        var drawPoi = async (poi, type) => {
                            // Get X position
                            if (poi.distance > routeDistance) var pointDistance = routeDistance
                            else var pointDistance = poi.distance
                            var roughPositionProportion = pointDistance / routeDistance * 100
                            var roughPositionPixel = roughPositionProportion * (chart.scales.x._maxLength - chart.scales.x.left - chart.scales.x.paddingRight) / 100
                            poi.position = roughPositionPixel + chart.scales.x.left
                            // Get Y position
                            const dataX = chart.scales.x.getPixelForValue(pointDistance)
                            const dataY = chart.scales.y.getPixelForValue(this.data.averagedPointsElevation[Math.floor(pointDistance * 10)])
                            // Draw a line
                            var cursorLength = 10
                            ctx.strokeStyle = '#d6d6d6'
                            ctx.lineWidth = 1
                            ctx.beginPath()
                            ctx.moveTo(poi.position, dataY)
                            ctx.lineTo(poi.position, dataY - cursorLength)
                            ctx.stroke()
                            ctx.closePath()

                            // Format icon
                            if (type == 'scenery') {
                                poi.number = poi.id
                                if (document.querySelector('#' + type + poi.number)) var img = document.querySelector('#' + type + poi.number).querySelector('img')
                                else return
                            } else if (type == 'rideCheckpoint') {
                                if (document.querySelector('#checkpointPoiIcon' + poi.number)) var img = document.querySelector('#checkpointPoiIcon' + poi.number)
                                else var img = await this.generateCheckpointPoiElement(poi)
                            }
                            else if (type == 'activityCheckpoint') {
                                var svgElement = document.querySelector('#' + 'checkpoint' + poi.number + ' svg')
                                var img = new Image()
                                img.src = 'https://api.iconify.design/' + svgElement.dataset.icon.replace(':', '/') + '.svg'
                                img.height = 24
                                img.width = 24
                            }
                            // Prepare profile drawing variables
                            var width  = 15
                            var height = 15
                            const positionX = poi.position - width / 2
                            const positionY = dataY - cursorLength - height
                            // If first loading, wait for img to load if not loaded yet, else use it directly
                            if (!document.querySelector('canvas#offscreenCanvas' + poi.number)) {
                                if (img.complete) drawOnCanvas(img)
                                else img.addEventListener('load', () => drawOnCanvas(img))

                                function drawOnCanvas (img) {
                                    if (img.classList.contains('admin-marker')) {
                                        ctx.strokeStyle = 'yellow'
                                        ctx.lineWidth = 3
                                    }
                                    if (img.classList.contains('selected-marker')) {
                                        ctx.strokeStyle = '#ff5555'
                                        ctx.lineWidth = 3
                                    }
            
                                    var abstract = {}
                                    abstract.offscreenCanvas = document.createElement("canvas")
                                    abstract.offscreenCanvas.width = width
                                    abstract.offscreenCanvas.height = height
                                    abstract.offscreenContext = abstract.offscreenCanvas.getContext("2d")
                                    const ctx2 = abstract.offscreenContext
                                    ctx2.drawImage(img, 0, 0, width, height)
                                    ctx2.globalCompositeOperation = 'destination-atop'
                                    ctx2.arc(0 + width/2, 0 + height/2, width/2, 0, Math.PI * 2)
                                    ctx2.closePath()
                                    ctx2.fillStyle = "#fff"
                                    ctx2.fill()
                                    // Keep offscreenCanvas 'in cache' for next profile generating 
                                    abstract.offscreenCanvas.style.display = 'none'
                                    abstract.offscreenCanvas.id = 'offscreenCanvas' + poi.number
                                    document.body.appendChild(abstract.offscreenCanvas)
            
                                    // Draw icon
                                    ctx.drawImage(abstract.offscreenCanvas, positionX, positionY)
                                    ctx.beginPath()
                                    ctx.arc(positionX + width/2, positionY + height/2, width/2, 0, Math.PI * 2)
                                    ctx.closePath()
                                    ctx.stroke()
                                }
                            // If img has already been loaded, direcly use it for preventing unnecessary loading time
                            } else {
                                var offscreenCanvas = document.querySelector('canvas#offscreenCanvas' + poi.number)
                                // Draw icon on profile
                                ctx.drawImage(offscreenCanvas, positionX, positionY)
                                ctx.beginPath()
                                ctx.arc(positionX + width/2, positionY + height/2, width/2, 0, Math.PI * 2)
                                ctx.closePath()
                                ctx.stroke()
                            }
                        }
                    }
                    // For sceneries
                    if (poiData.sceneries) poiData.sceneries.forEach( (scenery) => {
                        if (scenery.on_route && (!document.querySelector('#displaySceneriesBox') || (document.querySelector('#displaySceneriesBox') && document.querySelector('#displaySceneriesBox').checked))) {
                            drawPoi(scenery, 'scenery')
                        }
                    } )
                    // For ride checkpoints
                    if (poiData.rideCheckpoints && poiData.rideCheckpoints.length > 0) {
                        poiData.rideCheckpoints.forEach( (rideCheckpoint) => {
                            if (rideCheckpoint.marker) {
                                drawPoi(rideCheckpoint, 'rideCheckpoint')
                                // If only one start/finish marker, generate finish marker
                                if (rideCheckpoint.marker._element.innerText == 'SF') {
                                    var finishPoi = { ...rideCheckpoint }
                                    finishPoi.number = 'F'
                                    finishPoi.distance = this.data.pointData[this.data.pointData.length - 1].x
                                    drawPoi(finishPoi, 'rideCheckpoint')
                                }
                            }
                        } )
                    }
                    // For activity checkpoints
                    if (poiData.activityCheckpoints) poiData.activityCheckpoints.forEach( (activityCheckpoint) => {
                        drawPoi(activityCheckpoint, 'activityCheckpoint')
                    } )
                }
            }
            const cursorOnHover = {
                id: 'cursorOnHover',
                afterEvent: (chart, args) => {
                    if (this.map && this.data) {
                        var e = args.event
                        if (e.type == 'mousemove' && args.inChartArea == true) {
                            // Get relevant data
                            const dataX        = chart.scales.x.getValueForPixel(e.x)
                            const distance     = Math.floor(dataX * 10) / 10
                            const maxDistance  = chart.scales.x._endValue
                            // Slope
                            if (this.data.averagedPointsElevation[Math.floor(distance * 10) + 1]) {
                                var slope = this.data.averagedPointsElevation[Math.floor(distance * 10) + 1] - this.data.averagedPointsElevation[Math.floor(distance * 10)]
                            } else { // Only calculate on previous 100m for the last index (because no next index)
                                var slope = this.data.averagedPointsElevation[Math.floor(distance * 10)] - this.data.averagedPointsElevation[Math.floor(distance * 10) - 1]
                            }
                            // As mouse is inside route profile area
                            if (distance >= 0 && distance <= maxDistance) {
                                // Reload canvas
                                this.canvas.destroy()
                                this.canvas = new Chart(ctx, chartSettings)
                                // Draw a line
                                ctx.strokeStyle = 'black'
                                ctx.lineWidth = 1
                                ctx.beginPath()
                                ctx.moveTo(e.x, 0)
                                ctx.lineTo(e.x, 9999)
                                ctx.stroke()
                                // Display corresponding point on route
                                var routePoint = turf.along(this.routeData, distance, {units: 'kilometers'})
                                if (slope <= 2 && slope >= -2) var circleColor = 'white'
                                else var circleColor = this.setSlopeStyle(slope).color
                                if (!this.map.getLayer('profilePoint')) {
                                    this.map.addLayer( {
                                        id: 'profilePoint',
                                        type: 'circle',
                                        source: {
                                            type: 'geojson',
                                            data: routePoint
                                        },
                                        paint: {
                                            'circle-radius': 5,
                                            'circle-color': circleColor
                                        }
                                    } )
                                } else {
                                    this.map.getSource('profilePoint').setData(routePoint)
                                    this.map.setPaintProperty('profilePoint', 'circle-color', circleColor)
                                }
                                // Display tooltip
                                this.clearTooltip()
                                this.drawTooltip(this.routeData, routePoint.geometry.coordinates[0], routePoint.geometry.coordinates[1], e.x, false, {backgroundColor: '#ffffff'})
                                // Highlight corresponding scenery data
                                if (this.mapdata.sceneries && (!this.displaySceneriesBox || this.displaySceneriesBox.checked)) {
                                    this.mapdata.sceneries.forEach( (scenery) => {
                                        if (document.getElementById(scenery.id) && scenery.distance < (distance + 1) && scenery.distance > (distance - 1)) {
                                            // Highlight preview image
                                            document.getElementById(scenery.id).querySelector('img').classList.add('admin-marker')
                                            // Highlight marker
                                            document.querySelector('#scenery' + scenery.id).querySelector('img').classList.add('admin-marker')
                                        } else if (document.getElementById(scenery.id) && scenery.on_route == true) {
                                            document.getElementById(scenery.id).querySelector('img').classList.remove('admin-marker')
                                            document.querySelector('#scenery' + scenery.id).querySelector('img').classList.remove('admin-marker')
                                        }
                                    } )
                                }
                            }    
                        } else if (e.type == 'mouseout' || args.inChartArea == false) {
                            // Clear tooltip if one
                            this.clearTooltip()
                            // Reload canvas
                            this.canvas.destroy()
                            this.canvas = new Chart(ctx, chartSettings)
                            // Remove corresponding point on route
                            if (this.map.getLayer('profilePoint')) {
                                this.map.removeLayer('profilePoint')
                                this.map.removeSource('profilePoint')
                            }
                        }
                    }
                }
            }
            const chartOptions = {
                parsing: false,
                animation: false,
                maintainAspectRatio: false,
                pointRadius: 0,
                pointHitRadius: 0,
                pointHoverRadius: 0,
                events: ['mousemove', 'mouseout'],
                segment: {
                    borderColor: ctx => downtwo(ctx, '#00e06e') || flat(ctx, 'yellow') || uptwo(ctx, 'orange') || upsix(ctx, '#ff5555') || upten(ctx, 'black'),
                },
                scales: {
                    x: {
                        type: 'linear',
                        bounds: 'data',
                        grid: {
                            color: '#00000000',
                            tickColor: 'lightgrey'
                        },
                        ticks: {
                            format: {
                                style: 'unit',
                                unit: 'kilometer'
                            },
                            autoSkip: true,
                            autoSkipPadding: 50,
                            maxRotation: 0
                        },
                        beginAtZero: true,
                    },
                    y: {
                        grid: {
                            borderDash: [5, 5],
                            drawTicks: false
                        },
                        ticks: {
                            format: {
                                style: 'unit',
                                unit: 'meter'
                            },
                            autoSkipPadding: 20,
                            padding: 8
                        }
                    }
                },
                interaction: {
                    mode: 'point',
                    axis: 'x',
                    intersect: false
                },
                plugins: {
                    legend: {
                        display: false,
                        labels: {
                            boxWidth: 100
                        }
                    },
                    // Define background color
                    backgroundColor: backgroundColor,
                    // Draw a vertical cursor on hover
                    cursorOnHover: cursorOnHover,
                    tooltip: {
                        enabled: false
                    },
                },
            }
            const chartSettings = {
                type: 'line',
                data,
                options: chartOptions,
                plugins: [backgroundColor, cursorOnHover, displayPois]
            }

            // Reset canvas
            if (this.canvas) this.canvas.destroy()
            // Bound chart to canvas
            this.canvas = new Chart(ctx, chartSettings)
        }
    }    

    /**
     * Pregenerate checkpoint elements to display on profile
     * @param {Object} poi poi data
     * @returns {Promise}
     */
    generateCheckpointPoiElement (poi) {
        return new Promise((resolve, reject) => {
            const element = poi.marker._element
            const canvas = document.createElement('canvas')
            canvas.height = 50
            canvas.width = 50
            var ctx = canvas.getContext("2d")
            ctx.font = "bold 35px monospace"
            if ((element.innerText == 'S' || element.innerText == 'SF') && poi.distance == 0) {
                ctx.fillStyle = 'green'
                var text = 'S'
            } else if ((element.innerText == 'F' || element.innerText == 'SF') && poi.distance != 0) {
                ctx.fillStyle = 'red'
                var text = 'F'
            } else {
                ctx.fillStyle = 'blue'
                var text = poi.number
            }
            ctx.rect(0, 0, 50, 50)
            ctx.fill()
            ctx.fillStyle = 'white'
            ctx.fillText(text, 15, 40)
            var img = new Image()
            img.src = canvas.toDataURL()
            img.addEventListener('load', () => {
                ctx.drawImage (img, 0, 0)
                img.classList.add('js-poi-icon')
                img.id = 'checkpointPoiIcon' + poi.number
                img.style.display = 'none'
                document.querySelector('#elevationProfile').appendChild(img)
                resolve(img)
            })
        } )
    }
}