import Popup from "/map/class/Popup.js"

export default class MkpointPopup extends Popup {

    constructor () {
        super()
    }
    
    apiUrl = '/api/map.php'
    type = 'mkpoint'
    data
    photos
    activity_id = false

    setPopupContent (mkpoint) {
        var visitedIcon = ''
        if (this.activity_id) {
            visitedIcon = `
                <div id="visited-icon" title="You have visited this mkpoint.">
                    <a href="/activity/` + this.activity_id + `" target="_blank">
                        <span class="iconify" data-icon="akar-icons:circle-check-fill" data-width="20" data-height="20"></span>
                    </a>
                </div>
            `
        }

        return `
        <div class="popup-img-container">
            <div class="popup-icons">
                <div id="target-button" title="Click to fly to this spot">
                    <span class="iconify" data-icon="icomoon-free:target" data-width="20" data-height="20"></span>
                </div>
                <form enctype="multipart/form-data" method="post" id="addphoto-button-form">
                    <label for="addphoto-button" title="Click to add a photo to this spot">
                        <span class="iconify" data-icon="ic:baseline-add-a-photo" data-width="20" data-height="20"></span>
                    </label>
                    <input id="addphoto-button" type="file" name="file" />
                    <input type="hidden" name="MAX_FILE_SIZE" value="10000000" />
                </form>
                <div id="like-button" title="Click to like this photo">
                    <span class="iconify" data-icon="mdi:heart-plus" data-width="20" data-height="20"></span>
                </div>
                <div class="js-favorite-button" title="Add to favorites list">
                    <span class="iconify" data-icon="mdi:favorite-add" data-width="20" data-height="20"></span>
                </div>` + 
                visitedIcon + `
            </div>
        </div>
        <div id="popup-content" class="popup-content">
            <div class="d-flex gap">
                <div class="round-propic-container">
                    <a href="/rider/` + mkpoint.user_id + `">
                        <img class="round-propic-img" />
                    </a>
                </div>
                <div class="popup-properties">
                    <div class="popup-properties-reference">
                        <div class="popup-properties-name">
                            <a href="/scenery/` + mkpoint.id + `" target="_blank">` + mkpoint.name + `</a>
                        </div>
                        <div class="popup-properties-location">` + mkpoint.city + ` (` + mkpoint.prefecture + `) - ` + mkpoint.elevation + `m</div>
                        <div class="popup-rating"></div>
                    </div>
                </div>
            </div>
            <div class="popup-description">` + mkpoint.description + `</div>
        </div>
        <div class="popup-buttons">
            <button id="showReviews" class="mp-button bg-button text-white">Show reviews</button>
        </div>
        <div class="chat-box">
            <div class="msgbox-label">Reviews</div>
            <div class="chat-reviews"></div>
            <div class="chat-msgbox">
                <textarea id="mkpointReview" class="fullwidth"></textarea>
                <button id="mkpointReviewSend" class="mp-button bg-button text-white">Post review</button>
            </div>
        </div>`
    }

    reviews = () => {
        if (document.querySelector('#mkpointReview')) {

            // Get reviews on this mkpoint
            ajaxGetRequest (this.apiUrl + "?get-reviews-mkpoint=" + this.data.id, (reviews) => {
                // Clear reviews if necessary
                if (document.querySelector('.chat-line')) {
                    document.querySelectorAll('.chat-line').forEach( (chatline) => {
                        chatline.remove()
                    } )
                }
                // Display reviews or a message if there is no review yet
                if (reviews.length > 0) reviews.forEach( (review) => this.displayReview(review))
                else {
                    var $noReviewMessage = document.createElement('div')
                    $noReviewMessage.innerText = 'No one yet reviewed this scenery point.'
                    $noReviewMessage.className = 'chat-default pb-2'
                    document.querySelector('.chat-reviews').appendChild($noReviewMessage)
                }
                // If connected user has already posted a review, change 'Post review' button to 'Edit review' and prepopulate text area
                if (document.querySelector('#review-author-' + sessionStorage.getItem('session-id'))) {
                    document.querySelector('#mkpointReviewSend').innerText = 'Edit review'
                    reviews.forEach( (review) => {
                        if (review.user.id == sessionStorage.getItem('session-id')) {
                            document.querySelector('#mkpointReview').innerText = review.content
                        }
                    } )
                }
            } )

            // Treat posting of a new review
            var textareaReview   = document.querySelector('#mkpointReview')
            var buttonReview     = document.querySelector('#mkpointReviewSend')
            buttonReview.addEventListener('click', () => {
                let content = textareaReview.value
                ajaxGetRequest (this.apiUrl + "?add-review-mkpoint=" + this.data.id + '&content=' + content, (review) => {
                    console.log(review)
                    // If content is empty, remove review element and but button text back
                    if (review.content == '') {
                        document.querySelector('#review-author-' + review.user.id).remove()
                        document.querySelector('#mkpointReviewSend').innerText = 'Post review'
                    // Else, display new review on top and change button text
                    } else {
                        this.displayReview(review, {new: true})
                        document.querySelector('#mkpointReviewSend').innerText = 'Edit review'
                    }
                } )
            } )

            // Show review on button click
            if (document.querySelector('#showReviews')) document.querySelector('#showReviews').onclick = function () {
                let chatbox = document.querySelector('.chat-box')
                let button = document.querySelector('#showReviews')
                if (button.innerText == 'Show reviews') {
                    chatbox.style.visibility = 'visible'
                    chatbox.style.height = 'auto'
                    button.innerText = 'Hide reviews'
                } else if (button.innerText == 'Hide reviews') {
                    chatbox.style.visibility = 'hidden'
                    chatbox.style.height = '0px'
                    button.innerText = 'Show reviews'
                }
            }
        }
    }

    displayReview = (review, options = {new: false}) => {
        if (document.querySelector('#mkpointReview')) {
            // If review is already displayed, update it and move it to the top
            console.log(review)
            if (document.getElementById('review-author-' + review.user.id)) {
                let $review = document.getElementById('review-author-' + review.user.id)
                $review.querySelector('.chat-time').innerText = review.time
                $review.querySelector('.chat-message').innerText = review.content
                let chatReviews = document.querySelector('.chat-reviews')
                chatReviews.insertBefore($review, chatReviews.firstChild)
            // Else, display it
            } else {
                // If a default message was displayed, clear it
                if (document.querySelector('.chat-default')) document.querySelector('.chat-default').remove()
                var chatReviews = document.querySelector('.chat-reviews')
                let $review = document.createElement('div')
                $review.className = 'chat-line'
                // Set review background in yellow if author is connected user 
                if (sessionStorage.getItem('session-id') === review.user.id) {
                    $review.classList.add('bg-admin', 'p-2')
                }
                $review.id = 'review-author-' + review.user.id
                if (options.new) chatReviews.prepend($review)
                else chatReviews.appendChild($review)
                let propicContainer = document.createElement('div')
                propicContainer.className = 'round-propic-container'
                propicContainer.style.width = '40px'
                propicContainer.style.height = '40px'
                propicContainer.style.minWidth = '40px'
                $review.appendChild(propicContainer)
                let propicLink = document.createElement('a')
                propicLink.href = '/rider/' + review.user.id
                propicLink.setAttribute('target', '_blank')
                propicContainer.appendChild(propicLink)
                let propic = document.createElement('img')
                propic.className = 'round-propic-img'
                propic.src = review.propic
                propicLink.appendChild(propic)
                let messageBlock = document.createElement('div')
                messageBlock.className = 'chat-message-block'
                messageBlock.style.marginLeft = '10px'
                $review.appendChild(messageBlock)
                let login = document.createElement('div')
                login.className = 'chat-login'
                login.innerText = review.user.login + ' - '
                let loginLink = document.createElement('a')
                loginLink.href = '/rider/' + review.user.id
                loginLink.setAttribute('target', '_blank')
                loginLink.appendChild(login)
                messageBlock.appendChild(loginLink)
                let time = document.createElement('div')
                time.className = 'chat-time'
                time.innerText = review.time
                messageBlock.appendChild(time)
                let content = document.createElement('div')
                content.className = 'chat-message'
                content.innerText = review.content
                messageBlock.appendChild(content)
            }
            // Add (or replace) stars if voted
            ajaxGetRequest (this.apiUrl + "?check-user-vote=" + review.mkpoint_id + "&user_id=" + review.user.id, (response) => {
                if (response != false) {
                    var number = parseInt(response)
                    // Remove stars previously displayed
                    document.querySelector('#review-author-' + review.user.id).querySelectorAll('.selected-star').forEach($star => $star.remove())
                    // Display new stars
                    for (let i = 1; i < number + 1; i++) {
                        let star = document.createElement('div')
                        star.innerText = '★'
                        star.className = 'd-inline selected-star'
                        document.getElementById('review-author-' + review.user.id).querySelector('.chat-time').after(star)
                    }
                }
            } )
        }

    }

    setTarget = () => {
        this.popup.getElement().querySelector('#target-button').addEventListener('click', () => {
            var map = this.popup._map
            var lngLat = this.popup._lngLat
            map.flyTo( {
                center: lngLat,
                zoom: 17,
                speed: 0.4,
                curve: 1,
                pitch: 40,
                easing(t) {
                return t
                }
            } )
        } )
    }
    
    // Adds multiple photos to the mkpoint popup
    addPhoto = () => {
        var photoContainer = this.popup.getElement().querySelector('.popup-img-container')

        // Asks server for current photo data
        this.loaderContainer = photoContainer
        ajaxGetRequest (this.apiUrl + "?mkpoint-photos=" + this.data.id, displayPhotos.bind(this), this.loader)

        function displayPhotos (response) {

            this.photos = response

            var addArrows = () => {
                if (!photoContainer.querySelector('.small-prev')) {
                    var minusPhotoButton = document.createElement('a')
                    minusPhotoButton.classList.add('small-prev', 'lightbox-arrow')
                    minusPhotoButton.innerText = '<'
                    photoContainer.appendChild(minusPhotoButton)
                    var plusPhotoButton = document.createElement('a')
                    plusPhotoButton.classList.add('small-next', 'lightbox-arrow')
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

            var addDeletePhotoIcon = () => {
                // If delete photo button is not already displayed, display it
                if (!this.popup.getElement().querySelector('.deletephoto-button')) {
                    var deletePhoto = document.createElement('div')
                    deletePhoto.className = 'deletephoto-button admin-icon'
                    deletePhoto.innerHTML = '<span class="iconify" data-icon="mdi:image-remove" data-width="20" data-height="20"></span>'
                    deletePhoto.title = 'Click to delete this photo'
                    this.popup.getElement().querySelector('.popup-icons').appendChild(deletePhoto)
                    // Delete photo on click
                    deletePhoto.addEventListener('click', () => {
                        var modal = document.createElement('div')
                        modal.classList.add('modal', 'd-block')
                        document.querySelector('body').appendChild(modal)
                        // Remove modal on clicking outside popup
                        modal.addEventListener('click', (e) => {
                            var eTarget = e ? e.target : event.srcElement
                            if ((eTarget !== this.popup) && (eTarget !== modal)){
                                // Nothing
                            }else{
                                modal.remove()
                            }
                        })
                        var deleteConfirmationPopup = document.createElement('div')
                        deleteConfirmationPopup.classList.add('popup')
                        deleteConfirmationPopup.innerHTML = 'Do you really want to delete this photo ?<div class="d-flex justify-content-between"><div id="yes" class="mp-button bg-darkred text-white">Yes</div><div id="no" class="mp-button bg-darkgreen text-white">No</div></div>'
                        modal.appendChild(deleteConfirmationPopup)
                        // On click on "Yes" button, remove the photo and close the popup
                        document.querySelector('#yes').addEventListener('click', () => {
                            // Get currently displayed photo id
                            var photo_id
                            var currentPhoto
                            document.querySelectorAll('.popup-img').forEach( ($photo) => {
                                if ($photo.style.display == 'block') {
                                    photo_id = $photo.id
                                    currentPhoto = $photo
                                }
                            } )
                            // Delete photo
                            ajaxGetRequest (this.apiUrl + "?delete-photo-mkpoint=" + this.data.id + "&photo=" + photo_id, (response) => {
                                // Remove photo and period
                                currentPhoto.nextSibling.remove() // Period
                                currentPhoto.remove()
                                modal.remove()
                                deleteConfirmationPopup.remove()
                                // Reload photos
                                ajaxGetRequest (this.apiUrl + "?mkpoint-photos=" + this.data.id, displayPhotos.bind(this))
                            } )
                        } )
                        // On click on "No" button, close the popup
                        document.querySelector('#no').addEventListener('click', () => {
                            modal.remove()
                            deleteConfirmationPopup.remove()
                        } )
                    } )
                }
            }

            if (!document.querySelector('.popup-img')) {
                // Add photos to the DOM
                for (let i = 0; i < response.length; i++) {
                    addPhoto(response[i], i + 1)
                }
                // Handle listener to the add photo button
                var form = this.popup.getElement().querySelector('#addphoto-button-form')
                form.addEventListener('change', (e) => {

                    // Prevents default behavior of the submit button
                    e.preventDefault()
                    
                    // Get form data into queryData and adds tab id
                    var newPhotoData = new FormData(form)
                    newPhotoData.append('addphoto-button-form', true)
                    newPhotoData.append('mkpoint_id', this.data.id)
                    
                    // Proceed AJAX request and treat data in the callback function
                    ajaxPostFormDataRequest(this.apiUrl, newPhotoData, (response) => {
        
                        // In case of error, display corresponding error message
                        if (response.error) {
    
                            console.log('error')
                            console.log(response)
        
                            // If there is already a message displayed, remove it before
                            if (document.querySelector('.error-block')) {
                                document.querySelector('.error-block').remove()
                            }
                            var errorDiv = document.createElement('div')
                            errorDiv.classList.add('error-block', 'fullwidth', 'm-0', 'p-2')
                            var errorMessage = document.createElement('p')
                            errorMessage.innerHTML = response.error
                            errorMessage.classList.add('error-message')
                            errorDiv.appendChild(errorMessage)
                            document.querySelector('.mapboxgl-popup-content').prepend(errorDiv)
        
                        } else {
        
                            // If upload process went successfully, remove the error message if one is displayed
                            if (document.querySelector('.error-block')) {
                                document.querySelector('.error-block').remove()
                            }
        
                            // Reload photos
                            ajaxGetRequest (this.apiUrl + "?mkpoint-photos=" + this.data.id, displayPhotos.bind(this))
                        }
                    } )
                } )
            }

            // If a new photo has been uploaded, add it
            if (response.length > document.querySelectorAll('.popup-img').length) {
                addPhoto(response[response.length-1], response.length-1)
            }

            // Display first photo and period by default
            document.querySelector('.popup-img').style.display = 'block'
            document.querySelector('.mkpoint-period').style.display = 'block'

            // Set modal
            this.prepareModal()
            
            // Set slider system
            var setThumbnailSlider = setThumbnailSlider.bind(this)
            setThumbnailSlider(1)

            // Prepare toggle like function
            this.colorLike()
            this.toggleLike()

            function addPhoto (photo, number) {
                var newPhoto = document.createElement('img')
                newPhoto.classList.add('popup-img', 'js-clickable-thumbnail')
                newPhoto.style.display = 'none'
                newPhoto.dataset.number = number
                newPhoto.dataset.id = photo.id
                newPhoto.dataset.author = photo.user_id
                newPhoto.src = 'data:image/jpeg;base64,' + photo.blob
                photoContainer.firstChild.before(newPhoto)
                var newPhotoPeriod = document.createElement('div')
                newPhotoPeriod.classList.add('mkpoint-period', setPeriodClass(photo))
                newPhotoPeriod.innerText = photo.period
                newPhotoPeriod.style.display = 'none'
                newPhoto.after(newPhotoPeriod)
            }

            // Functions for sliding photos of mkpoints
            function setThumbnailSlider (photoIndex) {

                var i
                var photos = document.getElementsByClassName("popup-img")
                var photosPeriods = document.getElementsByClassName("mkpoint-period")

                // If there is more than one photo in the database
                if (response.length > 1) {

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
                        // Change the color of the like button depending on if new photo has been liked or not
                        this.colorLike()
                    }
                    
                    this.popup.getElement().querySelector('.small-prev').addEventListener('click', minusPhoto)
                    this.popup.getElement().querySelector('.small-next').addEventListener('click', plusPhoto)
                    showPhotos(photoIndex)
    
                // If there is only one photo in the database, remove arrows if needed
                } else {
                    removeArrows()
                }

                // Add delete photo button if necessary
                if (this.popup.getElement().querySelector('.deletephoto-button')) this.popup.getElement().querySelector('.deletephoto-button').remove() // If delete photo button is displayed, remove it...
                if (photos[photoIndex-1].dataset.author == sessionStorage.getItem('session-id')) addDeletePhotoIcon() // ... And add it if connected user is photo author
                
            }
        }
    }

    setFavorite () {
        var $button = document.querySelector('.js-favorite-button')
        $button.addEventListener('click', () => {
            var id = this.data.id
            var type = 'scenery'
            ajaxGetRequest ('/api/favorites.php' + '?toggle-' + type + '=' + id, (response) => {
                showResponseMessage(response, {
                    element: document.querySelector('.main'),
                    absolute: true
                } )
                this.popup.once('close', hideResponseMessage)
                var marker
                this.popup._map._markers.forEach( (_marker) => { // Get current marker instance
                    if (_marker.getElement().id == 'mkpoint' + this.data.id) marker = _marker
                } )
                marker.getElement().classList.toggle('favoured-marker')
                if (response.success.includes('removed')) {
                    $button.classList.remove('favoured')
                } else if (response.success.includes('added')) {
                    $button.classList.add('favoured')
                }
            } )
        } )
    }

    prepareModal () {

        // Prepare arrows
        if (this.photos.length > 1) {
            var prevArrow = document.createElement('a')
            prevArrow.className = 'prev lightbox-arrow'
            prevArrow.innerHTML = '&#10094;'
            var nextArrow = document.createElement('a')
            nextArrow.className = 'next lightbox-arrow'
            nextArrow.innerHTML = '&#10095;'
        }
        
        // If first opening, prepare modal window structure
        if (!document.querySelector('#myModal')) {
            var modalBaseContent = document.createElement('div')
            modalBaseContent.id = 'myModal'
            modalBaseContent.className = 'modal'
            var closeButton = document.createElement('span')
            closeButton.className = "close cursor"
            closeButton.setAttribute('onclick', 'closeModal()')
            closeButton.innerHTML = '&times;'
            var modalBlock = document.createElement('div')
            modalBlock.className = "modal-block"
            modalBaseContent.appendChild(closeButton)
            modalBaseContent.appendChild(modalBlock)
            document.querySelector('body').after(modalBaseContent)
            // If more than one photo, display arrows
            if (this.photos.length > 1) {
                modalBlock.appendChild(prevArrow)
                modalBlock.appendChild(nextArrow)
            }
        // Else, clear modal window content
        } else {
            document.querySelector('.modal-block').innerHTML = ''
            if (this.photos.length > 1) {
                document.querySelector('.modal-block').appendChild(prevArrow)
                document.querySelector('.modal-block').appendChild(nextArrow)
            }
        }
        
        // Slides display
        var slides = []
        var imgs = []
        var slidesBox = document.createElement('div')
        slidesBox.className = 'slides-box'
        document.querySelector('.modal-block').appendChild(slidesBox)
        for (let i = 0; i < this.photos.length; i++) {
            slides[i] = document.createElement('div')
            slides[i].className = 'mySlides wider-slide'
            // Create number
            let numberText = document.createElement('div')
            numberText.className = 'numbertext'
            numberText.innerHTML = (i + 1) + ' / ' + this.photos.length
            slides[i].appendChild(numberText)
            // Create image
            imgs[i] = document.createElement('img')
            imgs[i].src = 'data:image/jpeg;base64,' + this.photos[i].blob
            imgs[i].id = 'mkpoint-img-' + this.photos[i].id
            imgs[i].classList.add('fullwidth')
            slides[i].appendChild(imgs[i])
            // Create image meta
            var imgMeta = document.createElement('div')
            imgMeta.className = 'mkpoint-img-meta'
            slides[i].appendChild(imgMeta)
            var likeButton = document.createElement('div')
            likeButton.className = 'like-button-modal'
            likeButton.style.color = 'white'
            likeButton.setAttribute('title', 'Click to like this photo')
            var likeIcon = document.createElement('span')
            likeIcon.className = 'iconify'
            likeIcon.dataset.icon = 'mdi:heart-plus'
            likeIcon.dataset.width = '40'
            likeIcon.dataset.height = '40'
            likeButton.appendChild(likeIcon)
            imgMeta.appendChild(likeButton)
            var likes = document.createElement('div')
            likes.className = 'mkpoint-img-likes'
            likes.innerText = this.photos[i].likes
            imgMeta.appendChild(likes)
            var period = document.createElement('div')
            period.className = 'mkpoint-period lightbox-period'
            period.classList.add('period-' + this.photos[i].month)
            period.innerText = this.photos[i].period
            imgMeta.appendChild(period)
            slidesBox.appendChild(slides[i])
        }
        console.log(slides)
        // Caption display
        var caption = document.createElement('div')
        caption.className = 'lightbox-caption'
        var name = document.createElement('div')
        name.innerText = this.data.name
        name.className = 'lightbox-name'
        caption.appendChild(name)
        var location = document.createElement('div')
        location.innerText = this.data.city + ' (' + this.data.prefecture + ') - ' + this.data.elevation + 'm'
        location.className = 'lightbox-location'
        caption.appendChild(location)
        var description = document.createElement('div')
        description.className = 'lightbox-description'
        description.innerText = this.data.description
        caption.appendChild(description)
        slidesBox.appendChild(caption)
        // Display caption on slide hover
        slides.forEach( (slide) => {
            slide.addEventListener('mouseover', () => {
                caption.style.visibility = 'visible'
                caption.style.opacity = '1'
            } )
            slide.addEventListener('mouseout', () => {
                caption.style.visibility = 'hidden'
                caption.style.opacity = '0'
            } )
        } )
        // Demos display
        var demos = []
        var demosBox = document.createElement('div')
        demosBox.className = 'thumbnails-box'
        document.querySelector('.modal-block').appendChild(demosBox)
        for (let i = 0; i < this.photos.length; i++) {
            let column = document.createElement('div')
            column.className = 'column'
            demos[i] = document.createElement('img')
            demos[i].className = 'demo cursor fullwidth'
            demos[i].setAttribute('demoId', i + 1)
            demos[i].src = 'data:image/jpeg;base64,' + this.photos[i].blob
            column.appendChild(demos[i])
            demosBox.appendChild(column)
        }

        // Load lightbox script for this popup
        var script = document.createElement('script')
        script.src = '/assets/js/lightbox-script.js'
        if (this.popup.getElement()) this.popup.getElement().appendChild(script)
        else document.querySelector('body').appendChild(script)
    }
    
    // Adds user profile picture to the mkpoint popup
    addPropic = () => {
        // Asks server for profil picture src and display it
        ajaxGetRequest (this.apiUrl + "?getpropic=" + this.data.user_id, (response) => this.popup.getElement().querySelector('.round-propic-img').src = response)
    }
    
    mkpointAdmin = () => {
    // Edit
        var editButton = this.popup.getElement().querySelector('#mkpointEdit')
        // On click on edit button, change text into input fields and change Edit button into Save button
        editButton.addEventListener('click', () => {
            // Change name and description into input fields
            var $name = this.popup.getElement().querySelector('.popup-properties-name')
            var $description = this.popup.getElement().querySelector('.popup-description')
            var inputName = document.createElement('input')
            inputName.setAttribute('type', 'text')
            inputName.id = 'mkpoint-edit-name'
            inputName.classList.add('popup-properties-name', 'admin-field')
            inputName.value = $name.innerText
            var textareaDescription = document.createElement('textarea')
            textareaDescription.id = 'mkpoint-edit-description'
            textareaDescription.classList.add('admin-field')
            textareaDescription.value = $description.innerText
            $name.before(inputName)
            $name.style.display = 'none'
            $description.before(textareaDescription)
            $description.style.display = 'none'
            // Change edit button into save button
            var saveButton = document.createElement('div')
            saveButton.classList.add('mp-button', 'bg-button', 'text-white')
            saveButton.innerText = 'Save'
            editButton.after(saveButton)
            editButton.style.display = 'none'
            saveButton.addEventListener('click', () => {
                let name = inputName.value
                let description = textareaDescription.value
                ajaxGetRequest (this.apiUrl + "?edit-mkpoint=" + this.data.id + "&name=" + name + "&description=" + description, (response) => {
                    saveButton.remove()
                    editButton.style.display = 'block'
                    inputName.remove()
                    $name.style.display = 'block'
                    $name.innerText = response.name
                    textareaDescription.remove()
                    $description.style.display = 'block'
                    $description.innerText = response.description
                } )
            } )
        } )
    // Move
        var moveMkpoint = () => {
            moveButton.style.opacity = '70%'
            moveButton.innerText = 'Set'
            moveButton.onclick = quitMoveMkpoint
            $marker.classList.add('moving-marker')
            marker.setDraggable(true)
            this.popup.on('close', quitMoveMkpoint)
            marker.on('dragend', () => {
                ajaxGetRequest (this.apiUrl + "?mkpoint-dragged=" + $marker.dataset.id + "&lng=" + marker._lngLat.lng + "&lat=" + marker._lngLat.lat, afterMkpointUpdate)
                function afterMkpointUpdate (response) {
                    /*
                    // update mkpointsMarkerCollection
                    globalmap.mkpointsMarkerCollection
                    */
                }
            } )
        }
        var quitMoveMkpoint = () => {
            marker.setDraggable(false)
            marker.getElement().classList.remove('moving-marker')
            moveButton.style.opacity = '100%'
            moveButton.innerText = 'Move'
            moveButton.onclick = moveMkpoint
        }
        var marker
        this.popup._map._markers.forEach( (_marker) => { // Get current marker instance
            if (_marker.getElement().id == 'mkpoint' + this.data.id) marker = _marker
        } )
        var $marker = marker.getElement()
        var moveButton = this.popup.getElement().querySelector('#mkpointMove')
        moveButton.onclick = moveMkpoint
        
    // Delete
        var deleteButton = this.popup.getElement().querySelector('#mkpointDelete')
        deleteButton.addEventListener('click', async () => {
            var answer = await openConfirmationPopup('Do you really want to remove this spot ?')
            if (answer) { // If yes, remove the mkpoint and close the popup
                ajaxGetRequest (this.apiUrl + "?delete-mkpoint=" + this.data.id, (response) => { console.log(response) } )
                document.querySelector('#mkpoint' + this.data.id).remove()
                this.popup.getElement().remove()
            }
        } )
    }

    select () {
        document.querySelector('.mapboxgl-canvas-container #mkpoint' + this.data.id).querySelector('.mkpoint-icon').classList.add('selected-marker')
        // If a table or slider is displayed, also select corresponding entries
        if (document.querySelector('.spec-table #mkpoint' + this.data.id)) document.querySelector('.spec-table #mkpoint' + this.data.id).classList.add('selected-entry')
        if (document.querySelector('.rt-slider #mkpoint' + this.data.id)) document.querySelector('.rt-slider #mkpoint' + this.data.id + ' img').classList.add('selected-marker')
    }
}