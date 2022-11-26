    var apiUrl = "/api/ride.php"

    var join = document.getElementById('join')
    var quit = document.getElementById('rd-quit')
    var rideId = getIdFromString(location.pathname)
    
    if (join) {
        join.addEventListener('click', () => {
            ajaxGetRequest (apiUrl + "?is-bike-accepted=" + rideId, async (response) => {
                if (response.answer) window.location.href = "/rides/ride.php?id=" + rideId + "&join=true"
                else {
                    var answer = await openConfirmationPopup ('This ride only accepts the following bike types : ' + response.bikes_list + '. You don\'t have any of these registered in your bikes list. Do you still want to enter this ride ?')
                    if (answer) {
                        window.location.href = "/ride/" + rideId + "/join"
                    }
                }
            } )
        } )
    }
    
    if (quit) {
        quit.addEventListener('click', () => {
            window.location.href = "/ride/" + rideId + "/quit"
        } )
    }