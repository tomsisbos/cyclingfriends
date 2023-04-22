import SceneryPopup from '/class/scenery/SceneryPopup.js'

const scenery_id = getIdFromString(location.pathname)
ajaxGetRequest ('/api/map.php' + "?scenery=" + scenery_id, async (response) => {

	// Prepare scenery instance
	var instanceOptions = {
		noPopup: true
	}
	var instanceData = {
		mapInstance: null,
		scenery: response.data
	}
	let sceneryPopup = new SceneryPopup({}, instanceData, instanceOptions)
	
	// Initiate relevant functions
    sceneryPopup.loadRating(sceneryPopup.data.scenery)
    sceneryPopup.loadReviews()

	// Load photos
	ajaxGetRequest (sceneryPopup.apiUrl + "?scenery-photos=" + sceneryPopup.data.scenery.id, (photos) => {
		sceneryPopup.data.scenery.photos = photos
		sceneryPopup.loadLightbox(document.body)
		sceneryPopup.colorLike()
		sceneryPopup.prepareToggleLike()
		
		// Set lightbox listener
		document.querySelectorAll('.pg-sg-photo').forEach(thumbnail => {
			thumbnail.addEventListener('click', () => {
				let id = parseInt(thumbnail.dataset.number)
				sceneryPopup.lightbox.open(id)
			} )
		} )
	} )
} )