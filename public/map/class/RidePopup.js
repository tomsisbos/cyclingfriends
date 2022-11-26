import Popup from "/map/class/Popup.js"

export default class RidePopup extends Popup {

    constructor (options, ride) {
        super(options)
        this.data = ride
        this.load()
    }
    
    type = 'ride'
    data
    rideId

    load () {
        // Define tag color according to ride status
        if (this.data.status == 'Open') var tagColor = 'tag-green'
        else if (this.data.status == 'Full') var tagColor = 'tag-blue'
        else if (this.data.status == 'Closed') var tagColor = 'tag-red'

        // Build checkpoints table
        var trs = ''
        this.data.checkpoints.forEach( (checkpoint) => {
            if (checkpoint.checkpoint_id == 0) var number = 'Start'
            else if (checkpoint.checkpoint_id == this.data.checkpoints.length - 1) {
                var number = 'Goal'
                checkpoint.distance = this.data.distance
            } else var number = 'n°' + checkpoint.checkpoint_id
            trs += `
                <tr>
                    <td>` + number + `<td>
                    <td>` + checkpoint.name + `<td>
                    <td class="popup-checkpoints-table-distance">km ` + (Math.round(checkpoint.distance * 10) / 10) + `<td>
                </tr>`
        } )
        var checkpointsTable = '<table class="popup-checkpoints-table"><tbody>' + trs + '</tbody></table>'

        // Set content
        this.popup.setHTML(`
        <div class="popup-img-container">
            <a target="_blank" href="/ride/` + this.data.id + `">
                <div class="popup-img-background">
                    Check details
                    <img id="rideFeaturedImage` + this.data.id + `" class="popup-img popup-img-with-background" />
                </div>
            </a>
        </div>
        <div class="popup-content">
            <div class="popup-properties">
                <div class="popup-properties-name">` + this.data.name + `
                    <div class="popup-tag ` + tagColor + `" >`+ this.data.participants_number + `/` + this.data.nb_riders_max + `</div>
                </div>
                <div class="">`+ this.data.date + ` - by 
                    <a target="_blank" href="/rider/` + this.data.author_id + `">` + this.data.author_login + `</a>
                </div>
            </div>
            <div class="popup-description">`
                + this.data.description + `
            </div>
            <div class="popup-checkpoints">Checkpoints list</div>`
            + checkpointsTable +
        `</div>
        <div class="d-flex">
            <button class="mp-button bg-button m-auto">
                <a target="_blank" class="text-white" style="text-decoration: none;" href="/ride/` + this.data.id + `">Check details</a>
            </button>
        </div>`)
    }
}