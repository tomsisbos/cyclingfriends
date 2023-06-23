import CFUtils from "/class/utils/CFUtils.js"
import CFSession from "/class/utils/CFSession.js"
import Popup from "/class/maps/Popup.js"
import SceneryLightbox from "/class/maps/scenery/SceneryLightbox.js"
import FadeLoader from "/class/loaders/FadeLoader.js"

export default class SceneryPopup extends Popup {

    /**
     * @param {Object} options Mapbox GL JS popup options
     * @param {Object} data Data to be saved in this instance
     * @param {Object} instanceOptions Options for this popup
     * @param {Boolean} noPopup If this option is set, popup will not be loaded
     */
    constructor (options, data, instanceOptions) {
        super(options, {}, instanceOptions)
        this.data = data

        if (!instanceOptions.noPopup) {
            // Set popup element
            var content = this.setContent(data.scenery)
            this.popup.setHTML(content)
        }

        // Init interactions
        this.init()
    }
    
    apiUrl = '/api/map.php'
    type = 'scenery'
    data
    photos
    lightbox

    setContent (scenery) {

        return `
        <div class="popup-img-container">` +
            this.centerLoader + `
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
                <div class="js-favorite-button" title="この絶景スポットをお気に入りリストに追加する">
                    <span class="iconify" data-icon="mdi:favorite-add" data-width="20" data-height="20"></span>
                </div>
            </div>
        </div>
        <div id="popup-content" class="popup-content">
            <div class="d-flex gap">
                <div class="round-propic-container">
                    <a href="/rider/` + scenery.user_id + `">
                        <img class="round-propic-img" />
                    </a>
                </div>
                <div class="popup-properties">
                    <div class="popup-properties-reference">
                        <div class="popup-properties-name">
                            <a href="/scenery/` + scenery.id + `" target="_blank">` + scenery.name + `</a>
                        </div>
                        <div class="popup-properties-location"></div>
                        <div class="popup-rating"></div>
                        <div class="popup-tags js-tags"></div>
                    </div>
                </div>
            </div>
            <div class="popup-description">` + this.centerLoader + `</div>
        </div>
        <div class="popup-buttons">
            <a id="showReviews" class="cursor-pointer">レビューを表示する...</a>
        </div>
        <div class="chat-box">
            <div class="msgbox-label">レビュー</div>
            <div class="chat-reviews"></div>
            <div class="chat-msgbox">
                <textarea id="sceneryReview" class="fullwidth"></textarea>
                <button id="sceneryReviewSend" class="mp-button bg-button text-white">レビューを投稿</button>
            </div>
        </div>`
    }

    async getDetails (id) {
        return new Promise(async (resolve, reject) => ajaxGetRequest(this.apiUrl + "?scenery-details=" + id, (scenery) => resolve(scenery)))
    }

    async populate () {
        return new Promise(async (resolve, reject) => {

            // Get scenery details
            if (!this.data.scenery.photos) {
                var scenery = await this.getDetails(this.data.scenery.id)
                this.data.scenery = { ...scenery }
            }

            // Build visited icon if necessary
            if (this.data.scenery.isCleared) {
                var visitedIcon = document.createElement('div')
                visitedIcon.id = 'visited-icon'
                visitedIcon.title = 'この絶景スポットを訪れたことがあります。'
                visitedIcon.innerHTML = `
                    <a href="/activity/` + scenery.isCleared + `" target="_blank">
                        <span class="iconify" data-icon="akar-icons:circle-check-fill" data-width="20" data-height="20"></span>
                    </a>
                `
            }

            // Build tagslist
            var tags = ''
            if (this.data.scenery.tags) this.data.scenery.tags.map((tag) => {
                tags += `
                <a target="_blank" href="/tag/` + tag + `">
                    <div class="popup-tag tag-light">#` + CFUtils.getTagString(tag) + `</div>
                </a>`
            } )

            // Add administration panel if connected user has admin rights
            var sessionId = await CFSession.get('id')
            if (scenery.user_id == sessionId) {
                var adminPanel = document.createElement('div')
                adminPanel.id = 'sceneryAdminPanel'
                adminPanel.className = 'popup-content container-admin'
                adminPanel.innerHTML = `
                    <div class="popup-head">管理者ツール</div>
                    <div class="popup-buttons">
                        <button class="mp-button bg-button text-white" id="sceneryEdit">情報編集</button>
                        <button class="mp-button bg-button text-white" id="sceneryMove">位置変更</button>
                        <button class="mp-button bg-danger text-white" id="sceneryDelete">削除</button>
                    </div>
                `
                // Set markerpoint to draggable depending on if user is marker admin and has set edit mode to true or not
                if (this.popup && this.popup._map) var marker = this.getMarker()
                else resolve(false)
                if (marker && this.data.mapInstance.mode == 'edit') marker.setDraggable(true)
                else if (marker && this.data.mapInstance.mode == 'default') marker.setDraggable(false)
                this.popup._content.querySelector('#popup-content').before(adminPanel)
            }

            if (this.data.scenery.isFavorite) this.popup._content.querySelector('.js-favorite-button').classList.add('favoured')
            if (this.data.scenery.isCleared) this.popup._content.querySelector('.popup-icons').appendChild(visitedIcon)
            this.popup._content.querySelector('.popup-properties-location').innerHTML = this.data.scenery.city + ' (' + this.data.scenery.prefecture + ') - ' + this.data.scenery.elevation + 'm'
            this.popup._content.querySelector('.popup-description').innerHTML = this.data.scenery.description
            this.popup._content.querySelector('.js-tags').innerHTML = tags

            resolve(true)
        } )
    }

    init () {
        this.popup.once('open', async () => {

            // Setup general interactions
            this.select()
            this.loadReviews()
            this.loadRating(this.data.scenery)
            
            // Define actions to perform on each popup display
            this.popup.on('open', () => {
                this.unselectMarkers(this.popup._map)
                this.select()
            } )
            const map = this.popup._map
            this.popup.on('close', () => this.unselectMarkers(map))

            await this.populate().then((stillExists) => {
                if (stillExists) {
                    this.displayPhotos()
                    this.loadLightbox()
                    this.popup.on('open', () => {
                        this.loadLightbox()
                        this.colorLike()
                        this.prepareToggleLike()
                    } )
                }
            } )

            // Setup interactions depending on content
            const content = this.popup._content.innerHTML
            if (content.includes('sceneryAdminPanel')) this.sceneryAdmin()
            if (content.includes('target-button')) this.setTarget()
            if (content.includes('js-favorite-button')) this.setFavorite()
            if (content.includes('round-propic-img')) this.addPropic(this.data.scenery.user_id)
        } )
    }

    displayPhotos () {

        var photosContainer = this.popup._content.querySelector('.popup-img-container')
        var photos = this.data.scenery.photos

        const addArrows = async () => {

            // Create and append arrow elements
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

            // Set listeners
            var photoIndex = 1
            var plusPhoto = () => showPhotos(photoIndex += 1)
            var minusPhoto = () => showPhotos(photoIndex -= 1)
            var showPhotos = (n) => {
                photos = this.data.scenery.photos // Use latest photo data
                if (n > photos.length) {photoIndex = 1}
                if (n < 1) {photoIndex = photos.length}
                for (let i = 0; i < photos.length; i++) {
                    photos[i].$element.style.display = 'none'
                    photos[i].$period.style.display = 'none'
                }
                photos[photoIndex - 1].$element.style.display = 'block'
                photos[photoIndex - 1].$period.style.display = 'inline-block'
                // Update like button color on every photo change                
                this.colorLike()
            }
            this.popup._content.querySelector('.small-prev').addEventListener('click', minusPhoto)
            this.popup._content.querySelector('.small-next').addEventListener('click', plusPhoto)
            showPhotos(photoIndex)

            // Add delete photo button if necessary
            if (this.popup._content.querySelector('.deletephoto-button')) this.popup._content.querySelector('.deletephoto-button').remove() // If delete photo button is displayed, remove it...
            var sessionId = await CFSession.get('id')
            if (photos[photoIndex - 1].$element.dataset.author == sessionId) addDeletePhotoIcon() // ... And add it if connected user is photo author
        }

        const removeArrows = () => {
            if (photosContainer.querySelector('.small-prev')) {
                photosContainer.querySelector('.small-prev').remove()
                photosContainer.querySelector('.small-next').remove()
            }
        }

        const addDeletePhotoIcon = () => {
            // If delete photo button is not already displayed, display it
            if (!this.popup._content.querySelector('.deletephoto-button')) {
                var deletePhoto = document.createElement('div')
                deletePhoto.className = 'deletephoto-button admin-icon'
                deletePhoto.innerHTML = '<span class="iconify" data-icon="mdi:image-remove" data-width="20" data-height="20"></span>'
                deletePhoto.title = 'この写真を削除する'
                this.popup._content.querySelector('.popup-icons').appendChild(deletePhoto)
                // Delete photo on click
                deletePhoto.addEventListener('click', () => {
                    var modal = document.createElement('div')
                    modal.classList.add('modal', 'd-flex')
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
                                photo_id = $photo.dataset.id
                                currentPhoto = $photo
                            }
                        } )
                        // Delete photo
                        let popupLoader = new FadeLoader('写真を削除中...')
                        ajaxGetRequest(this.apiUrl + "?delete-scenery-photo=" + photo_id, (response) => {
                            // Remove photo and period
                            currentPhoto.nextSibling.remove() // Period
                            currentPhoto.remove()
                            modal.remove()
                            deleteConfirmationPopup.remove()
                            // Reload photos
                            showResponseMessage(response, {element: this.popup._content})
                            reloadPhotos()
                        }, popupLoader.loader)
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
            photo.$element.src = photo.url
            photosContainer.prepend(photo.$element)
            photo.$period = document.createElement('div')
            photo.$period.classList.add('photo-period', setPeriodClass(photo.month))
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

        // Reload photo and period elements with newest server data
        const reloadPhotos = async () => {
            ajaxGetRequest(this.apiUrl + "?scenery-photos=" + this.data.scenery.id, (newPhotos) => {
                // Update popup instance
                this.data.scenery.photos = newPhotos
                // Clear current photo and period elements
                photosContainer.querySelectorAll('.popup-img').forEach(element => element.remove())
                photosContainer.querySelectorAll('.photo-period').forEach(element => element.remove())
                // Add newest photos
                for (let i = 0; i < newPhotos.length; i++) addPhoto(newPhotos[i], i + 1)
            } )
            removeArrows()
            addArrows()
        }
        
        // Remove loader
        if (photosContainer.querySelector('.loader-center')) photosContainer.querySelector('.loader-center').remove()

        // Add photos to the DOM on first opening
        for (let i = 0; i < photos.length; i++) addPhoto(photos[i], i + 1)

        // Set up add photo button listener
        var form = this.popup._content.querySelector('#addphoto-button-form')
        if (form) form.addEventListener('change', (e) => {

            // Prevents default behavior of the submit button
            e.preventDefault()
            
            // Get form data into queryData and adds tab id
            var newPhotoData = new FormData(form)
            newPhotoData.append('addphoto-button-form', true)
            newPhotoData.append('scenery_id', this.data.scenery.id)

            // Proceed AJAX request and treat data in the callback function
            let popupLoader = new FadeLoader('写真をアップロード中...')
            ajaxPostFormDataRequest(this.apiUrl, newPhotoData, (response) => {
                showResponseMessage(response, {element: this.popup._content})
                if (response.success) reloadPhotos() // Reload photos if succeeded
            }, popupLoader.loader)
        } )

        // First clear container from previously displayed photos
        this.popup._content.querySelectorAll('popup-img').forEach(formerPhoto => formerPhoto.remove())
        // If a new photo has been uploaded, add it
        if (photos.length > document.querySelectorAll('.popup-img').length) {
            addPhoto(photos[photos.length - 1], photos.length - 1)
        }

        if (photos.length > 1) addArrows() // If there is more than one photo in the database, add left and right arrows and attach event listeners to it
        else removeArrows() // else, remove arrows if needed
    }

    // Setup lightbox
    loadLightbox (container = this.popup._map.getContainer()) {
        var lightboxData = {
            container,
            popup: this.popup,
            mapInstance: this.data.mapInstance,
            scenery: this.data.scenery,
            photos: this.data.scenery.photos
        }
        this.lightbox = new SceneryLightbox(lightboxData)
    }

    async loadReviews () {

        // Get reviews on this scenery
        ajaxGetRequest (this.apiUrl + "?get-reviews-scenery=" + this.data.scenery.id, async (reviews) => {
                
            const $popup = this.popup._content

            if ($popup && $popup.querySelector('#sceneryReview')) {
                // Clear reviews if necessary
                if ($popup.querySelector('.chat-line')) {
                    $popup.querySelectorAll('.chat-line').forEach( (chatline) => {
                        chatline.remove()
                    } )
                }
                // Display reviews or a message if there is no review yet
                if (reviews.length > 0) reviews.forEach( (review) => this.displayReview(review))
                else {
                    var $noReviewMessage = document.createElement('div')
                    $noReviewMessage.innerText = 'この絶景スポットはまだレビューがありません。'
                    $noReviewMessage.className = 'chat-default pb-2'
                    $popup.querySelector('.chat-reviews').appendChild($noReviewMessage)
                }
                // If connected user has already posted a review, change 'Post review' button to 'Edit review' and prepopulate text area
                if ($popup.querySelector('#review-author-' + await CFSession.get('id'))) {
                    $popup.querySelector('#sceneryReviewSend').innerText = 'レビューを更新'
                    reviews.forEach(async (review) => {
                        if (review.user.id == await CFSession.get('id')) $popup.querySelector('#sceneryReview').innerText = review.content
                    } )
                }

                // Treat posting of a new review
                var textareaReview   = $popup.querySelector('#sceneryReview')
                var buttonReview     = $popup.querySelector('#sceneryReviewSend')
                if (buttonReview) buttonReview.addEventListener('click', () => {
                    let content = textareaReview.value
                    ajaxGetRequest (this.apiUrl + "?add-review-scenery=" + this.data.scenery.id + '&content=' + content, (review) => {
                        // If content is empty, remove review element and but button text back
                        if (review.content == '') {
                            $popup.querySelector('#review-author-' + review.user.id).remove()
                            $popup.querySelector('#sceneryReviewSend').innerText = 'レビューを投稿'
                        // Else, display new review on top and change button text
                        } else {
                            this.displayReview(review, {new: true})
                            $popup.querySelector('#sceneryReviewSend').innerText = 'レビューを更新'
                        }
                    } )
                } )

                // Show review on button click
                    const reviewsButton = $popup.querySelector('#showReviews')
                if (reviewsButton) reviewsButton.addEventListener('click', () => {
                    const chatbox = $popup.querySelector('.chat-box')
                    if (chatbox.style.visibility != 'visible') {
                        chatbox.style.visibility = 'visible'
                        chatbox.style.height = 'auto'
                        reviewsButton.remove()
                    }
                } )
            }
        } )
    }

    displayReview = async (review, options = {new: false}) => {
        if (document.querySelector('#sceneryReview')) {
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
                let sessionId = await CFSession.get('id') 
                if (sessionId === review.user.id) $review.classList.add('bg-admin', 'p-2')
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
            ajaxGetRequest (this.apiUrl + "?check-user-vote=" + review.entry_id + "&user_id=" + review.user.id, (response) => {
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

    /**
     * Get the marker element corresponding to popup instance data scenery id
     * @returns {mapboxgl.Marker}
     */
    getMarker () {
        var marker
        this.popup._map._markers.forEach( (_marker) => { // Get current marker instance
            if (_marker.getElement().id == 'scenery' + this.data.scenery.id) marker = _marker
        } )
        return marker
    }

    setFavorite () {
        if (this.popup._content) var $button = this.popup._content.querySelector('.js-favorite-button')
        else var $button = document.querySelector('.js-favorite-button')
        $button.addEventListener('click', () => {
            var type = 'scenery'
            ajaxGetRequest ('/api/favorites.php' + '?toggle-' + type + '=' + this.data.scenery.id, (response) => {
                showResponseMessage(response, {
                    element: document.querySelector('.main'),
                    absolute: true
                } )
                this.popup.once('close', hideResponseMessage)
                var marker = this.getMarker()
                // Update data in map instance for ensuring display update
                marker.isFavorite = !marker.isfavorite
                
                // Update map instance properties
                const mapInstance = this.data.mapInstance
                var scenery = mapInstance.mapdata.sceneries.find(scenery => scenery.id == this.data.scenery.id)
                scenery.isFavorite = !scenery.isFavorite
                var key
                for (let i = 0; i < mapInstance.sceneriesMarkerCollection.length; i++) {
                    if (mapInstance.sceneriesMarkerCollection[i]._element.dataset.id == scenery.id) key = i
                }
                mapInstance.sceneriesMarkerCollection[key].isFavorite = !mapInstance.sceneriesMarkerCollection[key].isFavorite

                // Toggle button and marker element class
                $button.classList.toggle('favoured')
                marker.getElement().classList.toggle('favoured-marker')
            } )
        } )
    }
    
    sceneryAdmin () {
        const mapInstance = this.data.mapInstance
    // Edit
        var editButton = this.popup._content.querySelector('#sceneryEdit')
        // On click on edit button, change text into input fields and change Edit button into Save button
        editButton.addEventListener('click', () => {
            // Change name and description into input fields
            var $name = this.popup._content.querySelector('.popup-properties-name')
            var $description = this.popup._content.querySelector('.popup-description')
            var inputName = document.createElement('input')
            inputName.setAttribute('type', 'text')
            inputName.id = 'scenery-edit-name'
            inputName.classList.add('popup-properties-name', 'admin-field')
            inputName.value = $name.innerText
            var textareaDescription = document.createElement('textarea')
            textareaDescription.id = 'scenery-edit-description'
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
                if (this.data.scenery.tags.includes(tag)) var checked = 'checked'
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
                ajaxGetRequest (this.apiUrl + "?edit-scenery=" + this.data.scenery.id + "&name=" + name + "&description=" + description + '&tags=' + tagsString, (response) => {
                    saveButton.remove()
                    editButton.style.display = 'block'
                    inputName.remove()
                    $name.style.display = 'block'
                    $name.innerHTML = '<a target="_blank" href="/scenery/' + this.data.scenery.id + '">' + response.name + '</a>'
                    textareaDescription.remove()
                    $description.style.display = 'block'
                    $description.innerText = response.description
                    checkboxesContainer.remove()
                    tagsContainer.style.display = 'block'
                    tagsContainer.innerHTML = ''
                    if (response.tags) response.tags.map(tag => {
                        tagsContainer.innerHTML += `<div class="popup-tag tag-light">#` + CFUtils.getTagString(tag) + `</div>`
                    } )
                    // Reload map instance sceneries data
                    mapInstance.loadSceneries()
                } )
            } )
        } )
    // Move
        var moveScenery = () => {
            moveButton.style.opacity = '70%'
            moveButton.innerText = '確定'
            moveButton.onclick = quitMoveScenery
            $marker.classList.add('moving-marker')
            marker.setDraggable(true)
            this.popup.on('close', quitMoveScenery)
            marker.on('dragend', () => {
                ajaxGetRequest (this.apiUrl + "?scenery-dragged=" + $marker.dataset.id + "&lng=" + marker._lngLat.lng + "&lat=" + marker._lngLat.lat, afterSceneryUpdate)
                function afterSceneryUpdate (response) {
                    /*
                    // update sceneriesMarkerCollection
                    globalmap.sceneriesMarkerCollection
                    */
                }
            } )
        }
        var quitMoveScenery = () => {
            marker.setDraggable(false)
            marker.getElement().classList.remove('moving-marker')
            moveButton.style.opacity = '100%'
            moveButton.innerText = '編集'
            moveButton.onclick = moveScenery
        }
        if (this.popup._map) {
            var marker = this.getMarker()
            var $marker = marker.getElement()
            var moveButton = this.popup._content.querySelector('#sceneryMove')
            moveButton.onclick = moveScenery
        }
        
    // Delete
        var deleteButton = this.popup._content.querySelector('#sceneryDelete')
        deleteButton.addEventListener('click', async () => {
            var answer = await openConfirmationPopup('この絶景スポットが削除されます。宜しいですか？')
            if (answer) { // If yes, remove the scenery and close the popup
                ajaxGetRequest (this.apiUrl + "?delete-scenery=" + this.data.scenery.id, (response) => {} )
                // Remove current marker
                document.querySelector('#scenery' + this.data.scenery.id).remove()
                // Also remove from map instance
                mapInstance.mapdata.sceneries.forEach(scenery => {
                    if (scenery.id == this.data.scenery.id) mapInstance.mapdata.sceneries.splice(mapInstance.mapdata.sceneries.indexOf(scenery), 1)
                } )
                this.popup._content.remove()
            }
        } )
    }

    select () {
        var $map = this.data.mapInstance.$map
        $map.querySelector('#scenery' + this.data.scenery.id).querySelector('.scenery-icon').classList.add('selected-marker')
        // If a table or slider is displayed, also select corresponding entries
        if (document && document.querySelector('.spec-table #scenery' + this.data.scenery.id)) document.querySelector('.spec-table #scenery' + this.data.scenery.id).classList.add('selected-entry')
        if (document && document.querySelector('.rt-slider #scenery' + this.data.scenery.id)) document.querySelector('.rt-slider #scenery' + this.data.scenery.id + ' img').classList.add('selected-marker')
    }
            
}