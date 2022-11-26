const imgsNumber = 30
const interval = 10 // seconds
var background = document.querySelector('.with-background-img')
var flash = document.querySelector('.with-background-flash')
var text = document.querySelector('.classy-title')

// Request [imgsNumber] images with the most likes from mkpoint images table
ajaxGetRequest ('/api/background.php' + "?get-background-imgs=" + imgsNumber, (imgs) => {
	console.log(imgs)

	// Initialize css properties
	background.style.setProperty('--imgAnimation', 'animatedImage 10s linear infinite alternate')
	flash.style.animation = 'animatedOpacity ' + (interval / 2) + 's cubic-bezier(0,0,0,1) infinite alternate'

	// Function for dynamically change image and meta information
	function changeImg () {
		var number = Math.floor(Math.random() * imgsNumber) // Randomly define an integer among imgsNumber
		background.style.setProperty('--bgImage', `url('data:` + imgs[number].type + `;base64,` + imgs[number].blob) // Change background image
		text.innerHTML = imgs[number].mkpoint.name + ' (' + imgs[number].month + '月)<br>' + imgs[number].mkpoint.city + ', ' + imgs[number].mkpoint.prefecture // Change meta information
	}

	// Run [changeImg] function once, then every [interval] seconds
	changeImg()
	setInterval(changeImg, interval * 1000)

} )

