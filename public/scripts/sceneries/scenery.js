import SceneryPopup from '/map/class/scenery/SceneryPopup.js'

const mkpoint_id = getIdFromString(location.pathname)
ajaxGetRequest ('/api/map.php' + "?mkpoint=" + mkpoint_id, async (response) => {

	// Prepare scenery instance
	var instanceOptions = {
		noPopup: true
	}
	var instanceData = {
		mapInstance: null,
		mkpoint: response.data
	}
	let sceneryPopup = new SceneryPopup({}, instanceData, instanceOptions)
	
	// Initiate relevant functions
    sceneryPopup.loadRating(sceneryPopup.data.mkpoint)
    sceneryPopup.loadReviews()

	// Load photos
	ajaxGetRequest (sceneryPopup.apiUrl + "?mkpoint-photos=" + sceneryPopup.data.mkpoint.id, (photos) => {
		sceneryPopup.data.mkpoint.photos = photos
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