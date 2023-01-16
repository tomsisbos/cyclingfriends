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
    ajaxGetRequest (apiUrl + "?get-questions=" + rideId, async (questions) => {
        console.log(questions)
        questions.forEach(async (question) => {
            var answer = await openPopup(question)
            console.log(answer)

        } )
        ///window.location.href = "/ride/" + rideId + "/join"
    } )
}

function openPopup (question) {
	return new Promise ((resolve, reject) => {
		var modal = document.createElement('div')
		modal.classList.add('modal', 'd-flex')
		document.querySelector('body').appendChild(modal)
		modal.addEventListener('click', (e) => {
			if ((e.target != confirmationPopup || e.target != confirmationPopup.firstElementChild) && (e.target == modal)) modal.remove()
		} )
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
            confirmationPopup.innerHTML = question.question + '<select id="answer">' + $options + '</select><div class="btn smallbutton" id="ok">確定</div>'
        }

		modal.appendChild(confirmationPopup)
		// On click on ok button, close the popup and return input content
		document.querySelector('#ok').addEventListener('click', () => {
            var answer = document.querySelector('#answer').value
			modal.remove()
			resolve(answer)
		} )
	} )
}