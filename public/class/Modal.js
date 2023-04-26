import Model from "/class/Model.js"

export default class Modal extends Model {

    constructor (src) {
        super()

        this.src = src
        var modal = document.createElement('div')
        modal.className = 'modal'

        // Load lightbox script for this popup
        var script = document.createElement('script')
        script.src = '/assets/js/lightbox-script.js'
        modal.appendChild(script)

        // Prepare content
        modal.innerHTML = `
            <span class="close cursor">&times;</span>
            <div class="modal-block">
                <div class="img-slide">
                    <img src="` + this.src + `">
                </div>
            </div>
        `
        // Close functionality
        modal.querySelector('.close.cursor').addEventListener('click', () => this.close())
        modal.addEventListener('click', (e) => {
            var eTarget = e ? e.target : event.srcElement
            if ((eTarget == this.popup) || (eTarget == modal)) modal.style.display = 'none'
        } )

        this.element = modal
        return this
    }

    src
    element

    open = () => this.element.style.display = 'flex'
    close = () => this.element.style.display = 'none'

    /**
     * Display a caption on mouse hover
     * @param {String} caption Caption to display
     */
    setCaption (title, caption) {
        var $caption = document.createElement('div')
        $caption.className = 'lightbox-caption'
        var captionRow = document.createElement('div')
        captionRow.className = 'lightbox-row'
        var captionContent = document.createElement('div')
        captionContent.className = 'caption-content'
        var $title = document.createElement('div')
        $title.innerText = title
        $title.className = 'lightbox-name'
        captionContent.appendChild($title)
        var description = document.createElement('div')
        description.className = 'lightbox-description'
        description.innerText = caption
        captionContent.appendChild(description)
        captionRow.appendChild(captionContent)
        $caption.appendChild(captionRow)

        var container = this.element.querySelector('.img-slide')
        container.appendChild($caption)
        // Display caption on slide box hover
        container.addEventListener('mouseover', () => {
            $caption.style.visibility = 'visible'
            $caption.style.opacity = '1'
        } )
        container.addEventListener('mouseout', () => {
            $caption.style.visibility = 'hidden'
            $caption.style.opacity = '0'
        } )
    }



}