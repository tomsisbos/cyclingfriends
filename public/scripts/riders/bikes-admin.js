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
        addListeners()
    }
} )

addListeners()

function addListeners() {

    // Display file name, size preview and submit button on bike image upload
    var inputs = document.querySelectorAll('.js-bike-input')
    inputs.forEach( (input) => {
        input.addEventListener('change', (e) => {
            var input = e.target
            var output = input.closest('.js-bike-container').querySelector('.js-file-preview')
            if (input.files[0]) {
                output.innerHTML = input.files[0].name + ' (' + input.files[0].size.toString().slice(0, -3) + 'Ko)' + `
                <input type="submit" value="送信" class="btn smallbutton" />`
            }
        } )
    } )

    // API update on input changes
    document.querySelectorAll('.js-bike-type').forEach( (input) => { input.addEventListener('change', updateInfo) } ) 
    document.querySelectorAll('.js-bike-model').forEach( (input) => { input.addEventListener('change', updateInfo) } ) 
    document.querySelectorAll('.js-bike-wheels').forEach( (input) => { input.addEventListener('change', updateInfo) } ) 
    document.querySelectorAll('.js-bike-components').forEach( (input) => { input.addEventListener('change', updateInfo) } ) 
    document.querySelectorAll('.js-bike-description').forEach( (input) => { input.addEventListener('change', updateInfo) } ) 

    // Delete a bike
    document.querySelectorAll('.js-delete-bike').forEach( (deleteBikeButton) => { 
        deleteBikeButton.addEventListener('click', async (e) => {
            var bikeId = e.target.closest('.js-bike-container').getAttribute('bike_id')
            var bikeDiv = e.target.closest('.container-admin')
            answer = await openConfirmationPopup('このバイクを削除します。宜しいですか？')
            if (answer) {
                ajaxGetRequest (apiUrl + '?deleteBike=' + bikeId, (response) => {
                    if (response[0] == true) bikeDiv.remove()
                    showResponseMessage(response)
                } )
            }
        } )
    } )

    // Open modal on bike image click
    document.querySelectorAll('.bike-image-img').forEach( (bikeImage) => {
        bikeImage.addEventListener('click', (e) => {
            openSingleModal(e.target.src)
        } )
    } )

}

function updateInfo (e) {
    var bikeId = e.target.closest('.js-bike-container').getAttribute('bike_id')
	ajaxGetRequest (apiUrl + '?' + e.target.name + '=' + e.target.value + '&id=' + bikeId, (response) => {
        // After database update, update bike id element attributes
        if (!document.querySelector('#bikeImageFile' + bikeId)) {
            var input = document.querySelector('#bikeImageFileNew')
            input.id = 'bikeImageFile' + response[0]
            input.previousElementSibling.setAttribute('for', 'bikeImageFile' + response[0])
        }
        e.target.closest('form').setAttribute('bike_id', response[0])
        e.target.closest('.js-bike-container').setAttribute('bike_id', response[0])
	} )
}

