const urlParams = new URLSearchParams(window.location.search)
const rideId    = urlParams.get('id')

// Button handler
document.getElementById('deleteButton').addEventListener('click', async () => {
    var answer = await openConfirmationPopup('Do you really want to delete this ride ?')
    if (answer) {
        ajaxGetRequest ('/api/ride.php' + "?ride-delete=" + rideId, async (login) => {
            window.location.replace('/' + login + '/rides')
        } )
    }
} )