apiUrl = '/actions/riders/profile/profileApi.php'

document.querySelector('.js-twitter').addEventListener('change', updateInfo)
document.querySelector('.js-facebook').addEventListener('change', updateInfo)
document.querySelector('.js-instagram').addEventListener('change', updateInfo)
document.querySelector('.js-strava').addEventListener('change', updateInfo)
document.querySelector('.js-last-name').addEventListener('change', updateInfo)
document.querySelector('.js-first-name').addEventListener('change', updateInfo)
document.querySelector('.js-gender').addEventListener('change', updateInfo)
document.querySelector('.js-birthdate').addEventListener('change', updateInfo)
document.querySelector('.js-level').addEventListener('change', updateInfo)
document.querySelector('.js-description').addEventListener('change', updateInfo)

function updateInfo (e) {
	ajaxGetRequest (apiUrl + '?' + e.target.name + '=' + e.target.value, (response) => {
		console.log(response)
	} )
}