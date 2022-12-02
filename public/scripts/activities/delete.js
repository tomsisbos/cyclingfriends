window.location

// Button handler
document.querySelectorAll('#deleteButton').forEach( (element) => {
    const activityId = element.dataset.id
    element.addEventListener('click', async () => {
        var answer = await openConfirmationPopup('Do you really want to delete this activity ?')
        if (answer) {
            ajaxGetRequest ('/api/activity.php' + "?delete=" + activityId, async (login) => {
                console.log(login)
                window.location.replace('/' + login + '/activities')
            } )
        }
    } )
    
} )