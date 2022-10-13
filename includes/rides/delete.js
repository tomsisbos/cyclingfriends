const urlParams = new URLSearchParams(window.location.search)
const rideId    = urlParams.get('id')

// Button handler
document.getElementById('deleteButton').addEventListener('click', async () => {
    var answer = await openConfirmationPopup('Do you really want to delete this ride ?')
    if (answer) {
        console.log('here')
        ajaxGetRequest ('/rides/api.php' + "?ride-delete=" + rideId, async (response) => {
            console.log(response)
            window.location.replace('/rides/myrides.php')
        } )
    }
} )