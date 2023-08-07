// Buttons handler
document.querySelectorAll('.js-delete-ride').forEach(element => {
    const rideId = element.dataset.id
    element.addEventListener('click', async () => {
        var answer = await openConfirmationPopup('このツアーが削除されます。宜しいですか？')
        if (answer) {
            ajaxGetRequest ('/api/ride.php' + "?ride-delete=" + rideId, async (login) => {
                window.location.replace('/ride/organizations')
            } )
        }
    } )
} )
