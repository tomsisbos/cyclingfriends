var apiUrl = "/api/ride.php"

var joinButton = document.getElementById('join')
var quitButton = document.getElementById('rd-quit')
var rideId = getIdFromString(location.pathname)

if (joinButton) {
    joinButton.addEventListener('click', () => {
        ajaxGetRequest (apiUrl + "?is-bike-accepted=" + rideId, async (response) => {
            if (response.answer　|| response.bikes_list == '車種問わず') join()
            else {
                var answer = await openConfirmationPopup ('このライドで参加が認められている車種は次の通り： ' + response.bikes_list + '。登録されているバイクの中で、該当する車種はありません。それでもエントリーしますか？')
                if (answer) join()
            }
        } )
    } )
}

if (quitButton) {
    quitButton.addEventListener('click', () => {
        window.location.href = "/ride/" + rideId + "/quit"
    } )
}


function join () {
    ajaxGetRequest(apiUrl + "?get-questions=" + rideId, async (questions) => {
        console.log(questions)
        var phase = 0
        var answers = {
            type: 'post-answers',
            data: []
        }
        while (phase < questions.length) {
            var answer = await openPopup(questions[phase])
            answers.data.push( {
                id: questions[phase].id,
                answer
            } )
            phase++
            console.log(answer)
        }
        console.log(answers)
        ajaxJsonPostRequest(apiUrl, answers, (response) => {
            console.log(response)
            window.location.href = "/ride/" + rideId + "/join"
        } )
    } )
}

function openPopup (question) {
	return new Promise ((resolve, reject) => {
        console.log(question.question)
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
            } )
            confirmationPopup.innerHTML = question.question + '<select id="answer">' + $options + '</select><div class="d-flex p-2 justify-content-center"><div class="btn smallbutton bg-darkred" id="cancel">戻る</div><div class="btn smallbutton bg-darkgreen" id="ok">確定</div></div>'
        }

		modal.appendChild(confirmationPopup)

		// On click on ok button, close the popup and return input content
		document.querySelector('#ok').addEventListener('click', () => {
            var answer = document.querySelector('#answer').value
			modal.remove()
			resolve(answer)
		} )
        
		// On click on cancel button, close the popup
		document.querySelector('#cancel').addEventListener('click', () => {
			modal.remove()
			reject()
		} )
	} )
}