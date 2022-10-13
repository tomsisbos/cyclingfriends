<!DOCTYPE html>
<html lang="en">

<?php 
session_start();
include '../includes/head.php';
include '../actions/users/securityAction.php';
?>

<body>

	<?php include '../includes/navbar.php';	?>
	
	<?php // Space for general error messages
		if(isset($errormessage)){ echo '<div class="error-block" style="margin: 0px;"><p class="error-message">' .$errormessage. '</p></div>'; }
		if(isset($successmessage)){ echo '<div class="success-block" style="margin: 0px;"><p class="success-message">' .$successmessage. '</p></div>'; }	
	?>
		
	<div class="mbx-main container p-0">
		<div class="mbx col-4">
			<div class="mbx-header">
				<h1 class="top-title m-auto">Mailbox</h1>
			</div>		
			<form class="mbx-search form-floating" method="POST" name="mbx-search" id="searchForm">
				<input type="search" name="mbx-query" class="mbx-searchbox" id="searchQuery" placeholder="Search for a friend..." />
				<div class="input-group-prepend">
					<button type="submit" class="mbx-search-button">@</button>
				</div>
			</form>
			<div class="mbx-inner"> 				
				<div class="mbx-queryresult">
					<?php 
					$friendslist = getFriendsList($_SESSION['id']);					
					foreach($friendslist as $i => $id){
						$chatfriends[$i]['id']    = $id;
						$chatfriends[$i]['login'] = getLoginById($id);
					}
					if(isset($chatfriends)){
						for($i = 0; $i < count($chatfriends); $i++){
							$log = getConversation($_SESSION['id'], $chatfriends[$i]['id']); ?>
							<li id="mbx-queryresult-<?= $chatfriends[$i]['id']; ?>"> <?php
								displaysProfilePictureIcon($chatfriends[$i]['id']); ?>
								<div class="mbx-queryresult-column">
									<div class="mbx-queryresult-login"><?= $chatfriends[$i]['login']; ?></div>
									<?php if(!empty($log)){ ?>
										<div class="mbx-queryresult-time">
											<?= $log[count($log) - 1]['time']; ?>
										</div>
										<div class="mbx-queryresult-msg">
											<?= truncate($log[count($log) - 1]['message'], 0, 50); ?>
										</div> <?php
									} ?>
								</div>
							</li><?php
						}
					}else{
						echo '<div class="error-block" style="margin: 0px;"><p class="error-message">No friend has been found.</p></div>';
					} ?>
				</div>
			</div>
		</div>
	
		<div class="chat col-8">
			<div class="chat-header">
				<div class="round-propic-container">
					<img class="round-propic-img" style="display: none" src="" />
				</div>
				<h2><span id="login"></span></h2>
			</div>
			<div class="chat-inner" style="background-color: #f9f9f9;"></div>
			<div class="chat-footer">
				<form method="post" id="inputForm" action="/users/mailbox/api.php" class="chat-input">
					<textarea placeholder="Write a message..." class="form-control" id="inputMessage" name="message"></textarea>
					<button type="submit" class="btn button button-primary" name="send">Send</button>
				</form>
			</div>
		</div>
	</div>
</body>

<script src="mailbox/mailbox.js"></script>