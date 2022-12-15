import Popup from "/map/class/Popup.js"
import CFUtils from "/map/class/CFUtils.js"

export default class TempPopup extends Popup {

    constructor () {
        super()
    }

    load () {

        // Build tag checkboxes
        var $tags = '<div class="js-tags">'
        this.tags.forEach(tag => {
            $tags += `
                <div class="mp-checkbox">
                    <input type="checkbox" data-name="` + tag + `" id="tag` + tag + `" class="js-segment-tag" />
                    <label for="tag` + tag + `">` + CFUtils.getTagString(tag) + `</label>
                </div>
            `
        } )
        $tags += '</div>'

        this.popup.setHTML(`
            <div class="popup-head popup-content container-admin">Share a new scenery spot</div>
            <form class="popup-content" name="mkpointForm" id="mkpointForm">
                <strong>Name :</strong>
                <input type="text" name="name" class="admin-field"/>
                <strong>Description :</strong>
                <textarea name="description" class="admin-field"></textarea>
                <input type="hidden" name="MAX_FILE_SIZE" value="10000000" />
                <label for="file" class="mp-button"><input enctype="multipart/form-data" type="file" name="file" id="file" style="display: none" />Upload a photo</label>
                <img class="mp-image-preview" />
                <input type="submit" name="mbkPointForm" value="Submit" class="mp-button bg-button text-white fullwidth" />
            </form>`
            + $tags + `
        ` )
    }

    // Treat Mkpoint input data through API requests
    async save () {
        return new Promise( async (resolve, reject) => {
            var popup = this.popup
            var form = popup._content.querySelector('form')
            var id = popup.id
            let lng = popup._lngLat.lng
            let lat = popup._lngLat.lat
            var location = await this.getLocation()
            var city = location.city
            var prefecture = location.prefecture
            var elevation = popup.elevation

            // Get form data into queryData on submission of the form
            form.addEventListener('submit', (e) => {
                
                // Prevents default behavior of the submit button
                e.preventDefault()

                // Get tags data
                var tags = []
                document.querySelectorAll('.js-tags input').forEach(checkbox => {
                    if (checkbox.checked) tags.push(checkbox.dataset.name)
                } )

                // Get form data into queryData and adds tab id
                var mkpointData = new FormData(form)
                mkpointData.append('saveMkpoint', true)
                mkpointData.append('tags', tags)
                mkpointData.append('city', city)
                mkpointData.append('prefecture', prefecture)
                mkpointData.append('lng', lng)
                mkpointData.append('lat', lat)
                mkpointData.append('elevation', elevation)
                
                // Proceed AJAX request and treat data in the callback function
                ajaxPostFormDataRequest(this.apiUrl, mkpointData, (response) => {
                    if (response.error) {
                        if (document.querySelector('.error-block')) document.querySelector('.error-block').remove()
                        var errorDiv = document.createElement('div')
                        errorDiv.classList.add('error-block', 'fullwidth', 'mt-0', 'mb-1', 'p-2')
                        var errorMessage = document.createElement('p')
                        errorMessage.innerHTML = response.error
                        errorMessage.classList.add('error-message')
                        errorDiv.appendChild(errorMessage)
                        document.querySelector('.mapboxgl-popup-content').insertBefore(errorDiv, document.querySelector('#form'))
                    } else {
                        // Remove temp marker after posting data
                        document.querySelector('.mapboxgl-popup').remove()
                        document.getElementById(id).remove()
                        resolve()
                    }
                } )
            } )
        } )
    }
    
}