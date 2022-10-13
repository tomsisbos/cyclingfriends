<?php
session_start();
include $_SERVER["DOCUMENT_ROOT"] . '/includes/functions.php';

// In case an Ajax request have been detected
if(isAjax()){
	
	// If a 'mbx-query' index has been detected = new search query
	if(isset($_POST['mbx-query']) AND !empty($_POST['mbx-query'])){
		require $_SERVER["DOCUMENT_ROOT"] . '/actions/databaseAction.php';
		$friendlist = getFriendsList($_SESSION['id']);
		$getFriend = $db->prepare("SELECT id, login FROM users WHERE (login LIKE '%' ? '%') AND id IN (" .implode(', ', $friendlist). ")");
		$getFriend->execute(array($_POST['mbx-query']));
		for($i = 0; $queryresult = $getFriend->fetch(); $i++){
			$log = getConversation($_SESSION['id'], $queryresult['id']);
			$chatfriends[$i]['id']              = $queryresult['id'];
			$chatfriends[$i]['login']           = $queryresult['login'];
			if(!empty($log)){
				$chatfriends[$i]['lastmsg']     = $log[count($log) - 1]['message'];
				$chatfriends[$i]['lastmsgtime'] = $log[count($log) - 1]['time'];
			}
			$propic = downloadProfilePicture($queryresult['id']);
			if(is_array($propic)){
				$chatfriends[$i]['propic']      = 'data:image/jpeg;base64,' . base64_encode($propic['img']);
			}else{
				$chatfriends[$i]['propic']      = '\includes\media\default-profile-' . getDefaultProfilePicture($queryresult['id'])['default_profilepicture_id'] . '.jpg';
			}
		}
		// Send back query result data into a json object
		if(isset($chatfriends)){
			echo json_encode($chatfriends);
		}else{
			echo json_encode(NULL);
		}
		// If the query is empty
	}else if(isset($_POST['mbx-query']) AND empty($_POST['mbx-query'])){
		$friendslist = getFriendsList($_SESSION['id']);					
		foreach($friendslist as $i => $id){
			$log = getConversation($_SESSION['id'], $id);
			$chatfriends[$i]['id']              = $id;
			$chatfriends[$i]['login']           = getLoginById($id);
			if(!empty($log)){
				$chatfriends[$i]['lastmsg']     = $log[count($log) - 1]['message'];
				$chatfriends[$i]['lastmsgtime'] = $log[count($log) - 1]['time'];
			}
			$propic = downloadProfilePicture($id);
			if(is_array($propic)){
				$chatfriends[$i]['propic']      = 'data:image/jpeg;base64,' . base64_encode($propic['img']);
			}else{
				$chatfriends[$i]['propic']      = '\includes\media\default-profile-' . getDefaultProfilePicture($id)['default_profilepicture_id'] . '.jpg';
			}
		}
		// Send back all friends data into a json object
		if(isset($chatfriends)){
			echo json_encode($chatfriends);
		}else{
			echo json_encode(NULL);
		}
	}
	
	// If a 'message' index has been detected = new message,
	if(isset($_POST['message'])){
		if(!empty($_POST['message'])){
			// Store it in the messages table server side
			addMessage($_POST['receiver_id'], $_POST['message']);
			// Send back message data into a json object
			echo json_encode(['senderId' => $_SESSION['id'], 'senderLogin' => $_SESSION['login'], 'receiverId' => $_POST['receiver_id'], 'receiverLogin' => getLoginById($_POST['receiver_id']), 'message' => $_POST['message'], 'time' => date('Y-m-d H:i:s')]);
		}
	}
	
	// If a 'query_conversation' index has been detected = asking for an user's conversation log,
	if(isset($_GET['receiver_id'])){
		// Get user's conversation log with connected user
		$log = getConversation($_SESSION['id'], $_GET['receiver_id']);
		// Send back log data into a json object
		echo json_encode(getConversation($_SESSION['id'], $_GET['receiver_id']));
	}
	
	if(!headers_sent()){
		header('Content-type: application/json');
	}
	
}

?>