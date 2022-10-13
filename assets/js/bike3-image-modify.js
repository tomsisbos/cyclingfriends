// Bike 3

	// Hides input button
	const bike3imagefile = document.getElementById('bike3imagefile');
	bike3imagefile.style.display = "none";
		
	// Function for submitting the picture (loaded onchange of the input tag)
	bike3imagefile.addEventListener("change", bike3formautosubmit);
	function bike3formautosubmit() {
		document.getElementById('bike3-image-form').submit();
		document.getElementById('addBike3').scrollIntoView()
	}