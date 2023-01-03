import Popup from "/map/class/Popup.js"
import SceneryLightbox from "/map/class/scenery/SceneryLightbox.js"
import CFUtils from "/map/class/CFUtils.js"

export default class SceneryPopup extends Popup {

    constructor (options, data, instanceOptions) {
        super(options, {}, instanceOptions)
        this.data = data

        if (!instanceOptions.noPopup) {
            // Set popup element
            var content = this.setContent(data.mkpoint)
            if (instanceOptions.admin) { // Build and insert admin panel if admin option is true
                var adminPanel = `
                    <div id="mkpointAdminPanel" class="popup-content container-admin">
                        <div class="popup-head">管理者ツール</div>
                        <div class="popup-buttons">
                            <button class="mp-button bg-button text-white" id="mkpointEdit">情報編集</button>
                            <button class="mp-button bg-button text-white" id="mkpointMove">位置変更</button>
                            <button class="mp-button bg-danger text-white" id="mkpointDelete">削除</button>
                        </div>
                    </div>
                `
                // Insert admin panel before the popup content
                var index = content.indexOf('<div id="popup-content"')
                content = content.slice(0, index) + adminPanel + content.slice(index)
            }
            this.popup.setHTML(content)
        }

        // Init interactions
        this.init()
    }
    
    apiUrl = '/api/map.php'
    type = 'mkpoint'
    data
    photos
    lightbox

    setContent (mkpoint) {
        var visitedIcon = ''
        if (mkpoint.isCleared) {
            visitedIcon = `
                <div id="visited-icon" title="この絶景スポットを訪れたことがあります。">
                    <a href="/activity/` + mkpoint.isCleared + `" target="_blank">
                        <span class="iconify" data-icon="akar-icons:circle-check-fill" data-width="20" data-height="20"></span>
                    </a>
                </div>
            `
        }


        if (mkpoint.isFavorite) var favoriteButtonClass = ' favoured'
        else var favoriteButtonClass = ''

        // Build tagslist
        var tags = ''
        if (mkpoint.tags) mkpoint.tags.map( (tag) => {
            tags += `
            <a target="_blank" href="/tag/` + tag + `">
                <div class="popup-tag tag-dark">#` + CFUtils.getTagString(tag) + `</div>
            </a>`
        } )

        return `
        <div class="popup-img-container">
            <div class="popup-icons">
                <div id="target-button" title="この絶景スポットに移動する。">
                    <span class="iconify" data-icon="icomoon-free:target" data-width="20" data-height="20"></span>
                </div>
                <form enctype="multipart/form-data" method="post" id="addphoto-button-form">
                    <label for="addphoto-button" title="この絶景スポットに写真を追加する">
                        <span class="iconify" data-icon="ic:baseline-add-a-photo" data-width="20" data-height="20"></span>
                    </label>
                    <input id="addphoto-button" type="file" name="file" />
                    <input type="hidden" name="MAX_FILE_SIZE" value="10000000" />
                </form>
                <div id="like-button" title="この写真に「いいね」を付ける">
                    <span class="iconify" data-icon="mdi:heart-plus" data-width="20" data-height="20"></span>
                </div>
                <div class="js-favorite-button` + favoriteButtonClass + `" title="この絶景スポットをお気に入りリストに追加する">
                    <span class="iconify" data-icon="mdi:favorite-add" data-width="20" data-height="20"></span>
                </div>` + 
                visitedIcon + `
            </div>
        </div>
        <div id="popup-content" class="popup-content">
            <div class="d-flex gap">
                <div class="round-propic-container">
                    <a href="/rider/` + mkpoint.user.id + `">
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
            <div class="js-tags">` + 
                tags + `
            </div>
        </div>
        <div class="popup-buttons">
            <button id="showReviews" class="mp-button bg-button text-white">レビューを表示</button>
        </div>
        <div class="chat-box">
            <div class="msgbox-label">レビュー</div>
            <div class="chat-reviews"></div>
            <div class="chat-msgbox">
                <textarea id="mkpointReview" class="fullwidth"></textarea>
                <button id="mkpointReviewSend" class="mp-button bg-button text-white">レビューを投稿</button>
            </div>
        </div>`
    }

    init () {
        this.popup.once('open', async () => {

            // Setup general interactions
            this.select()
            await this.loadPhotos().then(() => this.loadLightbox())
            this.loadReviews()
            this.loadRating(this.data.mkpoint)
            this.colorLike()
            this.prepareToggleLike()

            // Setup interactions depending on content
            var content = this.popup.getElement().innerHTML
            if (content.includes('mkpointAdminPanel')) this.mkpointAdmin()
            if (content.includes('target-button')) this.setTarget()
            if (content.includes('js-favorite-button')) this.setFavorite()
            if (content.includes('round-propic-img')) this.addPropic()

            // Define actions to perform on each popup display
            this.popup.on('open', () => {
                this.data.mapInstance.unselectMarkers()
                this.loadLightbox()
                this.colorLike()
                this.prepareToggleLike()
            } )
            this.popup.on('close', () => {
                this.data.mapInstance.unselectMarkers()
            } )
        } )
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

                var addDeletePhotoIcon = () => {
                    // If delete photo button is not already displayed, display it
                    if (!this.popup.getElement().querySelector('.deletephoto-button')) {
                        var deletePhoto = document.createElement('div')
                        deletePhoto.className = 'deletephoto-button admin-icon'
                        deletePhoto.innerHTML = '<span class="iconify" data-icon="mdi:image-remove" data-width="20" data-height="20"></span>'
                        deletePhoto.title = 'この写真を削除する'
                        this.popup.getElement().querySelector('.popup-icons').appendChild(deletePhoto)
                        // Delete photo on click
                        deletePhoto.addEventListener('click', () => {
                            var modal = document.createElement('div')
                            modal.classList.add('modal', 'd-block')
                            document.querySelector('body').appendChild(modal)
                            // Remove modal on clicking outside popup
                            modal.addEventListener('click', (e) => {
                                var eTarget = e ? e.target : event.srcElement
                                if ((eTarget == this.popup) || (eTarget == modal)) modal.remove()
                            })
                            var deleteConfirmationPopup = document.createElement('div')
                            deleteConfirmationPopup.classList.add('popup')
                            deleteConfirmationPopup.innerHTML = 'この写真が削除されます。宜しいですか？<div class="d-flex justify-content-between"><div id="yes" class="mp-button bg-darkred text-white">はい</div><div id="no" class="mp-button bg-darkgreen text-white">いいえ</div></div>'
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
                                ajaxGetRequest (this.apiUrl + "?delete-photo-mkpoint=" + this.data.mkpoint.id + "&photo=" + photo_id, () => {
                                    // Remove photo and period
                                    currentPhoto.nextSibling.remove() // Period
                                    currentPhoto.remove()
                                    modal.remove()
                                    deleteConfirmationPopup.remove()
                                    // Reload photos
                                    ajaxGetRequest (this.apiUrl + "?mkpoint-photos=" + this.data.mkpoint.id, displayPhotos.bind(this))
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

                // Add photos to the DOM on first opening
                for (let i = 0; i < this.photos.length; i++) addPhoto(this.photos[i], i + 1)

                // Set on first opening
                if (!this.popup.getElement().querySelector('.popup-img')) {
                    // Handle listener to the add photo button
                    var form = this.popup.getElement().querySelector('#addphoto-button-form')
                    form.addEventListener('change', (e) => {

                        // Prevents default behavior of the submit button
                        e.preventDefault()
                        
                        // Get form data into queryData and adds tab id
                        var newPhotoData = new FormData(form)
                        newPhotoData.append('addphoto-button-form', true)
                        newPhotoData.append('mkpoint_id', this.data.mkpoint.id)
                        
                        // Proceed AJAX request and treat data in the callback function
                        ajaxPostFormDataRequest(this.apiUrl, newPhotoData, (response) => {
            
                            // In case of error, display corresponding error message
                            if (response.error) {
        
                                console.log('error')
                                console.log(response)
            
                                // If there is already a message displayed, remove it before
                                if (document.querySelector('.error-block')) document.querySelector('.error-block').remove()
                                var errorDiv = document.createElement('div')
                                errorDiv.classList.add('error-block', 'fullwidth', 'm-0', 'p-2')
                                var errorMessage = document.createElement('p')
                                errorMessage.innerHTML = response.error
                                errorMessage.classList.add('error-message')
                                errorDiv.appendChild(errorMessage)
                                document.querySelector('.mapboxgl-popup-content').prepend(errorDiv)
            
                            } else {
            
                                // If upload process went successfully, remove the error message if one is displayed
                                if (document.querySelector('.error-block')) document.querySelector('.error-block').remove()
            
                                // Reload photos
                                ajaxGetRequest (this.apiUrl + "?mkpoint-photos=" + this.data.mkpoint.id, displayPhotos.bind(this))
                            }
                        } )
                    } )
                }

                // First clear container from previously displayed photos
                this.popup.getElement().querySelectorAll('popup-img').forEach(formerPhoto => formerPhoto.remove())
                // If a new photo has been uploaded, add it
                if (this.photos.length > document.querySelectorAll('.popup-img').length) {
                    addPhoto(this.photos[this.photos.length - 1], this.photos.length - 1)
                }

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

                    // Change the color of the like button depending on if new photo has been liked or not
                    this.colorLike()

                    // Add delete photo button if necessary
                    if (this.popup.getElement().querySelector('.deletephoto-button')) this.popup.getElement().querySelector('.deletephoto-button').remove() // If delete photo button is displayed, remove it...
                    if (photos[photoIndex-1].$element.dataset.author == sessionStorage.getItem('session-id')) addDeletePhotoIcon() // ... And add it if connected user is photo author

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
        this.lightbox = new SceneryLightbox(lightboxData)
    }

    loadReviews = () => {
        if (document.querySelector('#mkpointReview')) {

            // Get reviews on this mkpoint
            ajaxGetRequest (this.apiUrl + "?get-reviews-mkpoint=" + this.data.mkpoint.id, (reviews) => {
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
                    $noReviewMessage.innerText = 'この絶景スポットはまだレビューがありません。'
                    $noReviewMessage.className = 'chat-default pb-2'
                    document.querySelector('.chat-reviews').appendChild($noReviewMessage)
                }
                // If connected user has already posted a review, change 'Post review' button to 'Edit review' and prepopulate text area
                if (document.querySelector('#review-author-' + sessionStorage.getItem('session-id'))) {
                    document.querySelector('#mkpointReviewSend').innerText = 'レビューを更新'
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
                ajaxGetRequest (this.apiUrl + "?add-review-mkpoint=" + this.data.mkpoint.id + '&content=' + content, (review) => {
                    // If content is empty, remove review element and but button text back
                    if (review.content == '') {
                        document.querySelector('#review-author-' + review.user.id).remove()
                        document.querySelector('#mkpointReviewSend').innerText = 'レビューを投稿'
                    // Else, display new review on top and change button text
                    } else {
                        this.displayReview(review, {new: true})
                        document.querySelector('#mkpointReviewSend').innerText = 'レビューを更新'
                    }
                } )
            } )

            // Show review on button click
            if (document.querySelector('#showReviews')) document.querySelector('#showReviews').onclick = function () {
                let chatbox = document.querySelector('.chat-box')
                let button = document.querySelector('#showReviews')
                if (button.innerText == '表示') {
                    chatbox.style.visibility = 'visible'
                    chatbox.style.height = 'auto'
                    button.innerText = '非表示'
                } else if (button.innerText == '非表示') {
                    chatbox.style.visibility = 'hidden'
                    chatbox.style.height = '0px'
                    button.innerText = '表示'
                }
            }
        }
    }

    displayReview = (review, options = {new: false}) => {
        if (document.querySelector('#mkpointReview')) {
            // If review is already displayed, update it and move it to the top
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

    setFavorite () {
        if (this.popup.getElement()) var $button = this.popup.getElement().querySelector('.js-favorite-button')
        else var $button = document.querySelector('.js-favorite-button')
        $button.addEventListener('click', () => {
            var type = 'scenery'
            ajaxGetRequest ('/api/favorites.php' + '?toggle-' + type + '=' + this.data.mkpoint.id, (response) => {
                showResponseMessage(response, {
                    element: document.querySelector('.main'),
                    absolute: true
                } )
                this.popup.once('close', hideResponseMessage)
                var marker
                this.popup._map._markers.forEach( (_marker) => { // Get current marker instance
                    if (_marker.getElement().id == 'mkpoint' + this.data.mkpoint.id) marker = _marker
                } )
                // Update data in map instance for ensuring display update
                marker.isFavorite = !marker.isfavorite
                
                // Update map instance properties
                const mapInstance = this.data.mapInstance
                var mkpoint = mapInstance.data.mkpoints.find(mkpoint => mkpoint.id == this.data.mkpoint.id)
                mkpoint.isFavorite = !mkpoint.isFavorite
                var key
                for (let i = 0; i < mapInstance.mkpointsMarkerCollection.length; i++) {
                    if (mapInstance.mkpointsMarkerCollection[i]._element.dataset.id == mkpoint.id) key = i
                }
                mapInstance.mkpointsMarkerCollection[key].isFavorite = !mapInstance.mkpointsMarkerCollection[key].isFavorite

                // Toggle button and marker element class
                $button.classList.toggle('favoured')
                marker.getElement().classList.toggle('favoured-marker')
            } )
        } )
    }

    addPropic = async () => this.popup.getElement().querySelector('.round-propic-img').src = await this.loadPropic(this.data.mkpoint.user.id)
    
    mkpointAdmin () {
        const mapInstance = this.data.mapInstance
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
            // Change tags into checkbox fields
            var tagsContainer = document.querySelector('.js-tags')
            tagsContainer.style.display = 'none'
            var checkboxesContainer = document.createElement('div')
            checkboxesContainer.className = 'js-tags'
            var $tags = ''
            this.tags.forEach(tag => {
                if (this.data.mkpoint.tags.includes(tag)) var checked = 'checked'
                else var checked = ''
                $tags += `
                    <div class="mp-checkbox">
                        <input type="checkbox" data-name="` + tag + `" id="tag` + tag + `" class="js-segment-tag" ` + checked + `/>
                        <label for="tag` + tag + `">` + CFUtils.getTagString(tag) + `</label>
                    </div>
                `
            } )
            checkboxesContainer.innerHTML = $tags
            tagsContainer.after(checkboxesContainer)
            // Change edit button into save button
            var saveButton = document.createElement('div')
            saveButton.classList.add('mp-button', 'bg-button', 'text-white')
            saveButton.innerText = '保存'
            editButton.after(saveButton)
            editButton.style.display = 'none'
            saveButton.addEventListener('click', () => {
                let name = inputName.value
                let description = textareaDescription.value
                let tags = []
                checkboxesContainer.querySelectorAll('input').forEach(checkbox => {
                    if (checkbox.checked) tags.push(checkbox.dataset.name)
                } )
                let tagsString = tags.join()
                ajaxGetRequest (this.apiUrl + "?edit-mkpoint=" + this.data.mkpoint.id + "&name=" + name + "&description=" + description + '&tags=' + tagsString, (response) => {
                    saveButton.remove()
                    editButton.style.display = 'block'
                    inputName.remove()
                    $name.style.display = 'block'
                    $name.innerHTML = '<a target="_blank" href="/scenery/' + this.data.mkpoint.id + '">' + response.name + '</a>'
                    textareaDescription.remove()
                    $description.style.display = 'block'
                    $description.innerText = response.description
                    checkboxesContainer.remove()
                    tagsContainer.style.display = 'block'
                    tagsContainer.innerHTML = ''
                    if (response.tags) response.tags.map(tag => {
                        tagsContainer.innerHTML += `<div class="popup-tag tag-dark">#` + CFUtils.getTagString(tag) + `</div>`
                    } )
                    // Reload map instance mkpoints data
                    mapInstance.loadMkpoints()
                } )
            } )
        } )
    // Move
        var moveMkpoint = () => {
            moveButton.style.opacity = '70%'
            moveButton.innerText = '確定'
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
            moveButton.innerText = '編集'
            moveButton.onclick = moveMkpoint
        }
        var marker
        this.popup._map._markers.forEach( (_marker) => { // Get current marker instance
            if (_marker.getElement().id == 'mkpoint' + this.data.mkpoint.id) marker = _marker
        } )
        var $marker = marker.getElement()
        var moveButton = this.popup.getElement().querySelector('#mkpointMove')
        moveButton.onclick = moveMkpoint
        
    // Delete
        var deleteButton = this.popup.getElement().querySelector('#mkpointDelete')
        deleteButton.addEventListener('click', async () => {
            var answer = await openConfirmationPopup('この絶景スポットが削除されます。宜しいですか？')
            if (answer) { // If yes, remove the mkpoint and close the popup
                ajaxGetRequest (this.apiUrl + "?delete-mkpoint=" + this.data.mkpoint.id, (response) => { console.log(response) } )
                mapInstance.data.mkpoints.forEach(mkpoint => {
                    if (mkpoint.id === this.data.mkpoint.id) mapInstance.data.mkpoints.splice(mapInstance.data.mkpoints.indexOf(mkpoint), 1)
                } )
                document.querySelector('#mkpoint' + this.data.mkpoint.id).remove()
                this.popup.getElement().remove()
            }
        } )
    }

    select () {
        var $map = this.data.mapInstance.$map
        $map.querySelector('#mkpoint' + this.data.mkpoint.id).querySelector('.mkpoint-icon').classList.add('selected-marker')
        // If a table or slider is displayed, also select corresponding entries
        if (document && document.querySelector('.spec-table #mkpoint' + this.data.mkpoint.id)) document.querySelector('.spec-table #mkpoint' + this.data.mkpoint.id).classList.add('selected-entry')
        if (document && document.querySelector('.rt-slider #mkpoint' + this.data.mkpoint.id)) document.querySelector('.rt-slider #mkpoint' + this.data.mkpoint.id + ' img').classList.add('selected-marker')
    }
            
}