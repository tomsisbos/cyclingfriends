const urlParams  = new URLSearchParams(window.location.search)

// Button handler
document.querySelectorAll('#deleteButton').forEach( (element) => {
    const activityId = element.dataset.id
    element.addEventListener('click', async () => {
        var answer = await openConfirmationPopup('Do you really want to delete this activity ?')
        if (answer) {
            ajaxGetRequest ('/actions/activities/activityApi.php' + "?activity-delete=" + activityId, async (response) => {
                console.log(response)
                window.location.replace('/activities/myactivities.php')
            } )
        }
    } )
    
} )