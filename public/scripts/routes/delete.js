if (document.querySelector('#deleteRoute')) {
    document.querySelectorAll('#deleteRoute').forEach(element => {
        const routeId = element.dataset.id
        element.addEventListener('click', async () => {
            var answer = await openConfirmationPopup('このルートが削除されます。宜しいですか？')
            if (answer) {
                ajaxGetRequest ('/api/route.php' + "?route-delete=" + routeId, async (response) => {
                    console.log(response)
                    window.location.replace('/routes')
                } )
            }
        } )
    } )
}