import BuildRouteMap from "/map/class/BuildRouteMap.js"

export default class EditRouteMap extends BuildRouteMap {

    routeId
    routeData

    constructor () {
        super()
        const queryString = window.location.search
        const urlParams = new URLSearchParams(queryString)
        this.routeId = urlParams.get('id')
    }

    async openSavePopup () {
        return new Promise ((resolve, reject) => {
            // Initialize data
            var data = {
                category: this.routeData.category,
                name: this.routeData.name,
                description: this.routeData.description
            }
            // Build modal
            var modal = document.createElement('div')
            modal.classList.add('modal', 'd-block')
            document.querySelector('body').appendChild(modal)
            var savePopup = document.createElement('div')
            savePopup.classList.add('popup')
            savePopup.innerHTML = `
            <div>
                <label>Name :</label>
                <input type="text" class="js-route-name fullwidth" value="` + this.routeData.name + `" />
                <label>Description :</label>
                <textarea class="js-route-description fullwidth">` + this.routeData.description + `</textarea>
            </div>
            <div class="d-flex justify-content-between">
                <div id="save" class="mp-button bg-darkgreen text-white">
                    Save
                </div>
                <div id="cancel" class="mp-button bg-darkred text-white">
                    Cancel
                </div>
            </div>`

            modal.appendChild(savePopup)
            var inputName        = document.querySelector('.js-route-name')
            var inputDescription = document.querySelector('.js-route-description')
            inputName.addEventListener('change', () => data.name = inputName.value)
            inputDescription.addEventListener('change', () => data.description = inputDescription.value)
            // Close on click outside popup
            modal.addEventListener('click', (e) => {
                if (e.target == modal) {
                    modal.remove()
                    resolve(false)
                }
            })
            // On click on "Yes" button, close the popup and return true
            document.querySelector('#save').addEventListener('click', () => {
                modal.remove()
                resolve(data)
            } )
            // On click on "Cancel" button, close the popup and return false
            document.querySelector('#cancel').addEventListener('click', () => {
                modal.remove()
                resolve(false)
            } )
        } )
    }

    // Save current route
    async saveRoute (details) {
        var routeData = this.map.getSource('route')._data
        if (details.category == 'route') {
            var route = {
                id: parseInt(this.routeId),
                type: this.map.getSource('route')._data.geometry.type,
                coordinates: routeData.geometry.coordinates,
                tunnels: routeData.properties.tunnels,
                category: 'route',
                name: details.name,
                description: details.description,
                distance: turf.length(this.map.getSource('route')._data),
                elevation: await this.calculateElevation(this.map.getSource('route')),
                startplace: await this.getCourseGeolocation(this.map.getSource('route')._data.geometry.coordinates[0]),
                goalplace: await this.getCourseGeolocation(this.map.getSource('route')._data.geometry.coordinates[this.map.getSource('route')._data.geometry.coordinates.length - 1]),
                thumbnail: details.thumbnail
            }
        }/* else if (details.category == 'segment') {
            var route = {
                id: parseInt(this.routeId),
                type: this.map.getSource('route')._data.geometry.type,
                coordinates: routeData.geometry.coordinates,
                tunnels: routeData.properties.tunnels,
                category: 'segment',
                name: details.name,
                description: details.description,
                distance: turf.length(this.map.getSource('route')._data),
                elevation: await this.calculateElevation(this.map.getSource('route')),
                startplace: await this.getCourseGeolocation(this.map.getSource('route')._data.geometry.coordinates[0]),
                goalplace: await this.getCourseGeolocation(this.map.getSource('route')._data.geometry.coordinates[this.map.getSource('route')._data.geometry.coordinates.length - 1]),
                thumbnail: details.thumbnail,
                rank: details.rank,
                seasons: details.seasons,
                advice: details.advice,
                specs: details.specs,
                tags: details.tags
            }
        }*/
        ajaxJsonPostRequest(this.apiUrl, route, (response) => {
            window.location.replace('/routes.php')
        } )
    }

}