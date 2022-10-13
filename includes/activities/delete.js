const urlParams = new URLSearchParams(window.location.search)
const activityId    = urlParams.get('id')

// Button handler
document.getElementById('deleteButton').addEventListener('click', async () => {
    var answer = await openConfirmationPopup('Do you really want to delete this activity ?')
    if (answer) {
        ajaxGetRequest ('/actions/activities/activityApi.php' + "?activity-delete=" + activityId, async (response) => {
            console.log(response)
            window.location.replace('/activities/myactivities.php')
        } )
    }
} )