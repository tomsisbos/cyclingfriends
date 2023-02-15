const followersApiUrl = '/api/riders/followers.php'
const friendsApiUrl   = '/api/riders/friends.php'

/* Follower buttons */

if (followerButtons = document.querySelectorAll('.js-follower')) {
	followerButtons.forEach( (button) => {
		var user_id = getIdFromString(button.dataset.id)
		button.addEventListener('click', function () {
			var action = button.dataset.action
			followerAction(action, user_id, (response) => {
				afterClick(button, user_id, response)
			} )
		} )
	} )
}

/* Friend buttons */

if (friendButtons = document.querySelectorAll('.js-friend')) {
	friendButtons.forEach( (button) => {
		var user_id = getIdFromString(button.dataset.id)
		var user_login = button.dataset.login
		button.addEventListener('click', async function () {
			var action = button.dataset.action
			if (action == 'dismiss') var answer = await openConfirmationPopup(user_login + 'の友達申請を却下します。宜しいですか？')
			else if (action == 'remove') var answer = await openConfirmationPopup(user_login + 'を友達リストから削除します。宜しいですか？')
			else var answer = true
			if (action != 'requested' && answer) {
				friendAction(action, user_id, (response) => {
					afterClick(button, user_id, response)
				} )
			}
		} )
	} )
}

// Shows a confirm box on click on button
function followerAction (action, followed_id, callback) {
	ajaxGetRequest(followersApiUrl + "?" + action + "=" + followed_id, callback)
}

// Shows a confirm box on click on button
function friendAction (action, friend_id, callback) {
	ajaxGetRequest (friendsApiUrl + "?" + action + "=" + friend_id, callback)
}

function afterClick (button, user_id, response) {
	showResponseMessage(response)
	var action = getTwinAction(button.dataset.action)
	button.className = 'btn rdr-button ' + getClass(action)
	button.dataset.id = user_id
	button.dataset.action = action
	button.innerHTML = getIcon(action) + ' ' + getActionName(action)
}

function getClass (action) {
	switch (action) {
		case 'follow': return 'success js-follow'
		case 'unfollow': return 'danger js-unfollow'
		case 'add': return 'success js-add'
		case 'remove': return 'danger js-remove'
		case 'accept': return 'success js-accept'
		case 'dismiss': return 'danger js-dismiss'
		case 'requested': return 'no-click js-requested'
	}
}

function getTwinAction (action) {
	switch (action) {
		case 'follow': return 'unfollow'
		case 'unfollow': return 'follow'
		case 'add': return 'requested'
		case 'remove': return 'add'
		case 'accept': return 'remove'
		case 'dismiss': return 'add'
	}
}

function getIcon (action) {
	switch (action) {
		case 'follow': return '<span class="iconify-inline" data-icon="mdi:eye-arrow-right-outline" style="color: white;" data-width="20" data-height="20"></span>'
		case 'unfollow': return '<span class="iconify-inline" data-icon="mdi:eye-remove-outline" style="color: white;" data-width="20" data-height="20"></span>'
		case 'add': return '<span class="iconify-inline" data-icon="eva:person-add-outline" style="color: white;" data-width="20" data-height="20"></span>'
		case 'remove': return '<span class="iconify-inline" data-icon="eva:person-remove-outline" style="color: white;" data-width="20" data-height="20"></span>'
		case 'accept': return '<span class="iconify-inline" data-icon="eva:person-done-outline" style="color: white;" data-width="20" data-height="20"></span>'
		case 'dismiss': return '<span class="iconify-inline" data-icon="eva:person-remove-outline" style="color: white;" data-width="20" data-height="20"></span>'
		default: return ''
	}
}

function getActionName (action) {
	switch (action) {
		case 'follow': return 'フォローする'
		case 'unfollow': return 'フォローを辞める'
		case 'add': return '友達申請を送る'
		case 'remove': return '友達を辞める'
		case 'accept': return '友達申請を承認する'
		case 'dismiss': return '友達申請を却下する'
		case 'requested': return '送信済み...'
	}
}