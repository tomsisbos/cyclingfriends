import Popup from '/map/class/Popup.js'

export default class SceneryLightbox extends Popup {

    constructor(data) {
        super({}, {}, {noSession: true})
        this.data = data

        this.build()
        this.prepare()
    }

    data
    modal
    modalBlock
    slideIndex

    build () {

        const photos = this.data.photos

        // Prepare arrows
        if (photos.length > 1) {
            var prevArrow = document.createElement('a')
            prevArrow.className = 'prev lightbox-arrow'
            prevArrow.innerHTML = '&#10094;'
            var nextArrow = document.createElement('a')
            nextArrow.className = 'next lightbox-arrow'
            nextArrow.innerHTML = '&#10095;'
        }

        // Prepare modal window structure
        this.modal = document.createElement('div')
        this.modal.id = 'modal'
        this.modal.className = 'modal'
        var closeButton = document.createElement('span')
        closeButton.className = "close cursor"
        closeButton.addEventListener('click', () => this.close())
        closeButton.innerHTML = '&times;'
        this.modalBlock = document.createElement('div')
        this.modalBlock.className = "modal-block"
        this.modal.appendChild(closeButton)
        this.modal.appendChild(this.modalBlock)
        this.data.container.appendChild(this.modal)
        // If more than one photo, display arrows
        if (photos.length > 1) {
            this.modalBlock.appendChild(prevArrow)
            this.modalBlock.appendChild(nextArrow)
        }

        // Modal block display
        var slides = []
        var imgs = []
        var slidesBox = document.createElement('div')
        slidesBox.className = 'slides-box'
        this.modalBlock.appendChild(slidesBox)
        
        // Caption display
        var caption = document.createElement('div')
        caption.className = 'lightbox-caption'
        var captionRow = document.createElement('div')
        captionRow.className = 'lightbox-row'
        var propicContainer = document.createElement('a')
        propicContainer.className = 'round-propic-container'
        propicContainer.setAttribute('target', '_blank')
        var propicElement = document.createElement('img')
        propicElement.className = 'round-propic-img'
        propicElement.style.backgroundColor = '#eee'
        propicContainer.appendChild(propicElement)
        var captionContent = document.createElement('div')
        captionContent.className = 'caption-content'
        var name = document.createElement('div')
        name.innerText = this.data.mkpoint.name
        name.className = 'lightbox-name'
        captionContent.appendChild(name)
        var location = document.createElement('div')
        location.innerText = this.data.mkpoint.city + ' (' + this.data.mkpoint.prefecture + ') - ' + this.data.mkpoint.elevation + 'm'
        location.className = 'lightbox-location'
        captionContent.appendChild(location)
        var description = document.createElement('div')
        description.className = 'lightbox-description'
        description.innerText = this.data.mkpoint.description
        captionContent.appendChild(description)
        captionRow.appendChild(propicContainer)
        captionRow.appendChild(captionContent)
        caption.appendChild(captionRow)
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

        // Slides display
        var slides = []
        var imgs = []
        for (let i = 0; i < photos.length; i++) {
            slides[i] = document.createElement('div')
            slides[i].className = 'mySlides wider-slide'
            // Create number
            let numberText = document.createElement('div')
            numberText.className = 'numbertext'
            numberText.innerHTML = (i + 1) + ' / ' + photos.length
            slides[i].appendChild(numberText)
            // Create image
            imgs[i] = document.createElement('img')
            imgs[i].src = photos[i].url
            imgs[i].id = 'mkpoint-img-' + photos[i].id
            imgs[i].classList.add('fullwidth')
            slides[i].appendChild(imgs[i])
            // Create image meta
            var imgMeta = document.createElement('div')
            imgMeta.className = 'mkpoint-img-meta'
            slides[i].appendChild(imgMeta)
            // Append like button
            var likeButton = document.createElement('div')
            likeButton.className = 'like-button-modal'
            likeButton.style.color = 'white'
            likeButton.setAttribute('title', 'この写真に「いいね」を付ける')
            var likeIcon = document.createElement('span')
            likeIcon.className = 'iconify'
            likeIcon.dataset.icon = 'mdi:heart-plus'
            likeIcon.dataset.width = '40'
            likeIcon.dataset.height = '40'
            likeButton.appendChild(likeIcon)
            imgMeta.appendChild(likeButton)
            var likes = document.createElement('div')
            likes.className = 'mkpoint-img-likes'
            likes.innerText = photos[i].likes
            imgMeta.appendChild(likes)
            var period = document.createElement('div')
            period.className = 'mkpoint-period lightbox-period'
            period.classList.add('period-' + photos[i].month)
            period.innerText = photos[i].period
            imgMeta.appendChild(period)
            slidesBox.appendChild(slides[i])
        }
        // Demos display
        var demos = []
        var demosBox = document.createElement('div')
        demosBox.className = 'thumbnails-box'
        this.modalBlock.appendChild(demosBox)
        for (let i = 0; i < photos.length; i++) {
            let column = document.createElement('div')
            column.className = 'column'
            demos[i] = document.createElement('img')
            demos[i].className = 'demo cursor fullwidth'
            demos[i].dataset.id = i + 1
            demos[i].src = photos[i].url
            column.appendChild(demos[i])
            demosBox.appendChild(column)
        }
        
        // Prepare toggle like function
        if (this.data.popup._content.querySelector('#like-button')) this.prepareToggleLike()

        // Remove on popup closing
        this.data.popup.on('close', () => this.modal.remove())
    }

    prepare () {

        // Display slide on click on a demo
        var demos = this.modal.querySelectorAll('.demo')
        demos.forEach(demo => {
            demo.addEventListener('click', (e) => {
                let id = parseInt(e.target.dataset.id)
                this.setSlide(id)
            } )
        } )

        this.slideIndex = 1

        // Set keyboard navigation
        var prev = this.modal.querySelector('.prev.lightbox-arrow')
        var next = this.modal.querySelector('.next.lightbox-arrow')
        if (prev) prev.addEventListener('click', () => this.plusSlide(-1))
        if (next) next.addEventListener('click', () => this.plusSlide(1))

        // Using onkeydown property rather than addEventListener prevents from adding a new listener on document each time a popup is opened.
        var nav = this.modal.querySelector('.lightbox-arrow')
        document.onkeydown = (e) => {
            if (this.modal && this.modal.style.display !== 'none') { // If modal is currently displayed
                if (e.composedPath()[0].localName !== 'input') { // If focus is not on input
                    if (nav && e.code == 'ArrowLeft') {
                        e.preventDefault()
                        this.plusSlide(-1)
                    } else if (nav && e.code == 'ArrowRight') {
                        e.preventDefault()
                        this.plusSlide(1)
                    } else if (e.code == 'Escape') {
                        e.preventDefault()
                        this.close()
                    }
                }
            }
        }
    }

    open (id) {
        this.modal.style.display = "block"

        // Close on clicking outside modal-block
        this.modal.addEventListener('click', (e) => {
            if ((e.target == this.modalBlock) || (e.target == this.modal)) this.close()
        } )
        
        this.setSlide(id)
    }

    close = () => this.modal.style.display = "none"

    setSlide = (n) => this.show(this.slideIndex = n)

    plusSlide = (n) => this.show(this.slideIndex += n)

    show (n) {
        var i;
        var demos = this.modal.querySelectorAll('.demo')
        var slides = this.modal.querySelectorAll('.mySlides')
        var slideImgs = this.modal.querySelectorAll('.mySlides img')
        if (this.modal.querySelector('.js-name')) var names = this.modal.querySelectorAll('.js-name')
        if (this.modal.querySelector('.js-caption')) var captions = this.modal.querySelectorAll('.js-caption')
        if (n > slides.length) this.slideIndex = 1
        if (n < 1) this.slideIndex = slides.length
        for (i = 0; i < slides.length; i++) slides[i].style.display = "none"
        for (i = 0; i < demos.length; i++) demos[i].className = demos[i].className.replace(" active", "")
        if (this.modal.querySelector('.js-name')) {
            for (i = 0; i < names.length; i++) {
                names[i].style.display = "none"
            }
        }
        if (this.modal.querySelector('.js-caption')) {
            for (i = 0; i < captions.length; i++) {
                captions[i].style.display = "none"
            }
        }
        slides[this.slideIndex - 1].style.display = "block"
        demos[this.slideIndex - 1].className += " active"
        if (this.modal.querySelector('.js-name')) names[this.slideIndex - 1].style.display = "block"
        if (this.modal.querySelector('.js-caption')) captions[this.slideIndex - 1].style.display = "block"

        // Update user profile picture asynchronously if propic element exists
        if (this.modal.querySelector('.round-propic-img')) {
            var currentPhotoId = getIdFromString(slideImgs[this.slideIndex - 1].id)
            this.data.photos.forEach(async (photo) => {
                if (parseInt(photo.id) == currentPhotoId) {
                    this.modal.querySelector('.round-propic-container').href = "/rider/" + photo.user_id
                    this.modal.querySelector('.round-propic-img').src = await this.loadPropic(photo.user_id)
                }
            } )
        }

        // Update like button color on every photo change                
        this.colorLike()
    }

}