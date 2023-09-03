import Popup from "/class/maps/Popup.js"

export default class NeighbourPopup extends Popup {

    constructor (properties) {
        super( {
            className: 'marker-popup',
            closeOnClick: true
        } )
        this.data = properties
        this.load()
    }
    apiUrl = '/api/riders/neighbours.php'
    type = 'neighbour'
    data

    load () {

        this.popup.setLngLat([this.data.lng, this.data.lat])

        ajaxGetRequest (this.apiUrl + "?get-rider-data=" + this.data.id, (data) => {

            // Prepare elements
            if (data.level == 'Beginner') var tag = `<span class="tag-green">初心者</span>`
            else if (data.level == 'Intermediate') var tag = `<span class="tag-blue">中級者</span>`
            else if (data.level == 'Athlete') var tag = `<span class="tag-red">上級者</span>`
            else var tag = ''
            
            this.data = data

            this.popup.setHTML(`
            <div class="popup-content nbr">
                <div class="d-flex gap">
                    <div class="round-propic-container">
                        <a href="/rider/` + data.id + `" target="_blank">
                            <img class="round-propic-img" src="` + data.propic + `" />
                        </a>
                    </div>
                    <div class="popup-properties">
                        <div class="popup-properties-reference">
                            <a href="/rider/` + data.id + `" target="_blank"><div class="popup-properties-name">` + data.login + `</div></a>
                            <div class="popup-properties-location">` + data.distance + `km - ` + data.location.city + `（` + data.location.prefecture + `）</div>
                            ` + tag + `
                        </div>
                    </div>
                </div>
                <div class="popup-description">
                    <div class="nbr-popup-line">アクティビティ数 : ` + data.activitiesNumber + `</div>
                </div>
            </div>`)

        } )

    }
}