const urlParams = new URLSearchParams(window.location.search)
const rideId    = urlParams.get('id')

// Button handler
document.getElementById('deleteButton').addEventListener('click', async () => {
    var answer = await openConfirmationPopup('このライドが削除されます。宜しいですか？')
    if (answer) {
        ajaxGetRequest ('/api/ride.php' + "?ride-delete=" + rideId, async (login) => {
            window.location.replace('/' + login + '/rides')
        } )
    }
} )