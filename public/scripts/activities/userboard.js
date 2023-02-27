// If a 'pending' key is set inside sessionStorage
if (sessionStorage.getItem('pending')) {

    // Ask server very second for upload status
    const uploadStatusCheck = window.setInterval(() => {

        ajaxGetRequest ("/api/loading.php?record-type=activity", (record) => {

            // First show a common message
            showResponseMessage({'success': record.message})

            // If upload is finished, clear interval and session storage
            if (record.status == 'success') {
                window.clearInterval(uploadStatusCheck)
                sessionStorage.clear()
            } else if (record.status == 'error') {
                window.clearInterval(uploadStatusCheck)
                showResponseMessage({'error': record.message})
                sessionStorage.clear()
            }
        } )

    }, 1000)
}