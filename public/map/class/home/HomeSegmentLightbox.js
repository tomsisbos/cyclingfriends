import SegmentLightbox from "/map/class/segment/SegmentLightbox.js"

export default class HomeSegmentLightbox extends SegmentLightbox {

    constructor(container, popup, data, instanceOptions = {noSession: true}) {
        super(container, popup, data, instanceOptions)
    }

    apiUrl = '/api/home.php'

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
        this.modal.id = 'myModal'
        this.modal.className = 'modal'
        var closeButton = document.createElement('span')
        closeButton.className = "close cursor"
        closeButton.addEventListener('click', () => this.close())
        closeButton.innerHTML = '&times;'
        this.modalBlock = document.createElement('div')
        this.modalBlock.className = "modal-block"
        this.modal.appendChild(closeButton)
        this.modal.appendChild(this.modalBlock)
        this.container.appendChild(this.modal)
        // If more than one photo, display arrows
        if (this.data.photos.length > 1) {
            this.modalBlock.appendChild(prevArrow)
            this.modalBlock.appendChild(nextArrow)
        }

        // Slides display
        var slides = []
        var imgs = []
        var slidesBox = document.createElement('div')
        slidesBox.className = 'slides-box'
        this.modalBlock.appendChild(slidesBox)
        var cursor = 0
        // Build slides
        this.data.sceneries.forEach( (scenery) => {
            scenery.photos.forEach( (photo) => {
                slides[cursor] = document.createElement('div')
                slides[cursor].className = 'mySlides wider-slide'
                // Create number
                let numberText = document.createElement('div')
                numberText.className = 'numbertext'
                numberText.innerHTML = (cursor + 1) + ' / ' + this.data.photos.length
                slides[cursor].appendChild(numberText)
                // Create image
                imgs[cursor] = document.createElement('img')
                imgs[cursor].src = photo.url
                imgs[cursor].id = 'scenery-img-' + photo.id
                imgs[cursor].classList.add('fullwidth')
                slides[cursor].appendChild(imgs[cursor])
                // Create image meta
                var imgMeta = document.createElement('div')
                imgMeta.className = 'scenery-img-meta'
                slides[cursor].appendChild(imgMeta)
                var period = document.createElement('div')
                period.className = 'scenery-period lightbox-period'
                period.classList.add('period-' + photo.month)
                period.innerText = photo.period
                imgMeta.appendChild(period)
                slidesBox.appendChild(slides[cursor])
                // Caption display
                var caption = document.createElement('div')
                caption.className = 'lightbox-caption'
                var name = document.createElement('div')
                name.innerText = 'km ' + (Math.ceil(scenery.distanceFromStart * 10) / 10) + ' - ' + scenery.name
                name.className = 'lightbox-name'
                caption.appendChild(name)
                var location = document.createElement('div')
                location.innerText = scenery.city + ' (' + scenery.prefecture + ') - ' + scenery.elevation + 'm'
                location.className = 'lightbox-location'
                caption.appendChild(location)
                var description = document.createElement('div')
                description.className = 'lightbox-description'
                description.innerText = scenery.description
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
        var demos = []
        var demosBox = document.createElement('div')
        demosBox.className = 'thumbnails-box'
        this.modalBlock.appendChild(demosBox)
        const photos = this.data.photos
        for (let i = 0; i < photos.length; i++) {
            let column = document.createElement('div')
            column.className = 'column'
            demos[i] = document.createElement('img')
            demos[i].className = 'demo cursor fullwidth'
            demos[i].dataset.number = i + 1
            demos[i].src = photos[i].url
            column.appendChild(demos[i])
            demosBox.appendChild(column)
        }
    }
}