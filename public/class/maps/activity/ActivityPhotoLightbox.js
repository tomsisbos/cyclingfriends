import Popup from '/class/maps/Popup.js'

export default class ActivityPhotoLightbox extends Popup {

    constructor(data) {
        super()
        this.data = data

        this.build()
    }

    data
    modal
    modalBlock
    slideIndex

    build () {

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
        this.modalBlock.appendChild(closeButton)
        this.modal.appendChild(this.modalBlock)
        this.data.container.appendChild(this.modal)

        // Modal block display
        var slideBox = document.createElement('div')
        slideBox.className = 'slides-box'
        this.modalBlock.appendChild(slideBox)

        // Slide display
        var slide = document.createElement('div')
        slide.className = 'mySlides wider-slide'
        // Create image
        var img = document.createElement('img')
        img.src = this.data.activityPhoto.url
        img.classList.add('fullwidth')
        slide.appendChild(img)
        slideBox.appendChild(slide)

        // Remove on popup closing
        this.data.popup.on('close', () => this.modal.remove())
    }

    open () {
        this.modal.style.display = "block"

        // Close on clicking outside modal-block
        this.modal.addEventListener('click', (e) => {
            if ((e.target == this.modalBlock) || (e.target == this.modal)) this.close()
        } )
    }

    close = () => this.modal.style.display = "none"

}