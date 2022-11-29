import CFUtils from "/map/class/CFUtils.js"
import Model from "/map/class/Model.js"

export default class Popup extends Model {

    constructor (
        options,
        settings = {
            markerHeight: 10,
            markerRadius: 10,
            linearOffset: 25,
        } ) {
        super()
        this.options
        this.markerHeight = settings.markerHeight
        this.markerRadius = settings.markerRadius
        this.linearOffset = settings.linearOffset
        this.popup = new mapboxgl.Popup(Object.assign(this.defaultOptions, options))
    }
    
    markerHeight = 20
    markerRadius = 10
    linearOffset = 25
    defaultOptions = {
        offset: {
            'top': [0, 0],
            'top-left': [0, 0],
            'top-right': [0, 0],
            'bottom': [0, -this.markerHeight],
            'bottom-left': [this.linearOffset, (this.markerHeight - this.markerRadius + this.linearOffset) * -1],
            'bottom-right': [-this.linearOffset, (this.markerHeight - this.markerRadius + this.linearOffset) * -1],
            'left': [this.markerRadius, (this.markerHeight - this.markerRadius) * -1],
            'right': [-this.markerRadius, (this.markerHeight - this.markerRadius) * -1]
        }, 
        className: 'marker-popup',
        closeOnClick: true
    }
    popup

    getLocation = async () => {
        return new Promise ((resolve, reject) => {
            var lngLat = this.popup.getLngLat()
            var lng = lngLat.lng
            var lat = lngLat.lat
		    ajaxGetRequest ('https://api.mapbox.com/search/v1/reverse/' + lng + ',' + lat + '?language=ja&access_token=' + this.apiKey, (response) => {
                console.log('MAPBOX GEOCODING API USE +1')
                var geolocation = CFUtils.reverseGeocoding(response)
                resolve(geolocation)
            } )
        } )
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
                        <a class="prev nav-link">&#10094;</a>
                        <a class="next nav-link">&#10095;</a>
                    </div>`
            document.querySelector('.mapboxgl-map').after(modalBaseContent)
        // Else, clear modal window content
        } else {
            document.querySelector('.modal-block').innerHTML =
            `<a class="prev nav-link">&#10094;</a>
            <a class="next nav-link">&#10095;</a>`
        }
        
        // Slides display
        var cursor = 0
        var slides = []
        var imgs = []
        var slidesBox = document.createElement('div')
        slidesBox.className = 'slides-box'
        document.querySelector('.modal-block').appendChild(slidesBox)
        this.mkpoints.forEach( (mkpoint) => {
            mkpoint.photos.forEach( (photo) => {
                const distanceFromStart = this.getDistanceFromStart(mkpoint)
                slides[cursor] = document.createElement('div')
                slides[cursor].className = 'mySlides wider-slide'
                // Create number
                let numberText = document.createElement('div')
                numberText.className = 'numbertext'
                numberText.innerHTML = (cursor + 1) + ' / ' + imgNumber
                slides[cursor].appendChild(numberText)
                // Create image
                imgs[cursor] = document.createElement('img')
                imgs[cursor].src = 'data:image/jpeg;base64,' + photo.file_blob
                imgs[cursor].id = 'mkpoint-img-' + photo.id
                imgs[cursor].classList.add('fullwidth')
                slides[cursor].appendChild(imgs[cursor])
                // Create image meta
                var imgMeta = document.createElement('div')
                imgMeta.className = 'mkpoint-img-meta'
                slides[cursor].appendChild(imgMeta)
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
                likes.innerText = photo.likes
                imgMeta.appendChild(likes)
                var period = document.createElement('div')
                period.className = 'mkpoint-period lightbox-period'
                period.classList.add('period-' + photo.month)
                period.innerText = photo.period
                imgMeta.appendChild(period)
                slidesBox.appendChild(slides[cursor])
                // Caption display
                var caption = document.createElement('div')
                caption.className = 'lightbox-caption'
                var name = document.createElement('div')
                name.innerText = 'km ' + (Math.ceil(distanceFromStart * 10) / 10) + ' - ' + mkpoint.name
                name.className = 'lightbox-name'
                caption.appendChild(name)
                var location = document.createElement('div')
                location.innerText = mkpoint.city + ' (' + mkpoint.prefecture + ') - ' + mkpoint.elevation + 'm'
                location.className = 'lightbox-location'
                caption.appendChild(location)
                var description = document.createElement('div')
                description.className = 'lightbox-description'
                description.innerText = mkpoint.description
                caption.appendChild(description)
                slidesBox.appendChild(caption)
                // Display caption on slide hover
                slides[cursor].addEventListener('mouseover', () => {
                    caption.style.visibility = 'visible'
                    caption.style.opacity = '1'
                } )
                slides[cursor].addEventListener('mouseout', () => {
                    caption.style.visibility = 'hidden'
                    caption.style.opacity = '0'
                } )
                cursor++
            } )
        } )
        // Demos display
        cursor = 0
        var demos = []
        var demosBox = document.createElement('div')
        demosBox.className = 'thumbnails-box'
        document.querySelector('.modal-block').appendChild(demosBox)
        this.mkpoints.forEach( (mkpoint) => {
            mkpoint.photos.forEach( (photo) => {
                let column = document.createElement('div')
                column.className = 'column'
                demos[cursor] = document.createElement('img')
                demos[cursor].className = 'demo cursor fullwidth'
                demos[cursor].setAttribute('demoId', cursor + 1)
                demos[cursor].src = 'data:' + photo.file_type + ';base64,' + photo.file_blob
                column.appendChild(demos[cursor])
                demosBox.appendChild(column)
            } )
        } )

        // Load lightbox script for this popup
        var script = document.createElement('script');
        script.src = '/assets/js/lightbox-script.js';
        this.popup.getElement().appendChild(script);

        // Prepare toggle like function
        if (this.popup.getElement().querySelector('#like-button')) this.toggleLike()
    }

    toggleLike () {

        // Get button elements
        var thumbnailButton = this.popup.getElement().querySelector('#like-button')
        var modalButtons = document.querySelectorAll('.like-button-modal')

        // On click, add or remove points to the mkpoint depending of user already liked it or not
        var clickOnThumbnailButton = toggleLike.bind(this)
        thumbnailButton.addEventListener('click', clickOnThumbnailButton)
        var clickOnModalButton = toggleLike.bind(this)
        modalButtons.forEach( (modalButton) => modalButton.addEventListener('click', clickOnModalButton) )

        function toggleLike (e) {

            // Check if clicked on thumbnail button or modal button
            var buttonType = ''
            if (e.target.closest('#like-button')) buttonType = 'thumbnail'
            else if (e.target.closest('.like-button-modal')) buttonType = 'modal'

            // Get image id
            if (buttonType == 'thumbnail') {
                var img_id
                this.popup._content.querySelectorAll('.popup-img').forEach( ($img) => {
                    if ($img.style.display != 'none') img_id = parseInt($img.id)
                } )
            }

            // Get image id
            else if (buttonType == 'modal') {
                var img_id
                document.querySelectorAll('.mySlides img').forEach( ($img) => {
                    if ($img.closest('.mySlides').style.display != 'none') img_id = getIdFromString($img.id)
                } )
            }

            // Get corresponding modal button and counter
            var modalButton = document.querySelector('#mkpoint-img-' + img_id).nextElementSibling.querySelector('.like-button-modal')
            var modalLikeCounter = modalButton.parentElement.querySelector('.mkpoint-img-likes')

            ajaxGetRequest (this.apiUrl + "?togglelike-img=" + img_id, (response) => { // Response contains like data
                if (buttonType == 'thumbnail') var button = thumbnailButton
                else if (buttonType == 'modal') var button = modalButton
                if (button.classList.contains('liked')) {
                    modalButton.classList.remove('liked')
                    modalLikeCounter.innerText = parseInt(modalLikeCounter.innerText) - 1
                    thumbnailButton.classList.remove('liked')
                } else {
                    modalButton.classList.add('liked')
                    modalLikeCounter.innerText = parseInt(modalLikeCounter.innerText) + 1
                    thumbnailButton.classList.add('liked')
                }
            } )
        }

        // To prevent increasing of click events
        this.popup.on('close', () => {
            thumbnailButton.removeEventListener('click', clickOnThumbnailButton)
            modalButtons.forEach( (modalButton) => modalButton.removeEventListener('click', clickOnModalButton) )
        } )
    }

    colorLike = () => {

        // Get image id and button elements
        var img_id
        this.popup._content.querySelectorAll('.popup-img').forEach( ($img) => {
            if ($img.style.display != 'none') img_id = parseInt($img.id)
        } )
        var thumbnailButton = this.popup.getElement().querySelector('#like-button')
        var modalButtons = document.querySelectorAll('.like-button-modal')
        
        // Set thumbnail like button default color depending on if user liked image or not
        ajaxGetRequest (this.apiUrl + "?islike-img=" + img_id, (islike) => {
            if (islike === true) thumbnailButton.classList.add('liked')
            else thumbnailButton.classList.remove('liked')
        } )

        // Set every modal like button default color depending on if user liked image or not
        modalButtons.forEach( (modalButton) => {
            
            // Get image id
            var img_id = getIdFromString(modalButton.closest('.mySlides').querySelector('img').id)

            // Check if liked or not and style button accordingly
            ajaxGetRequest (this.apiUrl + "?islike-img=" + img_id, (islike) => {
                if (islike === true) modalButton.classList.add('liked')
                else modalButton.classList.remove('liked')
            } )
        } )
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

                star.addEventListener('click', (e) => {
                    if (numIsPair(click)) {
                        // On click, send clicked number to API and update rating display
                        var vote = e.target.getAttribute('number')
                        ajaxGetRequest (this.apiUrl + "?set-rating=true&type=" + this.type + "&id=" + this.data.id + "&grade=" + vote, (response) => {
                            ratingInfos = response
                            setRating(ratingInfos)
                        } )
                    } else {
                        // On click, ask API to cancel current vote and update rating display
                        ajaxGetRequest (this.apiUrl + "?cancel-rating=true&type=" + this.type + "&id=" + this.data.id, (response) => {
                            ratingInfos = response
                            setRating(ratingInfos)
                        } )
                    }
                    click++
                } )
            } )
        } )
    }
}