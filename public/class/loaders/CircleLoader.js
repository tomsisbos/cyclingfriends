import Model from "../Model.js"

export default class CircleLoader extends Model {

    /**
     * A round loader to append to loading elements
     * @param {HTMLElement} container Element to append loader to (default to body)
     * @param {Boolean} options.absolute Either to set style position to absolute or not
     */
    constructor (container = document.body, options = {}) {
        super()
        this.options = options
        this.container = container
        this.build()
    }
    
    options
    container
    element

    build = () => {
        this.element = document.createElement('div')
        this.element.className = 'loader-center'
        if (this.options.absolute) this.element.classList.add('absolute')
        if (this.options.compact) this.element.classList.add('loader-compact')
    }

    start = () => this.container.appendChild(this.element)

    stop = () => this.element.remove()

}