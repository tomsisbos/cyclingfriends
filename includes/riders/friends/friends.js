var currentPage = window.location.href.toString()
var apiUrl = '/actions/riders/friends/friendsApi.php'

/* Add button */

if (addButtons = document.querySelectorAll('.js-add')) {
	addButtons.forEach( (addButton) => {
		// Get friend_id thanks to dynamic id
		var friend_id = getIdFromString(addButton.id)
		addButton.addEventListener('click', function () {
			friendAction('add', friend_id, (response) => {
				console.log(response)
				showResponseMessage(response)
			} )
		} )
	} )
}

/* Accept button */

if (acceptButtons = document.querySelectorAll('.js-accept')) {
	acceptButtons.forEach( (acceptButton) => {
		// Get friend_id thanks to dynamic id
		var friend_id = getIdFromString(acceptButton.id)
		acceptButton.addEventListener('click', function () {
			friendAction('accept', friend_id, (response) => {
				console.log(response)
				showResponseMessage(response)
			} )
		} )
	} )
}

/* Dismiss button */

if (dismissButtons = document.querySelectorAll('.js-dismiss')) {
	dismissButtons.forEach( (dismissButton) => {
		// Get friend_id thanks to dynamic id
		var friend_id    = getIdFromString(dismissButton.id)
		var friend_login = dismissButton.dataset.login
		dismissButton.addEventListener('click', async function () {
			answer = await openConfirmationPopup('Do you really want to dismiss ' + friend_login + '\'s request ?')
			if (answer) {
				friendAction('dismiss', friend_id, (response) => {
					console.log(response)
					showResponseMessage(response)
				} )
			}
		} )
	} )
}

/* Remove button */

if (removeButtons = document.querySelectorAll('.js-remove')) {
	removeButtons.forEach( (removeButton) => {
		// Get friend_id thanks to dynamic id
		var friend_id    = getIdFromString(removeButton.id)
		var friend_login = removeButton.dataset.login
		removeButton.addEventListener('click', async function () {
			answer = await openConfirmationPopup('Do you really want to remove ' + friend_login + ' from your friends list ?')
			if (answer) {
				friendAction('remove', friend_id, (response) => {
					console.log(response)
					showResponseMessage(response)
				} )
			}
		} )
	} )
}

// Shows a confirm box on click on button
function friendAction (action, friend_id, callback) {
	ajaxGetRequest (apiUrl + "?" + action + "=" + friend_id, callback)
}