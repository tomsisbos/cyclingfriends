// Click on upload button
var profileGalleryForm = document.querySelector('#profileGalleryForm')
var uploadProfileGallery = document.querySelector('#uploadProfileGallery')
uploadProfileGallery.addEventListener('change', async function (e) {

	// Get form data into queryData
    var newGalleryData = new FormData(profileGalleryForm)
    newGalleryData.append('uploadProfileGallery', true)
    console.log(newGalleryData)
    
    // Proceed AJAX request and treat data in the callback function
    ajaxPostFormDataRequest(apiUrl, newGalleryData, (response) => {
        showResponseMessage(response)
        console.log(response)
    } )
} )

// Click on delete gallery button
if (document.querySelector('#deleteProfileGallery')) {
    var deleteProfileGallery = document.querySelector('#deleteProfileGallery')
    deleteProfileGallery.addEventListener('click', async function (e) {
        e.preventDefault()
        answer = await openConfirmationPopup('Do you really want to delete your profile gallery ?')
        if (answer) {
            ajaxGetRequest ('/actions/riders/profile/profileApi.php?deleteGallery=true', (response) => {
                document.querySelector('.gallery').innerHTML = ''
                showResponseMessage(response)
            } )
        }
    } )
}


function displayFilelist () {
	var input = document.getElementById('uploadProfileGallery')
	var output = document.getElementById('fileList')

	output.innerHTML = '<ul>'
	for (var i = 0; i < input.files.length; ++i) {
		output.innerHTML += '<li>' + input.files.item(i).name + ' (' + input.files.item(i).size.toString().slice(0, -3) + 'Ko)</li>'
	}
	output.innerHTML += '</ul>'
}

// API update on caption input changes
var myModal = document.getElementById("myModal")
myModal.querySelectorAll('input').forEach(input => {
    input.addEventListener('change', updateInput)
} )

function updateInput (e) {
    var captionInfos = {
        updatecaption: true,
        caption: e.target.value,
        img_id: getIdFromString(e.target.name)[0]
    }
    return new Promise ((resolve, reject) => {
        ajaxJsonPostRequest ('/actions/riders/profile/profileApi.php', captionInfos, afterUpdating)
        function afterUpdating (response) {
            e.target.value = response.caption

            console.log(response)
            resolve(response)
        }
    } )
}