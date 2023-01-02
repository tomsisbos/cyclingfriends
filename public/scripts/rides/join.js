    var apiUrl = "/api/ride.php"

    var join = document.getElementById('join')
    var quit = document.getElementById('rd-quit')
    var rideId = getIdFromString(location.pathname)
    
    if (join) {
        join.addEventListener('click', () => {
            ajaxGetRequest (apiUrl + "?is-bike-accepted=" + rideId, async (response) => {
                if (response.answer　|| response.bikes_list == '車種問わず') window.location.href = "/ride/" + rideId + "/join"
                else {
                    var answer = await openConfirmationPopup ('このライドで参加が認められている車種は次の通り： ' + response.bikes_list + '。登録されているバイクの中で、該当する車種はありません。それでもエントリーしますか？')
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