import Popup from "/map/class/Popup.js"

export default class SegmentLightbox extends Popup {

    constructor(container, popup, data, instanceOptions) {
        super({}, {}, instanceOptions)
        this.container = container
        this.popup = popup
        this.data = data

        this.build()
        this.prepare()
    }

    container
    modal
    modalBlock
    slideIndex

    load () {
        return true
    }

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

        // If first opening, prepare modal window structure
        if (!this.container.querySelector('#myModal')) {
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
            console.log(this.container)
            // If more than one photo, display arrows
            if (this.data.photos.length > 1) {
                this.modalBlock.appendChild(prevArrow)
                this.modalBlock.appendChild(nextArrow)
            }
        // Else, clear modal window content
        } else {
            this.container.querySelector('.modal-block').innerHTML = ''
            if (this.data.photos.length > 1) {
                this.container.querySelector('.modal-block').appendChild(prevArrow)
                this.container.querySelector('.modal-block').appendChild(nextArrow)
            }
        }

        // Slides display
        var slides = []
        var imgs = []
        var slidesBox = document.createElement('div')
        slidesBox.className = 'slides-box'
        this.container.querySelector('.modal-block').appendChild(slidesBox)
        var cursor = 0
        this.data.mkpoints.forEach( (mkpoint) => {
            mkpoint.photos.forEach( (photo) => {
                const distanceFromStart = this.getDistanceFromStart(mkpoint)
                slides[cursor] = document.createElement('div')
                slides[cursor].className = 'mySlides wider-slide'
                // Create number
                let numberText = document.createElement('div')
                numberText.className = 'numbertext'
                numberText.innerHTML = (cursor + 1) + ' / ' + this.data.photos.length
                slides[cursor].appendChild(numberText)
                // Create image
                imgs[cursor] = document.createElement('img')
                imgs[cursor].src = 'data:image/jpeg;base64,' + photo.blob
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
        var demos = []
        var demosBox = document.createElement('div')
        demosBox.className = 'thumbnails-box'
        this.modalBlock.appendChild(demosBox)
        var demoCursor = 0
        this.data.photos.forEach( (photo) => {
            let column = document.createElement('div')
            column.className = 'column'
            demos[demoCursor] = document.createElement('img')
            demos[demoCursor].className = 'demo cursor fullwidth'
            demos[demoCursor].setAttribute('demoId', demoCursor + 1)
            demos[demoCursor].src = 'data:' + photo.type + ';base64,' + photo.blob
            column.appendChild(demos[demoCursor])
            demosBox.appendChild(column)
            demoCursor++
        } )

        // Prepare toggle like function
        if (this.popup.getElement().querySelector('#like-button')) this.prepareToggleLike()

        // Remove on popup closing
        this.popup.on('close', () => this.modal.remove())
    }

    prepare () {

        // Display slide on click on a demo
        var demos = this.modal.querySelectorAll('.demo')
        demos.forEach(demo => {
            demo.addEventListener('click', (e) => {
                let id = parseInt(e.target.getAttribute('demoId'))
                console.log(id)
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