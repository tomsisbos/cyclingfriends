// Variables declarations
var sendMessageButton = document.getElementById('sendMessageButton')
var messageModal      = document.getElementById('messageModal')
var modalBlock        = document.getElementById('messageModalBlock')

// This is what happens on click on sendMessageButton
var openMessageModal = function () {
	messageModal.style.display = "block";
	
	// Close on clicking outside modal-block
	messageModal.onclick = function(e){
		var eTarget = e ? e.target : event.srcElement
		if ((eTarget !== modalBlock) && (eTarget !== messageModal)){
			// Nothing
		}else{
			closeMessageModal()
		}
	}
}

function closeMessageModal() {
	messageModal.style.display = "none";
}

// Add event listener to sendMessageButton
if (sendMessageButton) {
	sendMessageButton.addEventListener('click', openMessageModal)
}