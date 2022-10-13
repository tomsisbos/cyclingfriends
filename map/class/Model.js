export default class Model {

    constructor () {
        ajaxGetRequest (this.apiUrl + "?get-session=true", (session) => {
            this.session = session
        })
    }

    apiKey = 'pk.eyJ1Ijoic2lzYm9zIiwiYSI6ImNsMDdyNGYxbjAxd2MzbG12M3V1bjM1MGIifQ.bFRgCmK9_kkfZSd_skNF1g' // API Key (public mode for the moment)
    apiUrl = '/map/api.php'

    loaderContainer = document.body
    loader = {
        prepare: () => {
            this.loaderElement = document.createElement('div')
            this.loaderElement.className = 'loader-element'
            let loaderIcon = document.createElement('div')
            loaderIcon.innerText = 'Loading...'
            loaderIcon.className = 'loader-icon'
            this.loaderElement.appendChild(loaderIcon)
        },
        start: () => this.loaderContainer.appendChild(this.loaderElement),
        stop: () => this.loaderElement.remove()
    }
}