import BuildRouteMap from "/map/class/route/BuildRouteMap.js"

export default class EditRouteMap extends BuildRouteMap {

    routeId
    routeData

    constructor () {
        super()
        this.routeId = getIdFromString(location.pathname)
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
            modal.classList.add('modal', 'd-flex')
            document.querySelector('body').appendChild(modal)
            var savePopup = document.createElement('div')
            savePopup.classList.add('popup')
            savePopup.innerHTML = `
            <div>
                <label>タイトル :</label>
                <input type="text" class="js-route-name fullwidth" value="` + this.routeData.name + `" />
                <label>詳細 :</label>
                <textarea class="js-route-description fullwidth">` + this.routeData.description + `</textarea>
            </div>
            <div class="d-flex justify-content-between">
                <div id="cancel" class="mp-button bg-darkred text-white">
                    キャンセル
                </div>
                <div id="save" class="mp-button bg-darkgreen text-white">
                    保存
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
                distance: turf.length(routeData),
                elevation: await this.calculateElevation(routeData),
                startplace: await this.getCourseGeolocation(routeData.geometry.coordinates[0]),
                goalplace: await this.getCourseGeolocation(routeData.geometry.coordinates[routeData.geometry.coordinates.length - 1]),
                thumbnail: details.thumbnail
            }
        }
        ajaxJsonPostRequest(this.apiUrl, route, (response) => {
            window.location.replace('/route/' + this.routeData.id)
        } )
    }

}