<!DOCTYPE html>
<html lang="en">

<?php 
session_start();
include '../actions/users/securityAction.php';

// Clear session form data if ride already posted
if (isset($_SESSION['forms']['created'])) {
	unset($_SESSION['forms'][1]);
	unset($_SESSION['forms'][2]);
	unset($_SESSION['course']);
	$successmessage = "ライドページが作成されました！「非公開」に設定されているので、募集を開始する際にはライドページからプライバシー設定を変更しましょう。";
	unset($_SESSION['forms']['created']);
} ?>

<link rel="stylesheet" href="/assets/css/rides.css">

<body> <?php
	
	include '../includes/navbar.php'; ?>

	<div class="main"> <?php

		// Space for general error messages
		displayMessage(); ?>
		
		<h2 class="top-title">My rides</h2>

		<div class="container end">
			<div class="my-rd-th justify">
				<div class="my-rd-td table-element e10 justify-center">
					作成日
				</div>
				<div class="my-rd-td table-element e10 justify-center">
					出発日
				</div>
				<div class="my-rd-td table-element e30">
					タイトル
				</div>
				<div class="my-rd-td table-element e15 justify-center">
					募集状況
				</div>
				<div class="my-rd-td table-element e10 justify-center">
					参加状況
				</div>
				<div class="my-rd-td table-element e10">
				</div>
			</div>
		
			<!-- Displays all my rides within a t-row with necessary infos data -->
			
			<?php 
			$rides = $connected_user->getRides();
			if (!empty($rides)) {
				forEach ($rides as $ride) {
					$ride = new Ride ($ride['id']); ?> 
				
					<div class="my-rd-tr justify">
						<div class="my-rd-td table-element e10 bg-white">
							<div class="my-rd-responsive-label">作成日 : </div><?= $ride->posting_date ?>
						</div>
						<div class="my-rd-td table-element e10 bg-white">
						<div class="my-rd-responsive-label">出発日 : </div><?= $ride->date ?>
						</div>
						<div class="my-rd-td table-element e30 bg-grey">
							<?= $ride->name ?>
						</div>
						<?php // Set text color depending on the status
						$status_color = $ride->getStatusColor(); ?>
						<div class="my-rd-td table-element e15 text-center" style="background-color: <?= $status_color ?>;">
							<?= $ride->status;
							// Only add substatus if there is one
							if (!empty($ride->substatus)) { echo ' (' .$ride->substatus. ')'; } ?>
						</div>
						<?php $participation = $ride->setParticipationInfos() ?>
						<div class="my-rd-td table-element e10 bg-white my-rd-participants-number">
							<?= '<div><span style="color:' .$participation['participation_color']. '">' .$participation['participants_number']. '</span>&nbsp;/&nbsp;' .$ride->nb_riders_max. '</div> (min. ' .$ride->nb_riders_min. ')'; ?>
						</div>
						<div class="my-rd-td table-element e10 my-rd-button" style="padding-top: 0px; padding-bottom: 0px;">
							<a href="/ride/<?= $ride->id ?>">
								<button class="btn button" type="button">確認</button>
							</a>
						</div>
					</div> <?php
				}

			} else {
				$noride = 'あなたが管理しているライドがありません。';
				if (isset($noride)) echo '<div class="error-block fullwidth text-center"><p class="error-message">' .$noride. '</p></div>';
			} ?>
		</div>
	</div>
	
</body>
</html>
