import SceneryLightbox from '/map/class/scenery/SceneryLightbox.js'

export default class HomeSceneryLightbox extends SceneryLightbox {

    constructor(data, instanceOptions) {
        super(data, instanceOptions)
        this.data = data

        this.build()
        this.prepare()
    }

    data
    modal
    modalBlock
    slideIndex

    build () {
        // Prepare arrows
        if (this.data.photos.length > 1) {
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
        if (this.data.photos.length > 1) {
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

        // Slides display
        var slides = []
        var imgs = []
        for (let i = 0; i < this.data.photos.length; i++) {
            slides[i] = document.createElement('div')
            slides[i].className = 'mySlides wider-slide'
            // Create number
            let numberText = document.createElement('div')
            numberText.className = 'numbertext'
            numberText.innerHTML = (i + 1) + ' / ' + this.data.photos.length
            slides[i].appendChild(numberText)
            // Create image
            imgs[i] = document.createElement('img')
            imgs[i].src = this.data.photos[i].url
            imgs[i].id = 'mkpoint-img-' + this.data.photos[i].id
            imgs[i].classList.add('fullwidth')
            slides[i].appendChild(imgs[i])
            // Create image meta
            var imgMeta = document.createElement('div')
            imgMeta.className = 'mkpoint-img-meta'
            slides[i].appendChild(imgMeta)
            var period = document.createElement('div')
            period.className = 'mkpoint-period lightbox-period'
            period.classList.add('period-' + this.data.photos[i].month)
            period.innerText = this.data.photos[i].period
            imgMeta.appendChild(period)
            slidesBox.appendChild(slides[i])
        }
        // Demos display
        var demos = []
        var demosBox = document.createElement('div')
        demosBox.className = 'thumbnails-box'
        this.modalBlock.appendChild(demosBox)
        for (let i = 0; i < this.data.photos.length; i++) {
            let column = document.createElement('div')
            column.className = 'column'
            demos[i] = document.createElement('img')
            demos[i].className = 'demo cursor fullwidth'
            demos[i].dataset.id = i + 1
            demos[i].src = this.data.photos[i].url
            column.appendChild(demos[i])
            demosBox.appendChild(column)
        }

        // Remove on popup closing
        this.data.popup.on('close', () => this.modal.remove())
    }

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
    }

}