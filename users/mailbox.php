<!DOCTYPE html>
<html lang="en">

<?php 
session_start();
include '../includes/head.php';
include '../actions/users/securityAction.php';
?>

<body>

	<?php include '../includes/navbar.php'; ?>

	<div class="main"> <?php
		
		// Space for general error messages
		displayMessage(); ?>
			
		<div class="mbx-main container p-0">
			<div class="mbx col-4">
				<div class="mbx-header">
					<h1 class="top-title m-auto">Mailbox</h1>
				</div>
				<div class="mbx-tabs">
					<div class="mbx-tab bg-high-friend top" id="friendsTab">
						Friends
					</div> <?php
					if (!$_SESSION['settings']['hide_on_chat']) { ?>
						<div class="mbx-tab bg-high-public" id="publicTab">
							Public
						</div> <?php 
					} ?>
				</div>			
				<form class="mbx-search form-floating" method="POST" name="mbx-search" id="searchForm">
					<input type="search" name="mbx-query" class="mbx-searchbox" id="searchQuery" placeholder="Search for a friend..." />
					<div class="input-group-prepend">
						<button type="submit" class="mbx-search-button">
							<span class="iconify-inline" data-icon="fluent:search-28-filled" style="color: white;" data-width="24" data-height="24"></span>
						</button>
					</div>
				</form>
				<div class="mbx-inner"> 				
					<div class="mbx-queryresult"> <?php
						// Display last messages from newest to oldest
						$lastmessages = $connected_user->getLastMessages($connected_user->getFriends());
						foreach ($lastmessages as $last_message) { ?>
							<li id="mbx-queryresult-<?= $last_message->friend->id ?>"> <?php
								$last_message->friend->displayPropic(); ?>
								<div class="mbx-queryresult-column">
									<div class="mbx-queryresult-login"><?= $last_message->friend->login ?></div>
									<div class="mbx-queryresult-time">
										<?= $last_message->time ?>
									</div>
									<div class="mbx-queryresult-msg">
										<?= truncate($last_message->message, 0, 50) ?>
									</div>
								</div>
							</li> <?php
						}
						if (empty($lastmessages)) { ?>
							<div class="error-block" style="margin: 0px;"><p class="error-message">No friend has been found.</p></div> <?php
						} ?>
					</div>
				</div>
			</div>
		
			<div class="chat col-8">
				<div class="chat-header">
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
	</div>
</body>

<script src="mailbox/mailbox.js"></script>