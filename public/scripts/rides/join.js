import CircleLoader from "/class/loaders/CircleLoader.js"

var apiUrl = "/api/ride.php"

var joinButton = document.getElementById('join')
var signupJoinButton = document.getElementById('signup_join')
var quitButton = document.getElementById('rd-quit')
var rideId = getIdFromString(location.pathname)

if (joinButton) {
    joinButton.addEventListener('click', async () => {
        var loader = new CircleLoader(joinButton)
        loader.start()
    })
}

if (quitButton) {
    quitButton.addEventListener('click', async () => {
        var answer = await openConfirmationPopup ('ツアーへのエントリーが取り消されます。宜しいですか？')
        if (answer) {
            var loader = new CircleLoader(quitButton)
            loader.start()
            ajaxGetRequest(apiUrl + "?quit=" + rideId, async (response) => {
                loader.stop()
                showResponseMessage(response)
                window.location.href = "/ride/participations"
            })
        }
    })
}

if (signupJoinButton) {
    signupJoinButton.addEventListener('click', async () => {
        var answer = await openAlertPopup ('ツアーに参加するには、アカウントが必要です。<br><br>アカウントをお持ちの方は<a style="font-weight: bold" href="' + window.location.href + '/entry/signin">こちら</a><br>アカウントをお持ちでない方は<a style="font-weight: bold" href="' + window.location.href + '/entry">こちら</a>')
    })
}