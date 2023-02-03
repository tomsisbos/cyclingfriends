<h3>チャット</h3> <?php

include '../actions/databaseAction.php';

// Space for error messages
if(isset($chaterror)){ echo '<div class="error-block"><p class="error-message">' .$chaterror. '</p></div>'; }

// Signup form validation
if(isset($_POST['send'])) {
	 
	// Check if user wrote something to post
	if(!empty($_POST['message'])){
		 
		// Prepare all variables
		$message    = nl2br(htmlspecialchars($_POST['message']));
		$time       = date('Y-m-d H:i:s');
		
		// Send variables into database
		$insertChatMessage = $db->prepare('INSERT INTO ride_chat(ride_id, author_id, user_login, message, time) VALUES (?, ?, ?, ?, ?)');
		$insertChatMessage->execute(array($ride->id, $connected_user->id, $connected_user->login, $message, $time));
		
		unset($message);
		unset($_POST);
		
	
	}else{
		
		$chaterror = 'Please write something into the chatbox.';
		
	}
}

// Prepare request of chat parent lines of a specific ride_id
$getRideChatParents = $db->prepare('SELECT * FROM ride_chat WHERE ride_id = ? AND parent_id IS NULL ORDER BY time ASC');
$getRideChatParents->execute(array($ride->id));


	$chat = $ride->getChat();
	forEach ($chat as $rideMessage){
		$rideMessage = new RideMessage ($rideMessage['id']);
		// First display messages which are not children messages
		if (!$rideMessage->parent) {
			// Displays chat-line in admin color if ride author has admin rights
			if ($rideMessage->author == $ride->author) {
				echo '<div class="chat-line chat-line-admin">';
			} else {
				echo '<div class="chat-line">';
			}
				?><a href="/rider/<?= $rideMessage->id ?>"><?php $rideMessage->author->displayPropic(); ?></a><?php
				echo '<div class="chat-message-block">';
					echo '<div class="chat-login">' . $rideMessage->author->login . '</div>';
					echo ' - <div class="chat-time">' . $rideMessage->time . '</div>';
					echo '<div class="chat-message">' . $rideMessage->message . '</div>';
				echo '</div>';
			echo '</div>';
		} else {
			if ($rideMessage->author == $ride->author) {
				echo '<div class="chat-line chat-line-admin child">';
			} else {
				echo '<div class="chat-line child">';
			}
				?><a href="/rider/<?= $rideMessage->id ?>"><?php $rideMessage->author->displayPropic(); ?></a><?php
				echo '<div class="chat-message-block">';
					echo '<div class="chat-login">' . $rideMessage->login . '</div>';
					echo ' - <div class="chat-time">' . $rideMessage->time . '</div>';
					echo '<div class="chat-message">' . $rideMessage->message . '</div>';
				echo '</div>';
			echo '</div>';
		}
	} ?>
	
<!-- Displays the input box -->
<form method="post" class="chat-msgbox form-floating">
	<textarea class="form-control" name="message"></textarea>
	<label class="form-label" for="floatingInput">コメントを書く...</label>
	<button type="submit" class="btn button btn button-primary" name="send">送信</button>
</form> <?php

/*
// Displays the chat parent lines if found in database
if ($getRideChatParents->rowCount() > 0) {
	while ($chat_parents = $getRideChatParents->fetch()) {
		var_dump($chat_parents);
		$parent = new RideMessage ($chat_parents['id']);
	// Displays chat-line in admin color if ride author has admin rights
	if ($parent == $ride->author) {
		echo '<div class="chat-line chat-line-admin">';
	}else{
		echo '<div class="chat-line">'; }
			?><a href="/users/profile.php?id=<?= $parent->id ?>"><?php $parent->author->displayPropic(); ?></a><?php
			echo '<div class="chat-message-block">';
				echo '<div class="chat-login">' . $parent->login . '</div>';
				echo ' - <div class="chat-time">' . $parent->time . '</div>';
				echo '<div class="chat-message">' . $parent->message . '</div>';
			echo '</div>';
		echo '</div>';
		// Prepare request of chat children lines of a specific parent_id
		$getRideChatChildren = $db->prepare('SELECT * FROM ride_chat WHERE ride_id = ? AND parent_id = ? ORDER BY time ASC');
		$getRideChatChildren->execute(array($ride->id, $parent->id));
		// Displays the chat parent lines if found in database
		if($getRideChatChildren->rowCount() > 0){
			while($chat_children = $getRideChatChildren->fetch()){
		// Displays chat-line in admin color if ride author has admin rights
			if($chat_children['user_id'] == $ride['ride_author_id']){
				echo '<div class="chat-line chat-line-admin child">';
			}else{
				echo '<div class="chat-line child">'; }
					?><a href="/users/profile.php?id=<?= $chat_children['user_id'] ?>"><?php displaysProfilePictureIcon($chat_children['user_id']); ?></a><?php
					echo '<div class="chat-message-block">';
						echo '<div class="chat-login">' . $chat_children['user_login'] . '</div>';
						echo ' - <div class="chat-time">' . $chat_children['time'] . '</div>';
						echo '<div class="chat-message">' . $chat_children['message'] . '</div>';
					echo '</div>';
				echo '</div>';
			}
		}
	}
} ?>
	
<!-- Displays the input box -->
<div class="msgbox-label">Chatbox</div>
<form method="post" class="chat-msgbox form-floating">
	<textarea class="form-control" name="message"></textarea>
	<label class="form-label" for="floatingInput">Write a comment...</label>
	<button type="submit" class="btn button btn button-primary" name="send">Send</button>
</form>
	
*/