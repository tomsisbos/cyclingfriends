// Set API address
var apiUrl = '/users/mailbox/api.php'


/* Select a tab */

// Set global variables
var friendsTab  = document.getElementById('friendsTab')
var publicTab   = document.getElementById('publicTab')
var mbxSearch   = document.querySelector('.mbx-search')
var queryresult = document.querySelectorAll('.mbx-queryresult li')
var searchQuery = document.getElementById('searchQuery')

// On click on friendsTab
friendsTab.addEventListener('click', () => {

	// Set the tab on top
	friendsTab.classList.add('top')
	publicTab.classList.remove('top')
	
	// Change the search box placeholder
	searchQuery.setAttribute('placeholder', 'Search for a friend...')
	
	// Change colouring of the page (pink)
	mbxSearch.style.backgroundColor = '#fccddd'
	queryresult.forEach( (li) => {
		li.style.backgroundColor  = '#ffe6ee'
	} )

	// Clear mbxInner content
	mbxInner.innerHTML = ''
	
	// Proceed AJAX request and treat data in the callback function searchForFriend
	ajaxGetRequest(apiUrl + "?display-friends=true", displayFriendsMessages)
	
} )

// On click on publicTab
publicTab.addEventListener('click', () => {

	// Set the tab on top
	publicTab.classList.add('top')
	friendsTab.classList.remove('top')
	
	// Change the search box placeholder
	searchQuery.setAttribute('placeholder', 'Search for a rider...')
	
	// Change colouring of the page (grey)
	mbxSearch.style.backgroundColor = '#e9ecef'

	// Clear mbxInner content
	mbxInner.innerHTML = ''
	
	// Proceed AJAX request and treat data in the callback function searchForFriend
	ajaxGetRequest(apiUrl + "?display-public=true", displayPublicMessages)
	
} )

function displayFriendsMessages (response) {

	console.log(response)
	
	// Display friends list in mbx innerHTML
	// Create an ul embracing query results list
	var mbxQueryResult = document.createElement('ul')
	mbxQueryResult.classList.add('mbx-queryresult')
	if (response.length > 0) {
		for (var i = 0; i < response.length ; i++) {
			// Every result will be a list element
			var mbxQueryResultLi = document.createElement('li')
			mbxQueryResultLi.id = 'mbx-queryresult-' + response[i].friend.id;
			// Charge profile picture from API and set it the same way as the rest of the website
			var roundPropicContainer = document.createElement('div')
			roundPropicContainer.classList.add('round-propic-container')
			var roundPropicImg = document.createElement('img')
			roundPropicImg.classList.add('round-propic-img')
			roundPropicImg.src = response[i].friend.propic
			// Set the column part of the list element
			var mbxQueryResultColumn = document.createElement('div')
			mbxQueryResultColumn.classList.add('mbx-queryresult-column')
			var mbxQueryResultLogin = document.createElement('div')
			mbxQueryResultLogin.classList.add('mbx-queryresult-login')
			mbxQueryResultLogin.innerText = response[i].friend.login
			
			// Build the element by appending everything in the right order
			mbxInner.appendChild(mbxQueryResult)
			mbxQueryResult.appendChild(mbxQueryResultLi)
			mbxQueryResultLi.appendChild(roundPropicContainer)
			roundPropicContainer.appendChild(roundPropicImg)
			mbxQueryResultLi.appendChild(mbxQueryResultColumn)
			mbxQueryResultColumn.appendChild(mbxQueryResultLogin)
			// Only display time and message if API sends at least one back
			if (response[i].time) {
				var mbxQueryResultTime = document.createElement('div')
				mbxQueryResultTime.classList.add('mbx-queryresult-time')
				mbxQueryResultTime.innerText = response[i].time
				mbxQueryResultColumn.appendChild(mbxQueryResultTime)
				var mbxQueryResultMsg = document.createElement('div')
				mbxQueryResultMsg.classList.add('mbx-queryresult-msg')
				mbxQueryResultMsg.innerText = truncateString (response[i].message, 50)
				mbxQueryResultColumn.appendChild(mbxQueryResultMsg)
			}
		}
	} else {
		errorMessage = document.createElement('p')
		errorMessage.classList.add('error-message')
		errorMessage.innerHTML = 'No friend has been found.'
		errorBlock = document.createElement('div')
		errorBlock.classList.add('error-block','m-0')
		errorBlock.appendChild(errorMessage)
		mbxInner.appendChild(errorBlock)
	}
	// Sets click event on new query results
	var queryresult = document.querySelectorAll(".mbx-queryresult li")
	queryresult.forEach(defineLiClickEvent)
	
	// Change colouring of the page (pink)
	queryresult.forEach( (li) => {
		li.style.backgroundColor  = '#ffe6ee'
	} )
	
}

function displayPublicMessages (response) {
		
	// Display the list of public users having a log with connected user in mbx innerHTML
	// Create an ul embracing query results list
	var mbxQueryResult = document.createElement('ul')
	mbxQueryResult.classList.add('mbx-queryresult')
	if (response) {
		console.log(response)
		for (var i = 0; i < response.length ; i++) {
			// Every result will be a list element
			var mbxQueryResultLi = document.createElement('li')
			mbxQueryResultLi.id = 'mbx-queryresult-' + response[i].friend.id;
			// Charge profile picture from API and set it the same way as the rest of the website
			var roundPropicContainer = document.createElement('div')
			roundPropicContainer.classList.add('round-propic-container')
			var roundPropicImg = document.createElement('img')
			roundPropicImg.classList.add('round-propic-img')
			roundPropicImg.src = response[i].friend.propic
			// Set the column part of the list element
			var mbxQueryResultColumn = document.createElement('div')
			mbxQueryResultColumn.classList.add('mbx-queryresult-column')
			var mbxQueryResultLogin = document.createElement('div')
			mbxQueryResultLogin.classList.add('mbx-queryresult-login')
			mbxQueryResultLogin.innerText = response[i].friend.login
			
			// Build the element by appending everything in the right order
			mbxInner.appendChild(mbxQueryResult)
			mbxQueryResult.appendChild(mbxQueryResultLi)
			mbxQueryResultLi.appendChild(roundPropicContainer)
			roundPropicContainer.appendChild(roundPropicImg)
			mbxQueryResultLi.appendChild(mbxQueryResultColumn)
			mbxQueryResultColumn.appendChild(mbxQueryResultLogin)
			// Only display time and message if API sends at least one back
			if (response[i].time) {
				var mbxQueryResultTime = document.createElement('div')
				mbxQueryResultTime.classList.add('mbx-queryresult-time')
				mbxQueryResultTime.innerText = response[i].time
				mbxQueryResultColumn.appendChild(mbxQueryResultTime)
				var mbxQueryResultMsg = document.createElement('div')
				mbxQueryResultMsg.classList.add('mbx-queryresult-msg')
				mbxQueryResultMsg.innerText = truncateString (response[i].message, 50)
				mbxQueryResultColumn.appendChild(mbxQueryResultMsg)
			}
		}
	// Display an error message if no result have been found
	} else {
		errorMessage = document.createElement('p')
		errorMessage.classList.add('error-message')
		errorMessage.innerHTML = 'You have no conversation here yet. Try to search for a rider in the above searchbar.'
		errorBlock = document.createElement('div')
		errorBlock.classList.add('error-block','m-0')
		errorBlock.appendChild(errorMessage)
		mbxInner.appendChild(errorBlock)
	}
	
	// Sets click event on new query results
	var queryresult = document.querySelectorAll(".mbx-queryresult li")
	queryresult.forEach(defineLiClickEvent)
	
	// Change colouring of the page (grey)
	queryresult.forEach( (li) => {
		li.style.backgroundColor  = '#f9f9f9'
	} )
}
	
	
/* Search for an user */

// Set global variables
var searchForm  = document.getElementById('searchForm')
var searchQuery = document.getElementById('searchQuery')
var mbxInner    = document.querySelector(".mbx-inner")
	
// Get form data into queryData on submission of the form
searchForm.addEventListener('submit', (e) => {
	
	// Prevents default behavior of the submit button
	e.preventDefault()
	
	// Get form data into queryData and adds tab id
	var queryData = new FormData(searchForm)
	if (friendsTab.classList.contains('top')) {
		queryData.append('tab_id', 'friends')
	} else {
		queryData.append('tab_id', 'public')
	}
	
	// Proceed AJAX request and treat data in the callback function searchForFriend
	ajaxPostFormDataRequest(apiUrl, queryData, displaySearchResults)
	
	function displaySearchResults (response) { 
		// Clear mbxInner content
		mbxInner.innerHTML = ''
		// Reset searchQuery value to empty after submission
		searchQuery.value = ''
		
		if (friendsTab.classList.contains('top')) {
			displayFriendsMessages(response)
		} else {
			displayPublicMessages(response)
		}
	}

} )


/* Display chat on click on messagebox query result, and set auto refresh functionnality */

// Set global variables
var chatInner   = document.querySelector(".chat-inner")
queryresult.forEach(defineLiClickEvent)
	
// Defines a click event on queryresult li and passes user id as an argument
function defineLiClickEvent (queryresultLi) {
	queryresultLi.addEventListener("click", function(e) { openChat (e) })
}
	
// Happens when click on queryresult li
function openChat (e) {
	
	var userId = getIdFromString(e.target.closest('.mbx-queryresult li').id)
	console.log(userId)
	
	// Set current user id attribute to ChatInner
	chatInner.dataset.userid = userId
	
	// Set chat header profile icon to selected user icon
	var chatHeader = document.querySelector('.chat-header')
	
	// Clear chat header to begin with
	chatHeader.innerHTML = ''
	
	// Generate link to user profile
	var iconUserLink = document.createElement('a')
	iconUserLink.setAttribute('href', '/riders/profile.php?id=' + userId)
	chatHeader.appendChild(iconUserLink)
	
	// Generate user icon from mailbox php infos
	var iconContainer = document.createElement('div')
	iconContainer.classList.add('round-propic-container')
	iconUserLink.appendChild(iconContainer)
	
	var icon          = document.getElementById(userId.str).querySelector('img')
	var iconSrc       = icon.getAttribute('src')
	var headerIcon    = document.createElement('img')
	headerIcon.classList.add('round-propic-img')
	headerIcon.setAttribute('src', iconSrc)
	iconContainer.appendChild(headerIcon)
	
	// Generate user login from mailbox php infos
	var login         = document.querySelector('#' + userId.str + ' .mbx-queryresult-login').innerText
	var headerLogin   = document.createElement('h2')
	headerLogin.innerText = login
	loginUserLink = iconUserLink.cloneNode(false)
	loginUserLink.classList.add('discreet-link')
	chatHeader.appendChild(loginUserLink)
	loginUserLink.appendChild(headerLogin)
	
	// Set a white background
	chatInner.style.backgroundColor = '#ffffff'
	
	// Displays input section
	var chatInput = document.querySelector('.chat-input')
	chatInput.style.display = 'flex'
	
	// Clear chatInner
	chatInner.innerHTML = ''
	
	// Proceed AJAX request and treat data in the callback function displayMessages
	ajaxGetRequest (apiUrl + "?receiver_id=" + userId, displayMessages)
	
	// Callback function : display user messages in chatInner 
	function displayMessages (response) {
		
		response.message.forEach( (message) => {
			
			// Create and set message container (bubble)
			var bubble = document.createElement('div')
			bubble.classList.add('bubble')
			// Create, set and append message content to bubble
			bubble.message = document.createElement('div')
			bubble.message.classList.add('content')
			// Set class regarding to sender identity
			if (message.sender.id != userId) {
				bubble.classList.add('sender')
			} else if (friendsTab.classList.contains('top')){
				bubble.classList.add('receiver-friend')
			} else {
				bubble.classList.add('receiver-public')
			}
			bubble.message.innerText = message.message
			bubble.appendChild(bubble.message)
			// Create, set and append time to bubble
			bubble.time = document.createElement('div')
			bubble.time.classList.add('bubble-time')
			bubble.time.innerText = message.time
			bubble.appendChild(bubble.time)
			// Append bubble to chatInner
			chatInner.appendChild(bubble)
		
		} )
				
		// Automatically scroll to the bottom of chatInner when a new message is posted
		chatInner.scrollTop = chatInner.scrollHeight
		
		// Sets interval properties
		autoRefresh = window.setInterval(refresh, 2000)
			
		// Function to call back periodically
		function refresh () {
			
			// Sets userId according to the ChatInner data-userid attribute
			userId.id = chatInner.dataset.userid
			
			// Make a AJAX request and treat data in the callback function updateLog
			ajaxGetRequest(apiUrl + "?receiver_id=" + userId.id, updateLog)
			
			function updateLog (response) {

				console.log(response)

				response = response.message
				
				// Capture the number of messages currently displayed and compare it to server response
				var currentState = chatInner.children.length
				
				// If there are more entries in the database than the number of messages currently displayed,
				if (response.length > currentState) {
					
					// Create and set message container (bubble)
					var bubble = document.createElement('div')
					bubble.classList.add('bubble')
					// Set class regarding to sender identity of the new message
					if (response[response.length-1].sender.id != userId) {
						bubble.classList.add('sender')
					} else if (friendsTab.classList.contains('top')) {
						bubble.classList.add('receiver-friend')
					} else {
						bubble.classList.add('receiver-public')
					}
					// Create, set and append message content to bubble
					bubble.message = document.createElement('div')
					bubble.message.classList.add('content')
					bubble.message.innerText = response[response.length-1].message
					bubble.appendChild(bubble.message)
					// Create, set and append time to bubble
					bubble.time = document.createElement('div')
					bubble.time.classList.add('bubble-time')
					bubble.time.innerText = response[response.length-1].time
					bubble.appendChild(bubble.time)
					// Append bubble to chatInner
					chatInner.appendChild(bubble)
					// Update message box excerpt
					console.log(userId.str)
					document.getElementById(userId.str).querySelector('.mbx-queryresult-msg').innerText = truncateString(bubble.message.innerText, 50)
					// Automatically scroll to the bottom of chatInner when a new message is posted
					chatInner.scrollTop = chatInner.scrollHeight
				}
			}
			
			// Clear interval on new click on any queryresult li for preventing multiple asynchronous interval
			queryresult.forEach(defineClearIntervalEvent)
			function defineClearIntervalEvent (queryresultLi) {
				queryresultLi.addEventListener('click', function (e) {
					window.clearInterval(autoRefresh)
				} )
			}
			
		}
	}
}

	
/* Send a message */

// Set global variables
var inputForm    = document.getElementById('inputForm')
var inputMessage = document.getElementById('inputMessage')
	
// Adds event listener on submission of inputForm
inputForm.addEventListener('submit', function (e) {
	
	// Prevents default behavior of the submit button
	e.preventDefault()
	
	// Get form data into newMessageData and adds receiver id
	var newMessageData = new FormData(inputForm)
	var receiver_id        = chatInner.dataset.userid
	newMessageData.append('receiver_id', receiver_id)
	
	// Proceed AJAX request and treat data in the callback function searchForFriend
	ajaxPostFormDataRequest(apiUrl, newMessageData, sendMessage)
	
	function sendMessage (response) {

		console.log(response)

		// Reset inputMessage value to empty after submission
		inputMessage.value = ''
		// Display response in chatInner
			// Create and set message container (bubble)
			var bubble = document.createElement('div')
			bubble.classList.add('bubble')
			bubble.classList.add('sender')
			// Create, set and append message content to bubble
			bubble.message = document.createElement('div')
			bubble.message.classList.add('content')
			bubble.message.innerText = response.message
			bubble.appendChild(bubble.message)
			// Create, set and append time to bubble
			bubble.time = document.createElement('div')
			bubble.time.classList.add('bubble-time')
			bubble.time.innerText = response.time
			bubble.appendChild(bubble.time)
			// Append bubble to chatInner
			chatInner.appendChild(bubble)
			// Update message box excerpt
			document.querySelector('#mbx-queryresult-' + receiver_id + ' .mbx-queryresult-msg').innerText = truncateString (bubble.message.innerText, 50)
		// Automatically scroll to the bottom of chatInner when a new message is posted
		chatInner.scrollTop = chatInner.scrollHeight;
	}
} )