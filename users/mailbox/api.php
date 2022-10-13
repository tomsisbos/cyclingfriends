<?php
session_start();
// Autoload
require_once $_SERVER["DOCUMENT_ROOT"] . '/class/Autoloader.php'; 
Autoloader::register(); 
require $_SERVER["DOCUMENT_ROOT"] . '/includes/functions.php';
require $_SERVER["DOCUMENT_ROOT"] . '/actions/users/securityAction.php';
require $_SERVER["DOCUMENT_ROOT"] . '/actions/databaseAction.php';

// In case an Ajax request have been detected
if (isAjax()) {
	
	// If a 'display-friends' index has been detected = Display friends list in mbxInner
	if (isset($_GET['display-friends']) AND $_GET['display-friends'] == true) {
		
		// Send back query result data into a json object
		$lastmessages = $connected_user->getLastMessages($connected_user->getFriends());
		echo json_encode($lastmessages);
	}
	
	// If a 'display-public' index has been detected = Display friends list in mbxInner
	if (isset($_GET['display-public']) AND $_GET['display-public'] == true) {
		
		// Send back query result data into a json object
		$userslist = $connected_user->getUsersWithMessages();
		$lastmessages = $connected_user->getLastMessages($userslist);
		echo json_encode($lastmessages);
	}
	
	// If a 'mbx-query' index has been detected = new search query
	if (isset($_POST['mbx-query']) AND !empty($_POST['mbx-query'])) {

		// If this is a query from the friends tab, only search users among friends list
		if ($_POST['tab_id'] == 'friends') {
			$getQuery = $db->prepare("SELECT id FROM users WHERE (login LIKE '%' ? '%') AND id IN (" .implode(', ', $connected_user->getFriends()). ")");
		// If this is a query from the public tab, search for any user except for users disabling public chat
		} else if ($_POST['tab_id'] == 'public') {
			$getQuery = $db->prepare("SELECT id FROM users WHERE (login LIKE '%' ? '%') AND id NOT IN (" .implode(', ', getUsersDisablingPublicChat()). ")");
		}
		$getQuery->execute(array($_POST['mbx-query']));
		$userslist = array_column($getQuery->fetchAll(PDO::FETCH_NUM), 0);
		$results = $connected_user->getLastMessages($userslist);
		
		// Send back query result data into a json object
		if (isset($results)) {
			echo json_encode($results);
		} else {
			echo json_encode(NULL);
		}

	// If the query is empty
	} else if (isset($_POST['mbx-query']) AND empty($_POST['mbx-query'])) {

		if ($_POST['tab_id'] == 'friends') {

			// Send back query result data into a json object
			$lastmessages = $connected_user->getLastMessages($connected_user->getFriends());
			echo json_encode($lastmessages);

		} else if ($_POST['tab_id'] == 'public') {

			// Send back query result data into a json object
			$userslist = $connected_user->getUsersWithMessages();
			$lastmessages = $connected_user->getLastMessages($userslist);
			echo json_encode($lastmessages);

		}
	}
	
	// If a 'message' index has been detected = new message,
	if (isset($_POST['message'])) {
		if (!empty($_POST['message'])) {
			$receiver = new User($_POST['receiver_id']);
			// Store it in the messages table server side
			$connected_user->sendMessage($receiver, $_POST['message']);
			// Send back message data into a json object
			echo json_encode(['sender_id' => $connected_user->id, 'sender_login' => $connected_user->login, 'receiver_id' => $receiver->id, 'receiver_login' => $receiver->login, 'message' => $_POST['message'], 'time' => date('Y-m-d H:i:s')]);
		}
	}
	
	// If a 'query_conversation' index has been detected = asking for an user's conversation log,
	if (isset($_GET['receiver_id'])) {
		$receiver = new User($_GET['receiver_id']);
		// Get user's conversation log with connected user
		$log = $connected_user->getConversation($receiver);
		// Send back log data into a json object
		echo json_encode($log);
	}
	
	if (!headers_sent()) {
		header('Content-type: application/json');
	}
	
}

?>