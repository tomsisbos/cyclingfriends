// Bike 1

	// Hides input button
	const bike1imagefile = document.getElementById('bike1imagefile');
	bike1imagefile.style.display = "none";
		
	// Function for submitting the picture (loaded onchange of the input tag)
	bike1imagefile.addEventListener("change", bike1formautosubmit);
	function bike1formautosubmit() {
		document.getElementById('bike1-image-form').submit();
		document.getElementById('addBike1').scrollIntoView()
	}