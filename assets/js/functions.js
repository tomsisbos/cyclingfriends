/* Global functions */

// XMLHttpRequest Function (function for preventing browser compatibility problems) */
function getHttpRequest () {
	if (window.XMLHttpRequest) { // Mozilla, Safari, Chrome...
		var httpRequest = new XMLHttpRequest()
		if (httpRequest.overrideMimeType) {
			httpRequest.overrideMimeType('text/xml')
		}
	} else if (window.ActiveXObject) { // IE
		try {
			httpRequest = new ActiveXObject("Msxml2.XMLHTTP")
		} catch (e) {
			try {
				httpRequest = new ActiveXObject("Microsoft.XMLHTTP")
			} catch (e) {}
		}
	}
	return httpRequest
}

// Ajax GET request generic function
function ajaxGetRequest (url, callback, loader = false) {
	var xhr = getHttpRequest()
	if (loader) loader.prepare()
	xhr.onreadystatechange = function () {
		// On loading
		if (xhr.readyState > 0) if (loader) loader.start()
		// On success
		if (xhr.readyState === 4) {
			if (loader) loader.stop()
			// If an error has occured during the request
			if (xhr.status != 200) {
				console.log('An error has occured during the request.')
			} else { // If the request have been performed successfully
				var response = JSON.parse(xhr.responseText)
			
				// Treat response
				callback(response)
			}
		}
	}
	// Send request through POST method
	xhr.open('GET', url, true)
	xhr.setRequestHeader('X-Requested-With', 'xmlhttprequest')
	xhr.send()
}

// Ajax POST formData request generic function
function ajaxPostFormDataRequest (url, formData, callback) {
	var xhr = getHttpRequest()
	var loading = document.createElement('div')
	loading.className = 'loading'
	loading.innerHTML = 'loading...'
	document.body.cursor = 'progress'
	xhr.onreadystatechange = function () {
		// On loading
		if (xhr.readyState > 0) {
			document.body.appendChild(loading)
		}
		// On success
		if (xhr.readyState === 4) {
			loading.remove()
			document.body.cursor = 'auto'
			// If an error has occured during the request
			if (xhr.status != 200) {
				console.log('An error has occured during the request.')
			} else { // If the request have been performed successfully
				var response = JSON.parse(xhr.responseText)
				
				// Treat response
				callback(response)
			}
		}
	}
	// Send request through POST method
	xhr.open('POST', url, true)
	xhr.setRequestHeader('X-Requested-With', 'xmlhttprequest')
	xhr.send(formData)
}

// Ajax POST json request generic function
function ajaxJsonPostRequest (url, jsonData, callback) {
	var xhr = getHttpRequest()
	var loading = document.createElement('div')
	loading.className = 'loading'
	loading.innerHTML = 'loading...'
	document.body.cursor = 'progress'
	xhr.onreadystatechange = function () {
		// On loading
		if (xhr.readyState > 0) {
			document.body.appendChild(loading)
		}
		// On success
		if (xhr.readyState === 4) {
			loading.remove()
			document.body.cursor = 'auto'
			// If an error has occured during the request
			if (xhr.status != 200) {
				console.log('An error has occured during the request.')
			} else { // If the request have been performed successfully
				var response = JSON.parse(xhr.responseText)
				
				// Treat response
				callback(response)
			}
		}
	}
	// Send request through POST method
	xhr.open('POST', url, true)
	xhr.setRequestHeader('X-Requested-With', 'xmlhttprequest')
	xhr.setRequestHeader('Content-Type', 'application/json')
	xhr.send(JSON.stringify(jsonData))
}

// Function for truncating strings
function truncateString (string, num) {
	if (string.length > num) {
		let substring = string.substring(0, num);
		return substring + "...";
	} else return string;
}

function capitalizeFirstLetter (string) {
	return string[0].toUpperCase() + string.substring(1)
}

// Get an ID from a string
function getIdFromString (string) {
	var regex  = /\d+/
	var id = string.match(regex) // Define id by extracting it from the string
	return parseInt(id)
}

// Get original URL (before the last GET variable)
function getAbsoluteUrl (str) {
	var regex = /[^?]*/
	var absoluteUrl = str.match(regex) // Define absolute URL by extracting it from the URL
	return absoluteUrl
}

// Set the class of the period tag depending on the month
function setPeriodClass (mkpoint) {
	let month = mkpoint.month
	switch (month) {
		case '1':
			return 'period-1'
			break
		case '2':
			return 'period-2'
			break
		case '3':
			return 'period-3'
			break
		case '4':
			return 'period-4'
			break
		case '5':
			return 'period-5'
			break
		case '6':
			return 'period-6'
			break
		case '7':
			return 'period-7'
			break
		case '8':
			return 'period-8'
			break
		case '9':
			return 'period-9'
			break
		case '10':
			return 'period-10'
			break
		case '11':
			return 'period-11'
			break
		case '12':
			return 'period-12'
			break
		default:
			return 'period-default'
	}	  
}

// Get a parameter value from the URL query string
function getParam (index) {
	const queryString = window.location.search
	const urlParams = new URLSearchParams(queryString)
	const value = urlParams.get(index)
	return value
}

// Round value to precision decimals
function round(value, precision) {
    var multiplier = Math.pow(10, precision || 0);
    return Math.round(value * multiplier) / multiplier;
}

// Check if a number is pair or not
function numIsPair(n) {
	return (n & 1) ? false : true;
}

// Return logarithm of x in base y
function getBaseLog(x, y) {
    return Math.log(y) / Math.log(x);
}

async function openConfirmationPopup (question) {
	return new Promise ((resolve, reject) => {
		var modal = document.createElement('div')
		modal.classList.add('modal', 'd-block')
		document.querySelector('body').appendChild(modal)
		modal.addEventListener('click', (e) => {
			var eTarget = e ? e.target : event.srcElement
			if ((eTarget == confirmationPopup || eTarget == confirmationPopup.firstElementChild) && (eTarget !== modal)){
				// Nothing
			}else{
				modal.remove()
			}
		})
		var confirmationPopup = document.createElement('div')
		confirmationPopup.classList.add('popup')
		confirmationPopup.innerHTML = question + '<div class="d-flex justify-content-between"><div id="yes" class="mp-button bg-darkred text-white">Yes</div><div id="no" class="mp-button bg-darkgreen text-white">No</div></div>'
		modal.appendChild(confirmationPopup)
		// On click on "Yes" button, close the popup and return true
		document.querySelector('#yes').addEventListener('click', () => {
			modal.remove()
			resolve (true)
		} )
		// On click on "No" button, return false and close the popup
		document.querySelector('#no').addEventListener('click', () => {
			modal.remove()
			resolve (false)
		} )
	} )
}

// Show corresponding message after request
function showResponseMessage (response) {
	console.log(response)
	hideResponseMessage()
	// If success, show a success message
	if (response.success) {
		var successBlock = document.createElement('div')
		successBlock.className = 'success-block m-0'
		var successMessage = document.createElement('p')
		successMessage.className = 'success-message'
		successMessage.innerHTML = response.success
		document.querySelector('nav').after(successBlock)
		successBlock.appendChild(successMessage)
	// Else, show an error message
	} else if (response.error) {
		var errorBlock = document.createElement('div')
		errorBlock.className = 'error-block m-0'
		var errorMessage = document.createElement('p')
		errorMessage.className = 'error-message'
		errorMessage.innerHTML = response.error
		document.querySelector('nav').after(errorBlock)
		errorBlock.appendChild(errorMessage)
	}
	window.scroll( {top: 80, behavior: 'smooth'} )
}

function hideResponseMessage () {
	if (document.querySelector('.success-block')) document.querySelector('.success-block').remove()
	if (document.querySelector('.error-block')) document.querySelector('.error-block').remove()
}

function openSingleModal (src) {
	var modal = document.createElement('div')
	modal.className = 'modal modal-single'
	modal.style.display = 'block'
	modal.style.textAlign = 'center'
	document.querySelector('body').appendChild(modal)
	var modalImage = document.createElement('img')
	modalImage.src = src
	modalImage.className = 'modal-single-img'
	modal.appendChild(modalImage)
	modal.onclick = function (e) {
		var eTarget = e ? e.target : event.srcElement
		if ((eTarget == modalImage) && (eTarget !== modal)) {
			// Nothing
		} else {
			modal.remove()
		}
	}
}

function blobToBase64 (blob) {
	return new Promise((resolve, _) => {
	  const reader = new FileReader()
	  reader.onloadend = () => resolve(reader.result)
	  reader.readAsDataURL(blob)
	} )
}

function getKeyByValue (object, value) {
	return Object.keys(object).find(key => object[key] === value)
}

function calculateAverage (array) {
    var total = 0
    var count = 0

    array.forEach(function(item, index) {
        total += item
        count++
    } )

    return total / count
}

function getFormattedDurationFromTimestamp (timestamp) {
	var hours = Math.floor(timestamp / 1000 / 60 / 60)
	var minutes = Math.floor(((timestamp / 1000 / 60 / 60) - hours) * 60)
	return hours + 'h' + ('0' + minutes).slice(-2)
}

function getDurationFromTimestamp (timestamp) {
	var hours = Math.floor(timestamp / 1000 / 60 / 60)
	var minutes = Math.floor(((timestamp / 1000 / 60 / 60) - hours) * 60)
	return hours + ':' + minutes + ':' + '00'
}

async function resizeAndCompress (img, max_width, max_height, compressionIndicator) {
	return new Promise((resolve, reject) => {
		var canvas = document.createElement('canvas')
		var width = img.width
		var height = img.height

		// Calculate the width and height, constraining the proportions
		if (width > height) {
			if (width > max_width) {
				height = Math.round(height *= max_width / width)
				width = max_width
			}
		} else {
			if (height > max_height) {
				width = Math.round(width *= max_height / height)
				height = max_height
			}
		}
		
		// Resize the canvas and draw the image data into it
		canvas.width = width
		canvas.height = height
		var ctx = canvas.getContext("2d")
		ctx.drawImage(img, 0, 0, width, height)
		
		// Resolve promise with blob after modification
		canvas.toBlob(blob => {
			resolve(blob)
		}, "image/jpeg", compressionIndicator) // Get the data from canvas as JPG
	} )

}

async function getDataURLFromBlob (blob) {
	return new Promise( (resolve, reject) => {
		const reader = new FileReader()
		reader.readAsDataURL(blob)
		reader.addEventListener("load", () => {
			resolve(reader.result)
		} )
	} )
}

function getExtension(filename) {
	var parts = filename.split('.')
	return parts[parts.length - 1]
}