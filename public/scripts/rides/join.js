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

        // Check if proper bike has been registered
        return new Promise((resolve, reject) => {
            ajaxGetRequest (apiUrl + "?is-bike-accepted=" + rideId, async (response) => {
                loader.stop()
                if (response.answer || response.bikes_list == '車種問わず') resolve(true)
                else {
                    var answer = await openConfirmationPopup ('このライドで参加が認められている車種は次の通り： ' + response.bikes_list + '。登録されているバイクの中で、該当する車種はありません。それでもエントリーしますか？')
                    if (answer) resolve(true)
                }
            })

        // Check if real name and birthdate have been registered
        }).then(() => {
            return new Promise((resolve, reject) => {
                ajaxGetRequest (apiUrl + "?get-missing-information=" + rideId, async (response) => {
                    if (response.length > 0)  {
                        var string = response.join('、')
                        var answer = await openAlertPopup('ライドに申し込むためには、次の情報の記入が必要です：' + string + '。<a href="/profile/edit" target="_blank">プロフィールページ</a>にてご記入頂けます。')
                        if (answer) resolve(false)
                    }
                    else resolve(true)
                })
            })
        }).then((result) => {
            if (result) join()
            else return
        })
    })
}

if (quitButton) {

    quitButton.addEventListener('click', () => {

        var loader = new CircleLoader(quitButton)
        loader.start()
        
        ajaxGetRequest(apiUrl + "?quit=" + rideId, async (response) => {
            loader.stop()
            showResponseMessage(response)
            window.location.href = "/ride/participations"
        })
    })
}



if (signupJoinButton) {
    signupJoinButton.addEventListener('click', async () => {

        var answer = await openAlertPopup ('ライドに参加するには、ログインする必要があります。<br><br>アカウントをお持ちの方は<a style="font-weight: bold" href="' + window.location.href + '/signin">こちら</a><br>アカウントをお持ちでない方は<a style="font-weight: bold" href="' + window.location.href + '/signup">こちら</a>')
        
    })
}


function join () {
    ajaxGetRequest(apiUrl + "?get-questions=" + rideId, async (questions) => {
        var phase = 0
        var answers = {
            id: rideId,
            type: 'post-answers',
            data: []
        }
        while (phase < questions.length) {
            var answer = await openPopup(questions[phase])
            answers.data.push( {
                id: questions[phase].id,
                answer
            })
            phase++
        }
        ajaxJsonPostRequest(apiUrl, answers, (response) => {
            showResponseMessage(response)
            window.location.href = "/ride/participations"
        })
    })
}

function openPopup (question) {
	return new Promise ((resolve, reject) => {
		var modal = document.createElement('div')
		modal.classList.add('modal', 'd-flex')
		document.querySelector('body').appendChild(modal)
		var confirmationPopup = document.createElement('div')
		confirmationPopup.classList.add('popup')

        // In case of text input
		if (question.type == 'text') confirmationPopup.innerHTML = question.question + '<input type="text" id="answer"></input><div class="btn smallbutton" id="ok">確定</div>'
		// In case of select input
        else if (question.type == 'select') {
            var $options = ''
            question.options.forEach(option => {
                $options += '<option value="' + option + '">' + option + '</option>'
            })
            confirmationPopup.innerHTML = question.question + '<select id="answer">' + $options + '</select><div class="d-flex p-2 justify-content-center"><div class="btn smallbutton bg-darkred" id="cancel">戻る</div><div class="btn smallbutton bg-darkgreen" id="ok">確定</div></div>'
        }

		modal.appendChild(confirmationPopup)

		// On click on ok button, close the popup and return input content
		document.querySelector('#ok').addEventListener('click', () => {
            var answer = document.querySelector('#answer').value
			modal.remove()
			resolve(answer)
		})
        
		// On click on cancel button, close the popup
		document.querySelector('#cancel').addEventListener('click', () => {
			modal.remove()
			reject()
		})
	})
}