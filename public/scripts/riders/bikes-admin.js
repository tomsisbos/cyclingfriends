import Modal from '/map/class/Modal.js'
import TimerPopup from '/map/class/TimerPopup.js'

var apiUrl = '/api/riders/profile.php'

// Adding a new bike block
document.querySelector('button#addBike').addEventListener('click', () => {
    // If a new block has already been added, just scroll to it
    if (document.getElementById('newBikeForm')) {
        document.getElementById('newBikeForm').scrollIntoView()
    // Else, add a new block
    } else {
        const bikesContainer = document.getElementById('bikes')
        const template = document.querySelector('#bikeTemplate')
        var newBikeBlock = template.content.firstElementChild.cloneNode(true)
        bikesContainer.appendChild(newBikeBlock)
        newBikeBlock.scrollIntoView()
        addListeners(newBikeBlock)
    }
} )

document.querySelectorAll('.js-bike-container').forEach(bikeBlock => addListeners(bikeBlock))

function addListeners(bikeBlock) {

    // Auto submit bike image on change
    var inputs = bikeBlock.querySelectorAll('.js-bike-input')
    inputs.forEach( (input) => {
        input.addEventListener('change', () => input.closest('form').submit())
    } )

    // API update on input changes
    bikeBlock.querySelectorAll('.js-bike-type').forEach( (input) => { input.addEventListener('change', updateInfo) } ) 
    bikeBlock.querySelectorAll('.js-bike-model').forEach( (input) => { input.addEventListener('change', updateInfo) } ) 
    bikeBlock.querySelectorAll('.js-bike-wheels').forEach( (input) => { input.addEventListener('change', updateInfo) } ) 
    bikeBlock.querySelectorAll('.js-bike-components').forEach( (input) => { input.addEventListener('change', updateInfo) } ) 
    bikeBlock.querySelectorAll('.js-bike-description').forEach( (input) => { input.addEventListener('change', updateInfo) } ) 

    // Delete a bike
    bikeBlock.querySelectorAll('.js-delete-bike').forEach((deleteBikeButton) => { 
        deleteBikeButton.addEventListener('click', async (e) => {
            var bikeId = e.target.closest('.js-bike-container').getAttribute('bike_id')
            var bikeDiv = e.target.closest('.container-admin')
            var answer = await openConfirmationPopup('このバイクを削除します。宜しいですか？')
            if (answer) {
                ajaxGetRequest (apiUrl + '?deleteBike=' + bikeId, (response) => {
                    if (response[0] == true) bikeDiv.remove()
                    showResponseMessage(response)
                } )
            }
        } )
    } )

    // Open modal on bike image click
    bikeBlock.querySelectorAll('.pf-bike-image').forEach( (bikeImage) => {
        var modal = new Modal(bikeImage.src)
        bikeImage.after(modal.element)
        bikeImage.addEventListener('click', () => modal.open())
    } )

}

function updateInfo (e) {
    var bikeId = e.target.closest('.js-bike-container').getAttribute('bike_id')
    var timerPopup = new TimerPopup({type: 'success', text: 'バイクの変更を保存しました！'}, 2)
	ajaxGetRequest (apiUrl + '?' + e.target.name + '=' + e.target.value + '&id=' + bikeId, (response) => {
        // After database update, update bike id element attributes
        if (!document.querySelector('#bikeImageFile' + bikeId)) {
            var input = document.querySelector('#bikeImageFileNew')
            input.id = 'bikeImageFile' + response[0]
            input.previousElementSibling.setAttribute('for', 'bikeImageFile' + response[0])
        }
        e.target.closest('form').setAttribute('bike_id', response[0])
        e.target.closest('.js-bike-container').setAttribute('bike_id', response[0])
        timerPopup.show()
	})
}