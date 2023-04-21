import Model from "/map/class/Model.js"

export default class Loader extends Model {

    constructor (text = 'Loading...', container = document.body) {
        super()
        // Set instance properties
        this.container = container
        this.text = text
        // Prepare
        this.modal = document.createElement('div')
        if (this.container == document.body) this.modal.className = 'loading-modal'
        else this.modal.className = 'loading-modal-absolute'
        this.modal.style.cursor = 'loading'
        this.element = document.createElement('div')
        this.element.innerText = text
        this.element.className = 'loading-text'
        this.modal.appendChild(this.element)
    }
    
    container
    modal
    element
    text
    appendice

    start = () => this.container.appendChild(this.modal)

    setText (text) {
        this.text = text
        this.element.innerText = text
    }

    setHTML (html) {
        this.element.innerHTML = html
    }

    appendText (text) {
        this.appendice = document.createElement('div')
        this.appendice.innerText = text
        this.appendice.className = 'loading-text loading-appendice'
        this.modal.appendChild(this.appendice)
    }

    isSet () {
        return this.modal.isConnected
    }

    stop = () => this.modal.remove()

}