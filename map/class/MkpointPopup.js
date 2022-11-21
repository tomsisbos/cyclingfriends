import Popup from "/map/class/Popup.js"

export default class MkpointPopup extends Popup {

    constructor () {
        super()
    }
    
    apiUrl = '/map/api.php'
    type = 'mkpoint'
    data
    photos
    activity_id = false

    setPopupContent (mkpoint) {
        var visitedIcon = ''
        if (this.activity_id) {
            visitedIcon = `
                <div id="visited-icon" title="You have visited this mkpoint.">
                    <a href="/activity.php?id=` + this.activity_id + `" target="_blank">
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
                </div>` + 
                visitedIcon + `
            </div>
        </div>
        <div id="popup-content" class="popup-content">
            <div class="d-flex gap">
                <div class="round-propic-container">
                    <a href="http://cyclingfriends.co/riders/profile.php?id=` + mkpoint.user_id + `">
                        <img class="round-propic-img" />
                    </a>
                </div>
                <div class="popup-properties">
                    <div class="popup-properties-reference">
                        <div class="popup-properties-name">` + mkpoint.name + `</div>
                        <div class="popup-properties-location">` + mkpoint.city + ` (` + mkpoint.prefecture + `) - ` + mkpoint.elevation + `m</div>
                        <div class="popup-rating"></div>
                    </div>
                </div>
            </div>
            <div class="popup-description">` + mkpoint.description + `</div>
        </div>
        <div class="popup-buttons">
            <button id="showComments" class="mp-button bg-button text-white">Show reviews</button>
        </div>
        <div class="chat-box">
            <div class="msgbox-label">Reviews</div>
            <div class="chat-comments"></div>
            <div class="chat-msgbox">
                <textarea id="mkpointComment" class="fullwidth"></textarea>
                <button id="mkpointCommentSend" class="mp-button bg-button text-white">Post review</button>
            </div>
        </div>`
    }

    comments = () => {
        if (document.querySelector('#mkpointComment')) {
            var $popup = this.popup.getElement()

            // Get comments on this mkpoint
            ajaxGetRequest (this.apiUrl + "?get-comments-mkpoint=" + this.data.id, (response) => {
                // Clear comments if necessary
                if (document.querySelector('.chat-line')) {
                    document.querySelectorAll('.chat-line').forEach( (chatline) => {
                        chatline.remove()
                    } )
                }
                // Display comments
                response.forEach( (comment) => {
                    this.displayComment(comment)
                } )
                // If connected user has already posted a comment, change 'Post review' button to 'Edit review'
                if (document.getElementById('comment-author-' + this.session.id)) {
                    document.getElementById('mkpointCommentSend').innerText = 'Edit review'
                }
            } )

            // Treat posting of a new comment
            var textareaComment   = document.querySelector('#mkpointComment')
            var buttonComment     = document.querySelector('#mkpointCommentSend')
            buttonComment.addEventListener('click', () => {
                let comment = textareaComment.value
                ajaxGetRequest (this.apiUrl + "?add-comment-mkpoint=" + this.data.id + '&content=' + comment, (response) => {
                    // Clear text area
                    textareaComment.value = ''
                    // Display new comment on top
                    this.displayComment(response)
                } )
            } )

            // Show comments on button click
            $popup.querySelector('#showComments').onclick = function () {
                let chatbox = $popup.querySelector('.chat-box')
                let button = $popup.querySelector('#showComments')
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

    displayComment = (comment) => {
        if (document.querySelector('#mkpointComment')) {
            // If comment is already displayed, update it and move it to the top
            if (document.getElementById('comment-author-' + comment.user_id)) {
                let $comment = document.getElementById('comment-author-' + comment.user_id)
                $comment.querySelector('.chat-time').innerText = comment.time
                $comment.querySelector('.chat-message').innerText = comment.content
                let chatComments = this.popup.getElement().querySelector('.chat-comments')
                chatComments.insertBefore($comment, chatComments.firstChild)
            // Else, display it
            } else {
                var chatComments = this.popup.getElement().querySelector('.chat-comments')
                let $comment = document.createElement('div')
                $comment.className = 'chat-line'
                // Set comment background in yellow if author is connected user 
                if (this.session.id === comment.user_id) {
                    $comment.classList.add('bg-admin', 'p-2')
                }
                $comment.id = 'comment-author-' + comment.user_id
                chatComments.insertBefore($comment, chatComments.firstChild)
                let propicContainer = document.createElement('div')
                propicContainer.className = 'round-propic-container'
                propicContainer.style.width = '40px'
                propicContainer.style.height = '40px'
                propicContainer.style.minWidth = '40px'
                $comment.appendChild(propicContainer)
                let propicLink = document.createElement('a')
                propicLink.href = 'http://cyclingfriends.co/users/profile.php?id=' + comment.user_id
                propicContainer.appendChild(propicLink)
                let propic = document.createElement('img')
                propic.className = 'round-propic-img'
                propic.src = comment.propic
                propicLink.appendChild(propic)
                let messageBlock = document.createElement('div')
                messageBlock.className = 'chat-message-block'
                messageBlock.style.marginLeft = '10px'
                $comment.appendChild(messageBlock)
                let login = document.createElement('div')
                login.className = 'chat-login'
                login.innerText = comment.user_login + ' - '
                messageBlock.appendChild(login)
                let time = document.createElement('div')
                time.className = 'chat-time'
                time.innerText = comment.time
                messageBlock.appendChild(time)
                let content = document.createElement('div')
                content.className = 'chat-message'
                content.innerText = comment.content
                messageBlock.appendChild(content)
            }
            // Add stars if voted
            ajaxGetRequest (this.apiUrl + "?check-user-vote=" + comment.mkpoint_id + "&user_id=" + comment.user_id, (response) => {
                if (response != false) {
                    var number = parseInt(response)
                    for (let i = 1; i < number + 1; i++) {
                        let star = document.createElement('div')
                        star.innerText = '★'
                        star.className = 'd-inline selected-star'
                        document.getElementById('comment-author-' + comment.user_id).querySelector('.chat-time').after(star)
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
                            console.log(photo_id)
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
                newPhoto.setAttribute('id', photo.id)
                newPhoto.setAttribute('thumbnailId', number)
                newPhoto.setAttribute('author', photo.user_id)
                newPhoto.src = 'data:image/jpeg;base64,' + photo.file_blob
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
                        console.log(this)
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
                if (photos[photoIndex-1].getAttribute('author') == this.session.id) addDeletePhotoIcon() // ... And add it if connected user is photo author
                
            }
        }
    }

    prepareModal () {
        // If first opening, prepare modal window structure
        if (!document.querySelector('#myModal')) {
            var modalBaseContent = document.createElement('div')
            modalBaseContent.id = 'myModal'
            modalBaseContent.className = 'modal'
            modalBaseContent.innerHTML =
                    `<span class="close cursor" onclick="closeModal()">&times;</span>
                    <div class="modal-block">
                        <a class="prev lightbox-arrow">&#10094;</a>
                        <a class="next lightbox-arrow">&#10095;</a>
                    </div>`
            document.querySelector('.mapboxgl-map').after(modalBaseContent)
        // Else, clear modal window content
        } else {
            document.querySelector('.modal-block').innerHTML =
            `<a class="prev lightbox-arrow">&#10094;</a>
            <a class="next lightbox-arrow">&#10095;</a>`
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
            imgs[i].src = 'data:image/jpeg;base64,' + this.photos[i].file_blob
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
        // Caption display
        var caption = document.createElement('div')
        caption.className = 'lightbox-caption'
        var name = document.createElement('div')
        name.innerText = this.popup.getElement().querySelector('.popup-properties-name').innerText
        name.className = 'lightbox-name'
        caption.appendChild(name)
        var location = document.createElement('div')
        location.innerText = this.popup.getElement().querySelector('.popup-properties-location').innerText
        location.className = 'lightbox-location'
        caption.appendChild(location)
        var description = document.createElement('div')
        description.className = 'lightbox-description'
        description.innerText = this.popup.getElement().querySelector('.popup-description').innerText
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
            demos[i].src = 'data:image/jpeg;base64,' + this.photos[i].file_blob
            column.appendChild(demos[i])
            demosBox.appendChild(column)
        }

        // Load lightbox script for this popup
        var script = document.createElement('script');
        script.src = '/assets/js/lightbox-script.js';
        this.popup.getElement().appendChild(script);
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