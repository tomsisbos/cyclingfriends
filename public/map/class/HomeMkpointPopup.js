import MkpointPopup from "/map/class/MkpointPopup.js"
import CFUtils from "/map/class/CFUtils.js"

export default class HomeMkpointPopup extends MkpointPopup {

    constructor () {
        super()
    }

    setPopupContent (mkpoint) {

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
        ajaxGetRequest (this.apiUrl + "?mkpoint-photos=" + this.data.id, (response) => {

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

            if (!this.popup.getElement().querySelector('.popup-img')) {
                // Add photos to the DOM
                for (let i = 0; i < response.length; i++) {
                    addPhoto(response[i], i + 1)
                }
            }

            // Display first photo and period by default
            document.querySelector('.popup-img').style.display = 'block'
            document.querySelector('.mkpoint-period').style.display = 'block'

            // Set modal
            this.prepareModal()
            
            // Set slider system
            var setThumbnailSlider = setThumbnailSlider.bind(this)
            setThumbnailSlider(1)

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
                newPhotoPeriod.classList.add('mkpoint-period', setPeriodClass(photo.month))
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
                    }
                    
                    this.popup.getElement().querySelector('.small-prev').addEventListener('click', minusPhoto)
                    this.popup.getElement().querySelector('.small-next').addEventListener('click', plusPhoto)
                    showPhotos(photoIndex)
    
                // If there is only one photo in the database, remove arrows if needed
                } else {
                    removeArrows()
                }
            }
        }, this.loader)
    }

    async prepareModal () {

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
        var captionContent = document.createElement('div')
        captionContent.className = 'caption-content'
        var name = document.createElement('div')
        name.innerText = this.data.name
        name.className = 'lightbox-name'
        captionContent.appendChild(name)
        var location = document.createElement('div')
        location.innerText = this.data.city + ' (' + this.data.prefecture + ') - ' + this.data.elevation + 'm'
        location.className = 'lightbox-location'
        captionContent.appendChild(location)
        var description = document.createElement('div')
        description.className = 'lightbox-description'
        description.innerText = this.data.description
        captionContent.appendChild(description)
        caption.appendChild(captionContent)
        slidesBox.appendChild(caption)
        // Display caption on slides box hover
        slidesBox.addEventListener('mouseover', () => {
            caption.style.visibility = 'visible'
            caption.style.opacity = '1'
        } )
        slidesBox.addEventListener('mouseout', () => {
            caption.style.visibility = 'hidden'
            caption.style.opacity = '0'
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
}