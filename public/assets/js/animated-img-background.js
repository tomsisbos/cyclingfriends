const imgsNumber = 30
const interval = 10 // seconds
var background = document.querySelector('.with-background-img')
var flash = document.querySelector('.with-background-flash')
var text = document.querySelector('.js-scenery-info')

// Request [imgsNumber] images with the most likes from mkpoint images table
ajaxGetRequest ('/api/background.php' + "?get-background-imgs=" + imgsNumber, (imgs) => {
	console.log(imgs)

	// Preload images
	imgs.forEach(img => new Image().src = img.url)

	// Initialize css properties
	background.style.setProperty('--imgAnimation', 'animatedImage 10s linear infinite alternate')
	flash.style.animation = 'animatedOpacity ' + (interval / 2) + 's cubic-bezier(0,0,0,1) infinite alternate'

	// Function for dynamically change image and meta information
	function changeImg () {
		var number = Math.floor(Math.random() * imgsNumber) // Randomly define an integer among imgsNumber
		background.style.setProperty('--bgImage', 'url(' + imgs[number].url + ')') // Change background image
		text.innerHTML = imgs[number].name + ' (' + imgs[number].month + '月)<br>' + imgs[number].city + ', ' + imgs[number].prefecture // Change meta information
	}

	// Run [changeImg] function once, then every [interval] seconds
	changeImg()
	setInterval(changeImg, interval * 1000)

} )

