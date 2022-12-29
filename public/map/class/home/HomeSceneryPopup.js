import HomeSceneryLightbox from "/map/class/home/HomeSceneryLightbox.js"
import SceneryPopup from "/map/class/scenery/SceneryPopup.js"
import CFUtils from "/map/class/CFUtils.js"

export default class HomeSceneryPopup extends SceneryPopup {

    constructor (options, data, instanceOptions) {
        super(options, data, instanceOptions)

        // Set popup element
        this.popup.setHTML(this.setContent(data.mkpoint))

        // Init interactions
        this.init()
    }

    apiUrl = '/api/home.php'

    init () {
        this.popup.once('open', async () => {

            // Setup general interactions
            this.select()
            await this.loadPhotos().then(() => this.loadLightbox())
            this.loadRating(this.data.mkpoint)
            this.setTarget()

            // Define actions to perform on each popup display
            this.popup.on('open', () => {
                this.data.mapInstance.unselectMarkers()
                this.loadLightbox()
            } )
            this.popup.on('close', () => {
                this.data.mapInstance.unselectMarkers()
            } )
        } )
    }

    setContent (mkpoint) {

        // Build tagslist
        var tags = ''
        if (mkpoint.tags) mkpoint.tags.map( (tag) => {
            tags += `
            <div class="popup-tag tag-dark">#` + CFUtils.getTagString(tag) + `</div>`
        } )

        return `
        <div class="popup-img-container">
            <div class="popup-icons">
                <div id="target-button" title="この絶景スポットに移動する。">
                    <span class="iconify" data-icon="icomoon-free:target" data-width="20" data-height="20"></span>
                </div>
            </div>
        </div>
        <div id="popup-content" class="popup-content">
            <div class="d-flex gap">
                <div class="popup-properties">
                    <div class="popup-properties-reference">
                        <div class="popup-properties-name">`
                            + mkpoint.name + `
                        </div>
                        <div class="popup-properties-location">` + mkpoint.city + ` (` + mkpoint.prefecture + `) - ` + mkpoint.elevation + `m</div>
                        <div class="popup-rating"></div>
                    </div>
                </div>
            </div>
            <div class="popup-description">` + mkpoint.description + `</div>
            <div class="js-tags">` + 
                tags + `
            </div>
        </div>`
    }

    async loadPhotos () {
        return new Promise((resolve, reject) => {

            var photosContainer = this.popup.getElement().querySelector('.popup-img-container')

            // Asks server for current photo data
            this.loaderContainer = photosContainer
            ajaxGetRequest (this.apiUrl + "?mkpoint-photos=" + this.data.mkpoint.id, (photos) => {

                this.photos = photos
                resolve(photos)

                var addArrows = () => {
                    if (!photosContainer.querySelector('.small-prev')) {
                        var minusPhotoButton = document.createElement('a')
                        minusPhotoButton.classList.add('small-prev', 'lightbox-arrow')
                        minusPhotoButton.innerText = '<'
                        photosContainer.appendChild(minusPhotoButton)
                        var plusPhotoButton = document.createElement('a')
                        plusPhotoButton.classList.add('small-next', 'lightbox-arrow')
                        plusPhotoButton.innerText = '>'
                        photosContainer.appendChild(plusPhotoButton)
                    }
                }

                var removeArrows = () => {
                    if (photosContainer.querySelector('.small-prev')) {
                        photosContainer.querySelector('.small-prev').remove()
                        photosContainer.querySelector('.small-next').remove()
                    }
                }

                const addPhoto = (photo, number) => {
                    photo.$element = document.createElement('img')
                    photo.$element.classList.add('popup-img')
                    photo.$element.dataset.number = number
                    photo.$element.dataset.id = photo.id
                    photo.$element.dataset.author = photo.user_id
                    photo.$element.src = 'data:image/jpeg;base64,' + photo.blob
                    photosContainer.prepend(photo.$element)
                    photo.$period = document.createElement('div')
                    photo.$period.classList.add('mkpoint-period', setPeriodClass(photo.month))
                    photo.$period.innerText = photo.period
                    // Display first photo and period by default
                    if (number == 1) {
                        photo.$period.style.display = 'block'
                        photo.$element.style.display = 'block'
                    } else {
                        photo.$period.style.display = 'none'
                        photo.$element.style.display = 'none'
                    }
                    photo.$element.after(photo.$period)
                    
                    // Set lightbox listener
                    photo.$element.addEventListener('click', () => {
                        let id = parseInt(photo.$element.dataset.number)
                        this.lightbox.open(id)
                    } )
                }

                // First clear container from previously displayed photos
                this.popup.getElement().querySelectorAll('popup-img').forEach(formerPhoto => formerPhoto.remove())
                
                // Add photos to the DOM on first opening
                for (let i = 0; i < this.photos.length; i++) addPhoto(this.photos[i], i + 1)

                var photoIndex = 1

                // If there is more than one photo in the database
                if (this.photos.length > 1) {

                    // Add left and right arrows and attach event listeners to it
                    addArrows()
                
                    var plusPhoto = () => { showPhotos (photoIndex += 1) }
                    var minusPhoto = () => { showPhotos (photoIndex -= 1) }
                    var showPhotos = (n) => {
                        if (n > this.photos.length) {photoIndex = 1}
                        if (n < 1) {photoIndex = this.photos.length}
                        for (let i = 0; i < this.photos.length; i++) {
                            this.photos[i].$element.style.display = 'none'
                            this.photos[i].$period.style.display = 'none'
                        }
                        this.photos[photoIndex-1].$element.style.display = 'block'
                        this.photos[photoIndex-1].$period.style.display = 'inline-block'
                    }
                    
                    this.popup.getElement().querySelector('.small-prev').addEventListener('click', minusPhoto)
                    this.popup.getElement().querySelector('.small-next').addEventListener('click', plusPhoto)
                    showPhotos(photoIndex)
                    
                // If there is only one photo in the database, remove arrows if needed
                } else removeArrows()
            }, this.loader)
        } )
    }

    // Setup lightbox
    loadLightbox (container = this.data.mapInstance.$map) {
        var lightboxData = {
            container,
            popup: this.popup,
            mapInstance: this.data.mapInstance,
            mkpoint: this.data.mkpoint,
            photos: this.photos
        }
        this.lightbox = new HomeSceneryLightbox(lightboxData, {noSession: true})
    }

    loadRating = (object) => {
        var ratingDiv = document.querySelector('.popup-rating')
        
        // Display 5 stars with an unique id
        if (ratingDiv.innerText == '') {
            for (let i = 1; i < 6; i++) {
                ratingDiv.innerHTML = ratingDiv.innerHTML + '<div number="' + i + '" class="star">☆</div>'
            }
        }
        
        var stars = document.querySelectorAll('.star')

        // Get current rating infos
        ajaxGetRequest (this.apiUrl + "?get-rating=true&type=" + this.type + "&id=" + object.id, (ratingInfos) => {

            var setRating = (ratingInfos) => {
                if (ratingInfos.vote != false) {
                    var number    = parseInt(ratingInfos.vote)
                    var className = 'voted-star'
                } else if (ratingInfos.grades_number > 0) {
                    var number    = parseInt(ratingInfos.rating)
                    var className = 'selected-star'
                } else {
                    var number    = 0
                    var className = ''
                }
                // Fill stars according to number
                for (let i = 1; i < number + 1; i++) {
                    stars[round(i-1)].innerText = '★'
                }
                for (let i = number + 1; i < 6; i++) {
                    stars[round(Math.floor(i)-1)].innerText = '☆'
                }
                // Set classes according to last declared response vote
                stars.forEach( (star) => { star.className = 'star ' + className } )

                // If rating details are already displayed, update them
                if (document.querySelector('.popup-rating-details')) {
                    if (number == 0) { // If number is 0, remove rating details
                        document.querySelector('.popup-rating-details').remove()
                    } else { // Else, update it
                        document.querySelector('.popup-rating-details').innerText = round(ratingInfos.rating, 2).toFixed(2) + ' (' + ratingInfos.grades_number + ')'
                    }
                // Else, display them
                } else if (ratingInfos.grades_number > 0) {
                    var ratingDetails = document.createElement('div')
                    ratingDetails.innerText = round(ratingInfos.rating, 2).toFixed(2) + ' (' + ratingInfos.grades_number + ')'
                    ratingDetails.className = 'popup-rating-details'
                    ratingDiv.after(ratingDetails)
                }
            }
            
            setRating(ratingInfos)

            if (ratingInfos.vote == false) var click = 0
            else var click = 1

            stars.forEach( (star) => {

                // On mouse hovering, fill the number of stars hovered and unfill others...
                star.addEventListener('mouseover', (e) => {
                    let number = parseInt(e.target.getAttribute('number'))
                    for (let i = 1; i < number + 1; i++) {
                        stars[round(i-1)].innerText = '★'
                        stars[round(i-1)].classList.add('hovered-star')
                    }
                    for (let i = number + 1; i < 6; i++) {
                        stars[round(i-1)].innerText = '☆'
                    }
                } )

                // ...and set it back to default when mouse leaves
                star.addEventListener('mouseout', () => {
                    setRating(ratingInfos)
                } )

                /*star.addEventListener('click', (e) => {
                    if (numIsPair(click)) {
                        // On click, send clicked number to API and update rating display
                        var vote = e.target.getAttribute('number')
                        ajaxGetRequest (this.apiUrl + "?set-rating=true&type=" + this.type + "&id=" + object.id + "&grade=" + vote, (response) => {
                            ratingInfos = response
                            setRating(ratingInfos)
                        } )
                    } else {
                        // On click, ask API to cancel current vote and update rating display
                        ajaxGetRequest (this.apiUrl + "?cancel-rating=true&type=" + this.type + "&id=" + object.id, (response) => {
                            ratingInfos = response
                            setRating(ratingInfos)
                        } )
                    }
                    click++
                } )*/
            } )
        } )
    }

}