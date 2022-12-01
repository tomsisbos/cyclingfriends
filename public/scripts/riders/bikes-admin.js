var apiUrl = '/api/riders/profile.php'

// Adding a new bike block
document.querySelector('button#addBike').addEventListener('click', () => {
    // If a new block has already been added, just scroll to it
    if (document.getElementById('bikeImageFileNew')) {
        document.getElementById('bikeImageFileNew').scrollIntoView()
    // Else, add a new block
    } else {
        var randnumber = Math.floor(Math.random() * 10)
        if (randnumber == 0) {randnumber = 1}
        var newBikeBlock = document.createElement('div')
        newBikeBlock.className = 'container container-admin js-bike-container d-flex flex-column gap'
        newBikeBlock.setAttribute('bike_id', 'new')
        newBikeBlock.innerHTML = `
        <div class="d-flex gap-20">
            <form title="Upload image" class="js-bike-image-form col-4" name="bike-image-form" enctype="multipart/form-data" method="post" action="/actions/riders/profile/bikeImageAction.php">
                <div class="bike-image-container">
                    <img class="bike-image-img" src="/media/default-bike-` + randnumber + `.svg">
                    <div class="image-icon-container">
                        <label for="bikeImageFileNew">
                            <span class="image-icon iconify" data-icon="ic:baseline-add-a-photo" data-width="20" data-height="20"></span>
                        </label>
                        <input id="bikeImageFileNew" type="file" class="js-bike-input hidden" name="bikeimagefile" size=50 />
                        <input type="hidden" name="MAX_FILE_SIZE" value="500000" />
                        <input type="hidden" name="bike-id" value="new" />
                        <div title="Delete bike" class="js-delete-bike" >
                            <span class="image-icon iconify" data-icon="el:remove-circle" data-width="20" data-height="20"></span>
                        </div>
                    </div>
                </div>
                <div class="js-file-preview filename"></div>
            </form>
            <form method="post" bike_id="new" class="fullwidth">
                <div class="row">
                    <div class="col mb-3">
                        <label><strong>Type : </strong></label>
                        <select name="bike-type" class="js-bike-type admin-field">
                            <option value="Other">Other</option>
                            <option value="City bike">City bike</option>
                            <option value="Road bike">Road bike</option>
                            <option value="Mountain bike">Mountain bike</option>
                            <option value="Gravel/Cyclocross bike">Gravel/Cyclocross bike</option>
                        </select>
                    </div>
                    <div class="col mb-3">
                        <label><strong>Model : </strong></label>
                        <input type="text" name="bike-model" class="js-bike-model admin-field">
                    </div>
                </div>
                <div class="row">
                    <div class="col mb-3">
                        <label><strong>Wheels : </strong></label>
                        <input type="text" name="bike-wheels" class="js-bike-wheels admin-field">
                    </div>
                    <div class="col mb-3">
                        <label><strong>Components : </strong></label>
                        <input type="text" name="bike-components" class="js-bike-components admin-field">
                    </div>
                </div>
                <div class="col mb-3">
                    <label><strong>Description : </strong></label>
                    <textarea name="bike-description" class="js-bike-description col-9 admin-field d-block fullwidth"></textarea>
                </div>
            </form>
        </div>`
        document.getElementById('bikes').appendChild(newBikeBlock)
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
                <input type="submit" value="Send" class="btn smallbutton" />`
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
            console.log(bikeId)
            var bikeDiv = e.target.closest('.container-admin')
            answer = await openConfirmationPopup('Do you really want to delete this bike ?')
            if (answer) {
                ajaxGetRequest (apiUrl + '?deleteBike=' + bikeId, (response) => {
                    if (response[0] == true) {
                        bikeDiv.remove()
                    }
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

