import Model from "/map/class/Model.js"

export default class Loader extends Model {

    constructor (container = document.body) {
        super()
        this.container = container
    }
    
    container
    element

    prepare = (text) => {
        this.element = document.createElement('div')
        this.element.className = 'loading-modal'
        let loaderIcon = document.createElement('div')
        loaderIcon.innerText = text
        this.element.style.cursor = 'loading'
        loaderIcon.className = 'loading-text'
        this.element.appendChild(loaderIcon)
    }

    start = () => this.container.appendChild(this.element)

    setText = (text) => this.element.innerText = text

    stop = () => this.element.remove()

}