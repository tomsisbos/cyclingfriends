import Model from "/map/class/Model.js"

export default class LoaderCircle extends Model {

    constructor (container = document.body) {
        super()
        this.container = container
        this.build()
    }
    
    container
    element

    build = () => {
        this.element = document.createElement('div')
        this.element.className = 'loader-center'
    }

    start = () => this.container.appendChild(this.element)

    stop = () => this.element.remove()

}