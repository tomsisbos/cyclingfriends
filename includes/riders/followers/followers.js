var currentPage = window.location.href.toString()
const followersApiUrl = '/actions/riders/followers/api.php'

/* Follow button */

if (followButtons = document.querySelectorAll('.js-follow')) {
	followButtons.forEach( (followButton) => {
		// Get friend_id thanks to dynamic id
		var followed_id = getIdFromString(followButton.id)
		followButton.addEventListener('click', function () {
			followerAction('follow', followed_id, (response) => {
				console.log(response)
				showResponseMessage(response)
			} )
		} )
	} )
}

/* Unfollow button */

if (unfollowButtons = document.querySelectorAll('.js-unfollow')) {
	unfollowButtons.forEach( (unfollowButton) => {
		// Get friend_id thanks to dynamic id
		var followed_id = getIdFromString(unfollowButton.id)
		unfollowButton.addEventListener('click', function () {
			followerAction('unfollow', followed_id, (response) => {
				console.log(response)
				showResponseMessage(response)
			} )
		} )
	} )
}

// Shows a confirm box on click on button
function followerAction (action, followed_id, callback) {
	ajaxGetRequest(followersApiUrl + "?" + action + "=" + followed_id, callback)
}