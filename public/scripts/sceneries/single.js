import SceneryPopup from '/class/maps/scenery/SceneryPopup.js'

var single = new SceneryPopup({}, {}, {noPopup: true})

// Load rating
var scenery_id = window.location.href.substring(window.location.href.lastIndexOf('/') + 1)
single.loadRating({id: scenery_id})