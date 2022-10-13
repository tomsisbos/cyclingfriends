// Variables declarations
var friendsList = document.getElementById('friendsList');
var modalBlock  = document.querySelector(".modal-block")

// Events handlers
friendsList.addEventListener('click', displayFriendsWindow);

function displayFriendsWindow() {
  document.getElementById("friendsWindow").style.display = "block";
  
	// Close on clicking outside modal-block
	friendsWindow.onclick = function(e){
		var eTarget = e ? e.target : event.srcElement
		if ((eTarget !== modalBlock) && (eTarget !== friendsWindow)){
			// Nothing
		}else{
			closeFriendsWindow()
		}
	}
}

function closeFriendsWindow() {
  document.getElementById("friendsWindow").style.display = "none";
}