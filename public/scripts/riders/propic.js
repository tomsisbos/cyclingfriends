var propicModal = document.getElementById("propicModal")
var modalBlock  = document.querySelector(".modal-block")

    document.querySelector('#propic').addEventListener('click', () => {
        
        propicModal.style.display = "block";
        
        // Close on clicking outside modal-block
        propicModal.onclick = function (e) {
            var eTarget = e ? e.target : event.srcElement
            if ((eTarget === modalBlock) || (eTarget === propicModal)) closePropicModal()
        }
    } )
    
    const closePropicModal = () => propicModal.style.display = "none"