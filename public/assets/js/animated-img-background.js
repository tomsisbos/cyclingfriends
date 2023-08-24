const defaultImgsNumber = 30
var interval = 10 // seconds
var background = document.querySelector('.with-background-img')
var flash = document.querySelector('.with-background-flash')
var text = document.querySelector('.js-scenery-info')
const parameter = background.dataset.parameter
var value = background.dataset.value
var imgsNumber = background.dataset.number
if (background.dataset.interval) interval = parseInt(background.dataset.interval)

// Request [imgsNumber] images with the most likes from scenery images table

if (!value) value = true
if (!imgsNumber) var imgsNumber = defaultImgsNumber
var request = '/api/background.php' + "?" + parameter + "=" + value + "&number=" + imgsNumber

ajaxGetRequest(request, (imgs) => {

	// Preload images
	imgs.forEach(img => new Image().src = img.url)

	// Initialize css properties
	background.style.setProperty('--imgAnimation', 'animatedImage ' + interval + 's linear infinite alternate')
	flash.style.animation = 'animatedOpacity ' + (interval / 2) + 's cubic-bezier(0,0,0,1) infinite alternate'

	// Function for dynamically change image and meta information
	function changeImg () {
		var number = Math.floor(Math.random() * imgs.length) // Randomly define an integer among imgsNumber
		background.style.setProperty('--bgImage', 'url(' + imgs[number].url + ')') // Change background image
		if (text) text.innerHTML = '<a href="/scenery/' + imgs[number].scenery_id + '">' + imgs[number].name + '</a>' + ' (' + imgs[number].month + '月)<br>' + imgs[number].city + ', ' + imgs[number].prefecture // Change meta information
	}

	// Run [changeImg] function once, then every [interval] seconds
	changeImg()
	setInterval(changeImg, interval * 1000)

} )

