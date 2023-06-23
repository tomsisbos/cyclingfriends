import CFUtils from "/class/utils/CFUtils.js"
import SegmentLightbox from "/class/maps/segment/SegmentLightbox.js"
import Popup from "/class/maps/Popup.js"
import Profile from "/class/Profile.js"

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
    profile
    data
    sceneries
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
                <div class="popup-tag tag-light">#` + CFUtils.getTagString(tag) + `</div>
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
            this.profile = new Profile(this.data.mapInstance.map)
            this.profile.generate({sourceName: 'segment' + this.data.id})

            // Query relevant sceneries and photos
            this.getSceneries().then(async (sceneries) => {
                this.sceneries = sceneries
                this.photos = await this.getPhotos()
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
    getSceneries () {
        return new Promise( (resolve, reject) => {
            
            // Asks server for current photo data
            this.loaderContainer = this.popup._content.querySelector('.popup-img-container')
            ajaxGetRequest (this.apiUrl + "?segment-sceneries=" + this.data.id, (sceneries) => {

                // Sort sceneries by distance order
                sceneries.forEach( (scenery) => scenery.distanceFromStart = this.getDistanceFromStart(scenery))
                sceneries.sort((a, b) => (a.distanceFromStart > b.distanceFromStart) ? 1 : -1)

                resolve(sceneries)
            }, this.loader)
        } )
    }

    getActivityPhotos () {
        return new Promise( (resolve, reject) => {
        
            // Asks server for current photo data
            this.loaderContainer = this.popup._content.querySelector('.popup-img-container')
            ajaxGetRequest (this.apiUrl + "?segment-public-photos=" + this.data.id, (photos) => {

                // Sort photos by distance order
                photos.forEach( (photo) => photo.distanceFromStart = this.getDistanceFromStart(photo))
                photos.sort((a, b) => (a.distanceFromStart > b.distanceFromStart) ? 1 : -1)

                resolve(photos)
            }, this.loader)
        })
    }

    async getPhotos () {
        var photos = []
        this.sceneries.forEach( (scenery) => {
            scenery.photos.forEach( (photo) => {
                photos.push(photo)
            } )
        } )
        ///var activityPhotos = await this.getActivityPhotos()
        ///activityPhotos.forEach(activityPhoto => photos.push(activityPhoto))
        return photos
    }

    // Set up lightbox
    loadLightbox () {
        var lightboxData = {
            photos: this.photos,
            sceneries: this.sceneries,
            route: this.data.route
        }
        this.lightbox = new SegmentLightbox(this.data.mapInstance.map.getContainer(), this.popup, lightboxData, {noSession: true})
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
        var profileData = this.profile.data
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
            newPhotoPeriod.classList.add('photo-period', setPeriodClass(photo.month))
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
        var photosPeriods = this.data.mapInstance.map.getContainer().getElementsByClassName("photo-period")

        // If there is more than one photo in the database
        if (photos.length > 1) {

            var photoIndex = 1

            // Add left and right arrows and attach event listeners to it
            addArrows()
        
            var plusPhoto = () => { showPhotos (photoIndex += 1) }
            var minusPhoto = () => { showPhotos (photoIndex -= 1) }
            var showPhotos = (n) => {
                if (n > this.photos.length) {photoIndex = 1}
                if (n < 1) {photoIndex = this.photos.length}
                for (let i = 0; i < photos.length; i++) {
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
        if (this.sceneries.length > 0) {
            var likeButton = document.createElement('div')
            likeButton.id = 'like-button'
            likeButton.setAttribute('title', 'この写真に「いいね」を付ける')
            likeButton.innerHTML = '<span class="iconify" data-icon="mdi:heart-plus" data-width="20" data-height="20"></span>'
            popupIcons.appendChild(likeButton)
            this.prepareToggleLike()
        }
    }

    getDistanceFromStart (scenery) {
        if (this.data.coordinates) var routeCoords = this.data.coordinates
        else if (this.data.route) var routeCoords = this.data.route.coordinates
        var section = turf.lineSlice(turf.point(routeCoords[0]), turf.point([scenery.lngLat.lng, scenery.lngLat.lat]), turf.lineString(routeCoords))
        return turf.length(section)
    }
}