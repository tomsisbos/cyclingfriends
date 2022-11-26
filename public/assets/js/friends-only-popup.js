var privacySelect = document.getElementById("privacySelect");
var selected = privacySelect.options[privacySelect.selectedIndex].text;
if (selected == 'Friends only') {
	alert('For now, only your friends can see and enter this ride, but some riders who are not in your friends list have already entered.');
}