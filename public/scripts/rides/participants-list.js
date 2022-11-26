// Variables declarations
var participantsList = document.getElementById('participantsList')

// Events handlers
if (participantsList) {
  participantsList.addEventListener('click', displayParticipantsWindow)
}

// Close on clicking outside modal block
var modal    = document.querySelector(".modal")
var modalBlock = document.querySelector(".modal-block")
modal.onclick = function (e) {
  var eTarget = e ? e.target : event.srcElement
  if ((eTarget !== modalBlock) && (eTarget !== modal)){
    // Nothing
  }else{
    closeParticipantsWindow()
  }
}

function displayParticipantsWindow() {
  document.getElementById("participantsWindow").style.display = "block"
}

function closeParticipantsWindow() {
  document.getElementById("participantsWindow").style.display = "none"
}