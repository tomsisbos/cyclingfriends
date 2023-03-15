export default class TimerPopup {

    /**
     * Shows a small popup displaying a success or error message
     * @param {{type: string, text: string}} message 
     * @param {int} seconds
     */
    constructor (message, seconds) {
        this.time = seconds * 1000
        this.element = document.createElement('div')
        this.element.className = 'temp-popup ' + message.type
        this.element.innerText = message.text
        document.querySelector('body').appendChild(this.element)
    }

    show () {
        this.element.classList.add('appear')
        window.setTimeout(() => {
            this.element.classList.remove('appear')
        }, this.time)
    }

} 