if (document.querySelector('#deleteRoute')) {
    document.querySelectorAll('#deleteRoute').forEach(element => {
        const routeId = element.dataset.id
        element.addEventListener('click', async () => {
            var answer = await openConfirmationPopup('Do you really want to delete this route ?')
            if (answer) {
                ajaxGetRequest ('/actions/routes/api.php' + "?route-delete=" + routeId, async (response) => {
                    console.log(response)
                    window.location.replace('/routes.php')
                } )
            }
        } )
    } )
}