import CFUtils from "/class/utils/CFUtils.js"
import HomeSegmentLightbox from "/class/maps/home/HomeSegmentLightbox.js"
import Profile from "/class/Profile.js"
import SegmentPopup from "/class/maps/segment/SegmentPopup.js"

export default class HomeSegmentPopup extends SegmentPopup {

    constructor (options, segment, instanceOptions) {
        super(options, segment, instanceOptions)
        
        this.data = segment
    }

    apiUrl = '/api/home.php'

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
                <div class="popup-properties-name">` + this.data.name +
                    advised + `
                </div>
                <div class="js-properties-location">` + this.inlineLoader + `</div>
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
        </div>`
    }

    init () {
        
        this.popup.once('open', async () => {

            // Setup general interactions
            this.profile = new Profile(this.data.mapInstance.map)
            this.profile.generate({sourceName: 'segment' + this.data.id})

            // Query relevant sceneries and photos
            this.getSceneries().then((sceneries) => {
                this.mapdata.sceneries = sceneries
                this.photos = this.getPhotos()
                this.displayPhotos()
                this.loadLightbox()
                this.addIconButtons()
            } )

            // Query segment details and fill up the popup
            this.populate()
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
            newPhotoPeriod.classList.add('scenery-period', setPeriodClass(photo.month))
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
        var photosPeriods = this.data.mapInstance.map.getContainer().getElementsByClassName("scenery-period")

        // If there is more than one photo in the database
        if (this.photos.length > 1) {

            var photoIndex = 1

            // Add left and right arrows and attach event listeners to it
            addArrows()
        
            var plusPhoto = () => { showPhotos (photoIndex += 1) }
            var minusPhoto = () => { showPhotos (photoIndex -= 1) }
            var showPhotos = (n) => {
                if (n > this.photos.length) {photoIndex = 1}
                if (n < 1) {photoIndex = photos.length}
                for (let i = 0; i < photos.length; i++) {
                    photos[i].style.display = 'none'
                }
                for (let i = 0; i < photosPeriods.length; i++) {
                    photosPeriods[i].style.display = 'none'
                }
                photos[photoIndex - 1].style.display = 'block'
                photosPeriods[photoIndex - 1].style.display = 'inline-block'
            }
            
            this.popup._content.querySelector('.small-prev').addEventListener('click', minusPhoto)
            this.popup._content.querySelector('.small-next').addEventListener('click', plusPhoto)
            showPhotos(photoIndex)

        // If there is only one photo in the database, remove arrows if needed
        } else removeArrows()
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
    }

    // Set up lightbox
    loadLightbox () {
        var lightboxData = {
            photos: this.photos,
            sceneries: this.mapdata.sceneries,
            route: this.data.route
        }
        this.lightbox = new HomeSegmentLightbox(this.data.mapInstance.map.getContainer(), this.popup, lightboxData, {noSession: true})
    }
}