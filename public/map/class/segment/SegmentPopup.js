import CFUtils from "/map/class/CFUtils.js"
import SegmentLightbox from "/map/class/segment/SegmentLightbox.js"
import Popup from "/map/class/Popup.js"

export default class SegmentPopup extends Popup {

    constructor (options, segment, instanceOptions) {
        super(options, {}, instanceOptions)
        
        this.data = segment
        
        // Set popup element
        var content = this.setContent(this.data)
        this.popup.setHTML(content)

        this.init()
    }
    
    apiUrl = '/api/map.php'
    type = 'segment'
    data
    mkpoints
    photos
    loaderContainer = document.body
    loader = {
        element: document.createElement('div'),
        prepare: () => this.loader.element.className = 'loader-center',
        start: () => this.loaderContainer.appendChild(this.loader.element),
        stop: () => this.loader.element.remove()
    }
    setFlyAlong

    async getDetails (id) {
        return new Promise(async (resolve, reject) => ajaxGetRequest(this.apiUrl + "?segment-details=" + id, (segment) => resolve(segment)))
    }

    setContent () {
        // Define advised
        var advised = ''
        if (this.data.advised) advised = '<div class="popup-advised" title="cyclingfriendsのおススメ">★</div>'

        // Define tag color according to segment rank
        if (this.data.rank == 'local') var tagColor = 'tag-lightblue'
        else if (this.data.rank == 'regional') var tagColor = 'tag-blue'
        else if (this.data.rank == 'national') var tagColor = 'tag-darkblue'

        // Build tagslist
        var tags = ''
        this.data.tags.map( (tag) => {
            tags += `
            <a target="_blank" href="/tag/` + tag + `">
                <div class="popup-tag tag-dark">#` + CFUtils.getTagString(tag) + `</div>
            </a>`
        } )

        return `
        <div class="popup-img-container"></div>
        <div class="popup-content">
            <div class="popup-properties">
                <div class="popup-properties-name">
                    <a target="_blank" style="text-decoration: none" href="/segment/` + this.data.id + `">` + this.data.name + `</a>` +
                    advised + `
                    <div class="popup-tag ` + tagColor + `" >`+ capitalizeFirstLetter(this.data.rank) + `</div>
                </div>
                <div class="js-properties-location">` + this.inlineLoader + `</div>
                <div class="popup-rating"></div>
                <div class="popup-tags">`
                    + tags + `
                </div>
                <div id="profileBox" class="mt-2 mb-2" style="height: 100px; background-color: white;">
                    <canvas id="elevationProfile"></canvas>
                </div>
            </div>
            <div class="popup-description">` + this.inlineLoader + `</div>
            <div class="js-popup-advice"></div>
            <div class="popup-season-box"></div>
            <a target="_blank" href="/segment/` + this.data.id + `">
                <button class="mp-button bg-button text-white">詳細ページ</div>
            </a>
        </div>`
    }

    init () {
        
        this.popup.once('open', async () => {

            // Setup general interactions
            this.loadRating(this.data)
            this.generateProfile({force: true})

            // Query relevant mkpoints and photos
            this.getMkpoints().then((mkpoints) => {
                this.mkpoints = mkpoints
                this.photos = this.getPhotos()
                this.displayPhotos()
                this.loadLightbox()
                this.addIconButtons()
            } )

            // Query segment details and fill up the popup
            this.populate()
        } )
    }

    async populate () {
        return new Promise(async (resolve, reject) => {

            // Get scenery details
            if (!this.data.description) {
                var data = await this.getDetails(this.data.id)
                this.data = {
                    ...data,
                    mapInstance: this.data.mapInstance
                }
            }

            // Build properties location
            var propertiesLocation = '距離 : ' + (Math.round(this.data.route.distance * 10) / 10) + 'km - 獲得標高 : ' + this.data.route.elevation + 'm'
            this.popup._content.querySelector('.js-properties-location').innerHTML = propertiesLocation
            
            // Build description
            this.popup._content.querySelector('.popup-description').innerHTML = this.data.description

            // Build adviceBox
            var adviceBox = ''
            if (this.data.advice.description) {
                adviceBox = `
                <div class="popup-advice">
                    <div class="popup-advice-name">
                        <iconify-icon icon="el:idea" width="20" height="20"></iconify-icon> ` +
                        this.data.advice.name + `
                    </div>
                        <div class="popup-advice-description">` +
                        this.data.advice.description + `
                    </div>
                </div>`
            }
            this.popup._content.querySelector('.js-popup-advice').innerHTML = adviceBox
            
            // Build seasonBox
            var seasonBox = ''
            if (this.data.seasons.length > 0) {
                this.data.seasons.forEach( (season) => {
                    seasonBox += `
                    <div class="popup-season">
                        <div class="popup-season-period">` +
                            CFUtils.getPeriodString(season.period_start) + ` から ` + CFUtils.getPeriodString(season.period_end) + ` まで
                        </div>
                        <div class="popup-season-description">` +
                            season.description + `
                        </div>
                    </div>`
                } )
            }
            this.popup._content.querySelector('.popup-season-box').innerHTML = seasonBox

            resolve(true)

        } )
    }

    // Get relevant photos from the API and display it with a modal behavior
    getMkpoints () {
        return new Promise( (resolve, reject) => {
            
            // Asks server for current photo data
            this.loaderContainer = this.popup._content.querySelector('.popup-img-container')
            ajaxGetRequest (this.apiUrl + "?segment-mkpoints=" + this.data.id, (mkpoints) => {

                // Sort mkpoints by distance order
                mkpoints.forEach( (mkpoint) => mkpoint.distanceFromStart = this.getDistanceFromStart(mkpoint))
                mkpoints.sort((a, b) => (a.distanceFromStart > b.distanceFromStart) ? 1 : -1)

                resolve(mkpoints)
            }, this.loader)
        } )
    }

    getPhotos () {
        var photos = []
        this.mkpoints.forEach( (mkpoint) => {
            mkpoint.photos.forEach( (photo) => {
                photos.push(photo)
            } )
        } )
        return photos
    }

    async generateProfile (profileOptions = {force: false}) {
        
        const map = this.data.mapInstance.map
        const route = map.getSource('segment' + this.data.id)

        // If a route is displayed on the map
        if (route) {

            // Prepare profile data
            if (profileOptions.force || !this.profileData) {
                this.profileData = await this.getProfileData(route)
            
                // Draw profile inside elevationProfile element

                // Prepare profile settings
                const ctx = document.getElementById('elevationProfile').getContext('2d')
                const downtwo = (ctx, value) => ctx.p0.parsed.y > ctx.p1.parsed.y + 2 ? value : undefined
                const flat = (ctx, value) => ctx.p0.parsed.y > ctx.p1.parsed.y - 2 ? value : undefined
                const uptwo = (ctx, value) => ctx.p0.parsed.y > ctx.p1.parsed.y - 6 ? value : undefined
                const upsix = (ctx, value) => ctx.p0.parsed.y > ctx.p1.parsed.y - 10 ? value : undefined
                const upten = (ctx, value) => ctx.p0.parsed.y > 0 ? value : undefined                    
                const data = {
                    labels: this.profileData.labels,
                    datasets: [ {
                        data: this.profileData.pointData,
                        fill: {
                            target: 'origin',
                            above: '#fffa9ccc'
                        },
                        borderColor: '#bbbbff',
                        tension: 0.1
                    } ],
                }/*
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
                }*/
                const cursorOnHover = {
                    id: 'cursorOnHover',
                    afterEvent: (chart, args) => {
                        var e = args.event
                        if (e.type == 'mousemove' && args.inChartArea == true) {
                            // Get relevant data
                            const dataX        = chart.scales.x.getValueForPixel(e.x)
                            const routeGeojson = route._data
                            const distance     = Math.floor(dataX * 10) / 10
                            const maxDistance  = chart.scales.x._endValue
                            const altitude     = this.profileData.pointsElevation[distance * 10]
                            // Slope
                            if (this.profileData.averagedPointsElevation[Math.floor(distance * 10) + 1]) {
                                var slope = this.profileData.averagedPointsElevation[Math.floor(distance * 10) + 1] - this.profileData.averagedPointsElevation[Math.floor(distance * 10)]
                            } else { // Only calculate on previous 100m for the last index (because no next index)
                                var slope = this.profileData.averagedPointsElevation[Math.floor(distance * 10)] - this.profileData.averagedPointsElevation[Math.floor(distance * 10) - 1]
                            }
                            // As mouse is inside route profile area
                            if (distance >= 0 && distance <= maxDistance) {
                                // Reload canvas
                                this.elevationProfile.destroy()
                                this.elevationProfile = new Chart(ctx, chartSettings)
                                // Draw a line
                                ctx.strokeStyle = 'black'
                                ctx.lineWidth = 1
                                ctx.beginPath()
                                ctx.moveTo(e.x, 0)
                                ctx.lineTo(e.x, 9999)
                                ctx.stroke()
                                // Display corresponding point on route
                                var routePoint = turf.along(routeGeojson, distance, {units: 'kilometers'})
                                if (slope <= 2 && slope >= -2) {
                                    var circleColor = 'white'
                                } else {
                                    var circleColor = this.setSlopeStyle(slope).color
                                }
                                if (!map.getLayer('profilePoint')) {
                                    map.addLayer( {
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
                                    map.getSource('profilePoint').setData(routePoint)
                                    map.setPaintProperty('profilePoint', 'circle-color', circleColor)
                                }
                                // Display tooltip
                                this.clearTooltip()
                                if (this.popup._content.querySelector('#profileBox')) var profileTop = this.popup._content.querySelector('#profileBox').getBoundingClientRect().top
                                else document.querySelector('#profileBox').getBoundingClientRect().top // On fly along mode, profile box isn't inside popup
                                var mapTop = map.getContainer().getBoundingClientRect().top
                                var navbarHeight = document.querySelector('.main-navbar').getBoundingClientRect().height
                                this.drawTooltip(map.getSource('segment' + this.data.id)._data, routePoint.geometry.coordinates[0], routePoint.geometry.coordinates[1], e.native.layerX, profileTop - mapTop - navbarHeight, {backgroundColor: '#ffffff', mergeWithCursor: true})
                            }    
                        } else if (e.type == 'mouseout' || args.inChartArea == false) {
                            // Clear tooltip if one
                            this.clearTooltip()
                            // Reload canvas
                            this.elevationProfile.destroy()
                            this.elevationProfile = new Chart(ctx, chartSettings)
                            // Remove corresponding point on route
                            if (map.getLayer('profilePoint')) {
                                map.removeLayer('profilePoint')
                                map.removeSource('profilePoint')
                            }
                        }  
                    }              
                }
                const options = {
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
                        /*backgroundColor: backgroundColor,*/
                        // Draw a vertical cursor on hover
                        cursorOnHover: cursorOnHover,
                        tooltip: {
                            enabled: false
                        },
                    },
                
                }
                const chartSettings = {
                    type: 'line',
                    data: data,
                    options: options,
                    plugins: [/*backgroundColor, */cursorOnHover]
                }

                // Reset canvas
                if (this.elevationProfile) {
                    this.elevationProfile.destroy()
                }
                // Bound chart to canvas
                this.elevationProfile = new Chart(ctx, chartSettings)
            }
        }
    }

    // Set up lightbox
    loadLightbox () {
        var lightboxData = {
            photos: this.photos,
            mkpoints: this.mkpoints,
            route: this.data.route
        }
        this.lightbox = new SegmentLightbox(this.data.mapInstance.map.getContainer(), this.popup, lightboxData, {noSession: true})
    }

    // Build profile data
    async getProfileData (route) {
        const map           = this.data.mapInstance.map
        const routeGeojson  = route._data
        const routeDistance = turf.length(routeGeojson)
        const tunnels       = routeGeojson.properties.tunnels
        // Get as many times of 100m distance as it fits inside route distance into an array
        var distances = []
        for (let i = 0; i < routeDistance; i += 0.1) {
            distances.push(i)
        }
        // Get an array of points to check for building route profile
        var profilePoints = getPointsToCheck(routeGeojson, distances)
        // Get an array of elevation data for each profile point
        var pointsElevation = []
        for (let i = 0; i < profilePoints.length; i++) {
            var thisPointElevation = Math.floor(map.queryTerrainElevation(profilePoints[i].geometry.coordinates, {exaggerated: false}))
            pointsElevation.push(thisPointElevation)
        }
        // Cut tunnels
        var profilePointsCoordinates = []
        profilePoints.forEach( (point) => {
            profilePointsCoordinates.push(point.geometry.coordinates)
        } )
        if (tunnels) {
            tunnels.forEach( (tunnel) => {
                var startClosestSectionCoordinates = CFUtils.closestLocation(tunnel[0], profilePointsCoordinates)
                var startKey = parseInt(getKeyByValue(profilePointsCoordinates, startClosestSectionCoordinates))
                var endClosestSectionCoordinates = CFUtils.closestLocation(tunnel[tunnel.length - 1], profilePointsCoordinates)
                var endKey = parseInt(getKeyByValue(profilePointsCoordinates, endClosestSectionCoordinates))
                if (startKey > endKey) [startKey, endKey] = [endKey, startKey] // Revert variables if found reverse order
                var toSlice = endKey - startKey + 1
                var toInsert = averageElevationFromTips(pointsElevation[startKey], pointsElevation[endKey], toSlice)
                // Replace in array
                toInsert.reverse()
                pointsElevation.splice(startKey, toSlice)
                for (let i = 0; i < toInsert.length; i++) {
                    pointsElevation.splice(startKey, 0, toInsert[i])
                }
            } )
        }
        // Average elevation
        var basis = defineBasis(routeDistance) * 1.2
        var averagedPointsElevation = averageElevation(pointsElevation, basis)
        // Build labels
        var labels = []
        for (let i = 0; i < (averagedPointsElevation.length); i++) labels.push((i / 10) + ' km')
        // Build points at regular format
        var pointData = []
        for (let i = 0; i < (profilePoints.length); i++) {
            pointData.push({x: distances[i], y: averagedPointsElevation[i]})
        }

        return {
            profilePoints: profilePoints,
            pointsElevation: pointsElevation,
            profilePointsCoordinates: profilePointsCoordinates,
            averagedPointsElevation: averagedPointsElevation,
            pointData: pointData,
            labels: labels
        }

        // Define profile averaging basis on a 100m unit
        function defineBasis (distance) {
            if (distance < 5) return 7
            else if (distance >= 5 && distance < 30) return 8
            else if (distance >= 30 && distance < 80) return 9
            else if (distance >= 80) return 10
            else return 8
        }

        function getPointsToCheck (lineString, distancesToCheck) {     
            let points = [] 
            distancesToCheck.forEach( (distance) => {
                let feature = turf.along(lineString, distance, {units: "kilometers"} )
                feature.properties.distanceAlongLine = distance * 1000
                points.push(feature)
            } )     
            return points
        }

        function averageElevation (pointsElevation, basis) { // ex: for a basis of 5, take 5 next altitude points and average them
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
        }
    
        function averageElevationFromTips (start, end, index) {
            var section = []
            for (let i = 0; i < index; i++) {
                var point = []
                for (let j = index; j > i; j--) {
                    point.push(start)
                }
                for (let k = 0; k < i; k++) {
                    point.push(end)
                }
                section.push(Math.floor(calculateAverage(point)))
            }
            return section
        }
    }

    clearTooltip () {
        if (document.querySelector('.map-tooltip')) {
            document.querySelector('.map-tooltip').remove()
        }
    }

    // Prepare tooltip display
    prepareTooltip () {
        this.map.on('mousemove', 'segment' + this.data.id, async (e) => {
            // Clear previous tooltip if displayed
            this.clearTooltip()
            // Prepare information to display
            this.drawTooltip(this.map.getSource('segment' + this.data.id)._data, e.lngLat.lng, e.lngLat.lat, e.point.x)
        } )
        this.map.on('mouseout', 'segment' + this.data.id, () => {
            // Clear tooltip
            this.clearTooltip()
        } )
    }

    // Prepare data of [lng, lat] route point and draw tooltip at pointX/pointY position
    async drawTooltip (routeData, lng, lat, pointX, pointY = false, options) {
        
        const map = this.data.mapInstance.map
        
        // Distance and twin distance if there is one
        var result = CFUtils.findDistanceWithTwins(routeData, {lng, lat})
        var distance = result.distance
        var twinDistance = result.twinDistance

        // Altitude
        var profileData = this.profileData
        var altitude = profileData.averagedPointsElevation[Math.floor(distance * 10)]

        // Slope
        if (profileData.averagedPointsElevation[Math.floor(distance * 10) + 1]) {
            var slope = profileData.averagedPointsElevation[Math.floor(distance * 10) + 1] - profileData.averagedPointsElevation[Math.floor(distance * 10)]
        } else { // Only calculate on previous 100m for the last index (because no next index)
            var slope = profileData.averagedPointsElevation[Math.floor(distance * 10)] - profileData.averagedPointsElevation[Math.floor(distance * 10) - 1]
        }

        // Build new tooltip
        var tooltip = document.createElement('div')
        tooltip.className = 'map-tooltip'
        tooltip.style.left = (10 + pointX) + 'px'
        if (pointY) tooltip.style.top = 'calc(' + (10 + pointY) + 'px)'
        else tooltip.style.bottom = 10 + document.querySelector('#profileBox').offsetHeight + 'px'
        if (twinDistance) {
            if (distance < twinDistance) {
                var dst1 = distance
                var dst2 = twinDistance
            } else {
                var dst1 = twinDistance
                var dst2 = distance
            }
            tooltip.innerHTML = `
            距離 : ` + dst1 + `km, ` + dst2 + `km<br>
            勾配 : <div class="map-slope">` + slope + `%</div><br>
            標高 : ` + altitude + `m`
        } else {
            tooltip.innerHTML = `
            距離 : ` + distance + `km<br>
            勾配 : <div class="map-slope">` + slope + `%</div><br>
            標高 : ` + altitude + `m`
        }
        map.getContainer().appendChild(tooltip)

        // Prevent tooltip from overflowing at the end of the profile
        if ((pointX + tooltip.offsetWidth) > map.offsetWidth) {
            tooltip.style.left = pointX - tooltip.offsetWidth - 10 + 'px'
        }

        // Styling
        var slopeStyle = document.querySelector('.map-slope')
        slopeStyle.style.color = this.setSlopeStyle(slope).color
        slopeStyle.style.fontWeight = this.setSlopeStyle(slope).weight
        if (options) {
            if (options.backgroundColor) tooltip.style.backgroundColor = options.backgroundColor
            if (options.mergeWithCursor) tooltip.style.borderRadius = '4px 4px 4px 0px'
        }
    }

    setSlopeStyle (slope) {
        if (slope <= -2) return {color: '#00e06e', weight: 'bold'}
        else if (slope > -2 && slope <= 2) return {color: '#000000', weight: 'normal'}
        else if (slope > 2 && slope <= 6) return {color: '#ffa500', weight: 'bold'}
        else if (slope > 6 && slope <= 9) return {color: '#ff5555', weight: 'bold'}
        else if (slope > 9) return {color: '#000000', weight: 'bold'}
    }

    displayPhotos () {
        
        var photoContainer = this.popup._content.querySelector('.popup-img-container')

        var addArrows = () => {
            if (!photoContainer.querySelector('.small-prev')) {
                var minusPhotoButton = document.createElement('a')
                minusPhotoButton.classList.add('small-prev')
                minusPhotoButton.innerText = '<'
                photoContainer.appendChild(minusPhotoButton)
                var plusPhotoButton = document.createElement('a')
                plusPhotoButton.classList.add('small-next')
                plusPhotoButton.innerText = '>'
                photoContainer.appendChild(plusPhotoButton)
            }
        }

        var removeArrows = () => {
            if (photoContainer.querySelector('.small-prev')) {
                photoContainer.querySelector('.small-prev').remove()
                photoContainer.querySelector('.small-next').remove()
            }
        }

        var cursor = 0
        // Add photos to the DOM
        this.photos.forEach( (photo) => {
            var newPhoto = document.createElement('img')
            newPhoto.classList.add('popup-img')
            if (cursor == 0) newPhoto.style.display = 'block'
            else newPhoto.style.display = 'none'
            newPhoto.dataset.id = photo.id
            newPhoto.dataset.author = photo.user_id
            newPhoto.dataset.number = cursor + 1
            newPhoto.src = photo.url
            photoContainer.appendChild(newPhoto)
            var newPhotoPeriod = document.createElement('div')
            newPhotoPeriod.classList.add('mkpoint-period', setPeriodClass(photo.month))
            newPhotoPeriod.innerText = photo.period
            newPhotoPeriod.style.display = 'none'
            newPhoto.after(newPhotoPeriod)

            // Set lightbox listener
            newPhoto.addEventListener('click', () => {
                let number = parseInt(newPhoto.dataset.number)
                this.lightbox.open(number)
            } )

            cursor++
        } )
        
        // Set slider system

        var photos = this.data.mapInstance.map.getContainer().getElementsByClassName("popup-img")
        var photosPeriods = this.data.mapInstance.map.getContainer().getElementsByClassName("mkpoint-period")

        // If there is more than one photo in the database
        if (this.photos.length > 1) {

            var photoIndex = 1

            // Add left and right arrows and attach event listeners to it
            addArrows()
        
            var plusPhoto = () => { showPhotos (photoIndex += 1) }
            var minusPhoto = () => { showPhotos (photoIndex -= 1) }
            var showPhotos = (n) => {
                if (n > this.photos.length) {photoIndex = 1}
                if (n < 1) {photoIndex = this.photos.length}
                for (let i = 0; i < this.photos.length; i++) {
                    photos[i].style.display = 'none'
                }
                for (let i = 0; i < photosPeriods.length; i++) {
                    photosPeriods[i].style.display = 'none'
                }
                photos[photoIndex - 1].style.display = 'block'
                photosPeriods[photoIndex - 1].style.display = 'inline-block'
                // Update like button color on every photo change
                this.colorLike()
            }
            
            this.popup._content.querySelector('.small-prev').addEventListener('click', minusPhoto)
            this.popup._content.querySelector('.small-next').addEventListener('click', plusPhoto)
            showPhotos(photoIndex)

        // If there is only one photo in the database, remove arrows if needed
        } else removeArrows()

        // If no photo for this segment, display link to details page
        if (this.photos.length == 0) photoContainer.innerHTML = `
            <a target="_blank" href="/segment/` + this.data.id + `">
                <div class="popup-img-background">
                    詳細ページ
                    <img id="segmentFeaturedImage` + this.data.id + `" class="popup-img popup-img-with-background" />
                </div>
            </a>`

    }

    addIconButtons () {
        var popupIcons = document.createElement('div')
        popupIcons.className = ('popup-icons')
        this.popup._content.querySelector('.popup-img-container').appendChild(popupIcons)

        // Fly along button
        var flyButton = document.createElement('div')
        flyButton.id = 'fly-button'
        flyButton.setAttribute('title', '走行再現モードに切り替える')
        flyButton.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" preserveAspectRatio="xMidYMid meet" viewBox="0 0 32 32"><path fill="currentColor" d="M23.188 3.735a1.766 1.766 0 0 0-3.532-.001c0 .975 1.766 4.267 1.766 4.267s1.766-3.292 1.766-4.267zm-2.61 0a.844.844 0 1 1 1.687-.001a.844.844 0 0 1-1.687.001zm4.703 14.76c-.56 0-1.097.047-1.59.123L11.1 13.976c.2-.18.312-.38.312-.59a.663.663 0 0 0-.088-.315l8.41-2.238c.46.137 1.023.22 1.646.22c1.52 0 2.75-.484 2.75-1.082c0-.6-1.23-1.083-2.75-1.083s-2.75.485-2.75 1.083c0 .07.02.137.054.202L9.896 12.2a8.075 8.075 0 0 0-2.265-.303c-2.087 0-3.78.667-3.78 1.49s1.693 1.49 3.78 1.49c.574 0 1.11-.055 1.598-.145l11.99 4.866c-.19.192-.306.4-.306.623c0 .19.096.364.236.533L8.695 25.415c-.158-.005-.316-.01-.477-.01c-3.24 0-5.87 1.036-5.87 2.31c0 1.277 2.63 2.313 5.87 2.313s5.87-1.034 5.87-2.312c0-.22-.083-.432-.23-.633l10.266-5.214c.37.04.753.065 1.155.065c2.413 0 4.37-.77 4.37-1.723c0-.944-1.957-1.716-4.37-1.716z"/></svg>'
        this.setFlyAlong(flyButton)
        popupIcons.appendChild(flyButton)

        // Like button
        if (this.mkpoints.length > 0) {
            var likeButton = document.createElement('div')
            likeButton.id = 'like-button'
            likeButton.setAttribute('title', 'この写真に「いいね」を付ける')
            likeButton.innerHTML = '<span class="iconify" data-icon="mdi:heart-plus" data-width="20" data-height="20"></span>'
            popupIcons.appendChild(likeButton)
            this.prepareToggleLike()
        }
    }

    getDistanceFromStart (mkpoint) {
        if (this.data.coordinates) var routeCoords = this.data.coordinates
        else if (this.data.route) var routeCoords = this.data.route.coordinates
        var section = turf.lineSlice(turf.point(routeCoords[0]), turf.point([mkpoint.lngLat.lng, mkpoint.lngLat.lat]), turf.lineString(routeCoords))
        return turf.length(section)
    }
}