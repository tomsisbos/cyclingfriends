<h3 id="chat">チャット</h3> <?php

// Space for error messages
if (isset($chaterror)) echo '<div class="error-block"><p class="error-message">' .$chaterror. '</p></div>';

// Signup form validation
if (isset($_POST['send'])) {
	 
	// Check if user wrote something to post
	if (!empty($_POST['message'])) {
		 
		// Prepare all variables
		$ride->postMessage(nl2br(htmlspecialchars($_POST['message'])));
		$_POST = [];
	
	} else $chaterror = '入力フォームが空欄になっています。';
}


$chat = $ride->getChat();
forEach ($chat as $rideMessage){
	$rideMessage = new RideMessage($rideMessage['id']);
	// First display messages which are not children messages
	if (!$rideMessage->parent) {
		// Displays chat-line in admin color if ride author has admin rights
		if ($rideMessage->author == $ride->getAuthor()) {
			echo '<div class="chat-line chat-line-admin">';
		} else {
			echo '<div class="chat-line">';
		}
			?><a href="/rider/<?= $rideMessage->id ?>"><?php $rideMessage->author->getPropicElement(); ?></a><?php
			echo '<div class="chat-message-block">';
				echo '<div class="chat-login">' . $rideMessage->author->login . '</div>';
				echo ' - <div class="chat-time">' . $rideMessage->time . '</div>';
				echo '<div class="chat-message">' . $rideMessage->message . '</div>';
			echo '</div>';
		echo '</div>';
	} else {
		if ($rideMessage->author == $ride->getAuthor()) {
			echo '<div class="chat-line chat-line-admin child">';
		} else {
			echo '<div class="chat-line child">';
		}
			?><a href="/rider/<?= $rideMessage->id ?>"><?php $rideMessage->author->getPropicElement(); ?></a><?php
			echo '<div class="chat-message-block">';
				echo '<div class="chat-login">' . $rideMessage->login . '</div>';
				echo ' - <div class="chat-time">' . $rideMessage->time . '</div>';
				echo '<div class="chat-message">' . $rideMessage->message . '</div>';
			echo '</div>';
		echo '</div>';
	}
}

// If user is connected, display input box
if (isset($_SESSION['auth'])) { ?>
	<form method="post" class="chat-msgbox form-floating">
		<textarea class="form-control" name="message"></textarea>
		<label class="form-label" for="floatingInput">コメントを書く...</label>
		<button type="submit" class="btn button btn button-primary" name="send">送信</button>
	</form> <?php
} ?>