import Popup from "/map/class/Popup.js"

export default class AmenityPopup extends Popup {

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

        this.popup.setLngLat(this.data.lngLat)

        ajaxGetRequest (this.apiUrl + "?get-rider-data=" + this.data.id, (data) => {

            console.log(data)

            // Prepare variables
            data.distance = parseFloat(document.querySelector('#card' + data.id).querySelector('.nbr-distance').innerText)

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
                    <div class="nbr-popup-line">訪問済み絶景スポット : ` + data.clearedMkpointsNumber + `</div>
                    <div class="nbr-popup-line">走行済みセグメント : ` + data.clearedSegmentsNumber + `</div>
                </div>
            </div>`)

        } )

    }
}