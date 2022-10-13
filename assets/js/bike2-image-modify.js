// Bike 2

	// Hides input button
	const bike2imagefile = document.getElementById('bike2imagefile');
	bike2imagefile.style.display = "none";
		
	// Function for submitting the picture (loaded onchange of the input tag)
	bike2imagefile.addEventListener("change", bike2formautosubmit);
	function bike2formautosubmit() {
		document.getElementById('bike2-image-form').submit();
		document.getElementById('addBike2').scrollIntoView()
	}