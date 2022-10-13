// Hides input button
const propicfile = document.getElementById('propicfile')
propicfile.style.display = "none";
		
// Function for submitting the picture (loaded onchange of the input tag)
function propicformautosubmit() {
	document.getElementById('propic-form').submit()
	document.getElementById('propic-form').scrollIntoView()
}