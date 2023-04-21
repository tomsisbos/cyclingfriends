import TimerPopup from '/map/class/TimerPopup.js'

apiUrl = '/api/riders/profile.php'

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
	var label = e.target.parentElement.querySelector('label')
	var editedPropertyString = label.innerText.substring(0, label.innerText.indexOf(" :"))
    var timerPopup = new TimerPopup({type: 'success', text: editedPropertyString + 'の変更を保存しました！'}, 2)
	ajaxGetRequest (apiUrl + '?' + e.target.name + '=' + e.target.value, (response) => {
        timerPopup.show()
	})
}