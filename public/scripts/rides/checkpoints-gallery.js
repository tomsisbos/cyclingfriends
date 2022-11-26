// API requests

var myModal = document.getElementById("myModal")
myModal.querySelectorAll('input').forEach(input => {
    input.addEventListener('change', updateInput)
} )
myModal.querySelectorAll('textarea').forEach(textArea => {
    textArea.addEventListener('change', updateTextArea)
} )

function updateInput (e) {
    var updateInfos = {
        field: 'name',
        value: e.target.value,
        ride_id: getParam('id'),
        checkpoint_id: getIdFromString(e.target.name)[0]
    }
    return new Promise ((resolve, reject) => {
        ajaxJsonPostRequest ('/api/rides/course', updateInfos, afterUpdating)
        function afterUpdating (response) {
            document.querySelectorAll('.numbertext').forEach(numbertext => {
                if (numbertext.closest('.mySlides').style.display === 'block') {
                    console.log(response)
                    resolve(response)
                }
            } )
            // Update name in thumbnails display
            document.getElementById(response.checkpoint_id).querySelector('.summary-checkpoint-name').innerText = response.value
        }
    } )
}

function updateTextArea (e) {
    var updateInfos = {
        field: 'description',
        value: e.target.value,
        ride_id: getParam('id'),
        checkpoint_id: getIdFromString(e.target.name)[0]
    }
    console.log(updateInfos)
    return new Promise ((resolve, reject) => {
        ajaxJsonPostRequest ('/api/rides/course', updateInfos, afterUpdating)
        function afterUpdating (response) {
            document.querySelectorAll('.numbertext').forEach(numbertext => {
                if (numbertext.closest('.mySlides').style.display === 'block') {
                    console.log(response)
                    resolve(response)
                }
            } )
            // Update caption in thumbnails display
            console.log(response)
            document.getElementById(response.checkpoint_id).querySelector('.summary-checkpoint-description').innerText = response.value
        }
    } )
}