
import MkpointPopup from '/map/class/MkpointPopup.js'

const mkpoint_id = getIdFromString(location.pathname)
ajaxGetRequest ('/api/map.php' + "?mkpoint=" + mkpoint_id, async (mkpoint) => {
	var mkpointPopup = new MkpointPopup()
	console.log(mkpointPopup)
	mkpointPopup.data = mkpoint.data
	mkpointPopup.photos = mkpoint.photos
	mkpointPopup.prepareModal()
    mkpointPopup.colorLike()
    mkpointPopup.prepareToggleLike()
    mkpointPopup.rating()
    mkpointPopup.reviews()
} )