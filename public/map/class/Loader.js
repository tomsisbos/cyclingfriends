import Model from "/map/class/Model.js"

export default class Loader extends Model {

    constructor (text = 'Loading...', container = document.body) {
        super({noSession: true})
        this.container = container
        this.text = text
    }
    
    container
    modal
    element
    text
    appendice

    prepare = (text = this.text) => {
        this.modal = document.createElement('div')
        this.modal.className = 'loading-modal'
        this.modal.style.cursor = 'loading'
        this.element = document.createElement('div')
        this.element.innerText = text
        this.element.className = 'loading-text'
        this.modal.appendChild(this.element)
    }

    start = () => this.container.appendChild(this.modal)

    setText (text) {
        this.text = text
        this.element.innerText = text
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