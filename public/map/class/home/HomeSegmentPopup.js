import CFUtils from "/map/class/CFUtils.js"
import HomeSegmentLightbox from "/map/class/home/HomeSegmentLightbox.js"
import SegmentPopup from "/map/class/segment/SegmentPopup.js"

export default class HomeSegmentPopup extends SegmentPopup {

    constructor (options, segment, instanceOptions) {
        super(options, segment, instanceOptions)
        this.data = segment
    }

    apiUrl = '/api/home.php'

    load () {
        console.log('here')

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
            <div class="popup-tag tag-dark">#` + CFUtils.getTagString(tag) + `</div>`
        } )

        // Build seasonBox
        var seasonBox = ''
        if (this.data.seasons.length > 0) {
            seasonBox = '<div class="popup-season-box">'
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
            seasonBox += '</div>'
        }

        // Build pointBox
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

        // Set content
        this.popup.setHTML(`
        <div class="popup-img-container">
            <div class="popup-img-background">
                <img id="segmentFeaturedImage` + this.data.id + `" class="popup-img popup-img-with-background" />
            </div>
            <div class="popup-icons"></div>
        </div>
        <div class="popup-content">
            <div class="popup-properties">
                <div class="popup-properties-name">` + this.data.name +
                    advised + `
                    <div class="popup-tag ` + tagColor + `" >`+ capitalizeFirstLetter(this.data.rank) + `</div>
                </div>
                <div>
                    距離 : `+ (Math.round(this.data.route.distance * 10) / 10) + `km - 獲得標高 : ` + this.data.route.elevation + `m
                </div>
                <div class="popup-rating"></div>
                <div id="profileBox" class="mt-2 mb-2" style="height: 100px; background-color: white;">
                    <canvas id="elevationProfile"></canvas>
                </div>
            </div>
            <div class="popup-description">`
                + this.data.description + `
            </div>
            <div>`
                + tags + `
            </div>`
            + adviceBox + ``
            + seasonBox + `
        </div>`)
    }

    rating = () => {
        var ratingDiv = this.popup.getElement().querySelector('.popup-rating')
        
        // Display 5 stars with an unique id
        if (ratingDiv.innerText == '') {
            for (let i = 1; i < 6; i++) {
                ratingDiv.innerHTML = ratingDiv.innerHTML + '<div number="' + i + '" class="star">☆</div>'
            }
        }
        
        var stars = this.popup.getElement().querySelectorAll('.star')

        // Get current rating infos
        ajaxGetRequest (this.apiUrl + "?get-rating=true&type=" + this.type + "&id=" + this.data.id, (ratingInfos) => {

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
                if (this.popup.getElement().querySelector('.popup-rating-details')) {
                    if (number == 0) { // If number is 0, remove rating details
                        this.popup.getElement().querySelector('.popup-rating-details').remove()
                    } else { // Else, update it
                        this.popup.getElement().querySelector('.popup-rating-details').innerText = round(ratingInfos.rating, 2).toFixed(2) + ' (' + ratingInfos.grades_number + ')'
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

        } )
    }

    // Get relevant photos from the API and display it with a modal behavior
    getMkpoints () {
        return new Promise( (resolve, reject) => {
            
            // Asks server for current photo data
            this.loaderContainer = this.popup.getElement().querySelector('.popup-img-background')
            ajaxGetRequest (this.apiUrl + "?segment-mkpoints=" + this.data.id, (mkpoints) => {

                resolve(mkpoints)
            }, this.loader)
        } )
    }

    displayPhotos () {
        
        var photoContainer = this.popup.getElement().querySelector('.popup-img-container')

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
            addPhoto(photo, cursor)
            cursor++
        } )
        
        // Set up lightbox
        var lightboxData = {
            photos: this.photos,
            mkpoints: this.mkpoints,
            route: this.data.route
        }
        var lightbox = new HomeSegmentLightbox(this.popup._map.getContainer(), this.popup, lightboxData)
        console.log(lightbox)
        
        // Set slider system
        var setThumbnailSlider = setThumbnailSlider.bind(this)
        setThumbnailSlider(1)

        function addPhoto (photo, number) {
            var newPhoto = document.createElement('img')
            newPhoto.classList.add('popup-img', 'js-clickable-thumbnail')
            if (number == 0) newPhoto.style.display = 'block'
            else newPhoto.style.display = 'none'
            newPhoto.dataset.id = photo.id
            newPhoto.dataset.author = photo.user_id
            newPhoto.dataset.number = number
            newPhoto.src = 'data:image/jpeg;base64,' + photo.blob
            photoContainer.firstChild.before(newPhoto)
            var newPhotoPeriod = document.createElement('div')
            newPhotoPeriod.classList.add('mkpoint-period', setPeriodClass(photo.month))
            newPhotoPeriod.innerText = photo.period
            newPhotoPeriod.style.display = 'none'
            newPhoto.after(newPhotoPeriod)
            
            // Lightbox listener
            newPhoto.addEventListener('click', () => {
                let id = parseInt(newPhoto.dataset.number)
                lightbox.open(id)
            } )
        }

        // Functions for sliding photos of mkpoints
        function setThumbnailSlider (photoIndex) {

            var i
            var photos = document.getElementsByClassName("popup-img")
            var photosPeriods = document.getElementsByClassName("mkpoint-period")

            // If there is more than one photo in the database
            if (this.photos.length > 1) {

                // Add left and right arrows and attach event listeners to it
                addArrows()
            
                var plusPhoto = () => { showPhotos (photoIndex += 1) }
                var minusPhoto = () => { showPhotos (photoIndex -= 1) }
                var showPhotos = (n) => {
                    if (n > photos.length) {photoIndex = 1}
                    if (n < 1) {photoIndex = photos.length}
                    for (i = 0; i < photos.length; i++) {
                        photos[i].style.display = 'none'
                    }
                    for (i = 0; i < photosPeriods.length; i++) {
                        photosPeriods[i].style.display = 'none'
                    }
                    photos[photoIndex-1].style.display = 'block'
                    photosPeriods[photoIndex-1].style.display = 'inline-block'
                }
                
                this.popup.getElement().querySelector('.small-prev').addEventListener('click', minusPhoto)
                this.popup.getElement().querySelector('.small-next').addEventListener('click', plusPhoto)
                showPhotos(photoIndex)

            // If there is only one photo in the database, remove arrows if needed
            } else removeArrows()   
        }
    }
}