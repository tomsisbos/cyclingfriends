// Button handler
document.querySelectorAll('#deleteButton').forEach( (element) => {
    const activityId = element.dataset.id
    element.addEventListener('click', async () => {
        var answer = await openConfirmationPopup('このアクティビティを削除します。宜しいですか？')
        if (answer) {
            ajaxGetRequest ('/api/activity.php' + "?delete=" + activityId, async (login) => {
                console.log(login)
                window.location.replace('/' + login + '/activities')
            } )
        }
    } )
    
} )