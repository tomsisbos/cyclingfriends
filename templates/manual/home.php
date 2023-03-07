<?php

include '../actions/users/initPublicSessionAction.php';
include '../includes/head.php'; ?>

<!DOCTYPE html>
<html lang="en">
    
<link rel="stylesheet" href="/assets/css/manual.css" />

<body>

	<?php include '../includes/navbar.php'; ?>

	<div class="main"> <?php
	
		// Space for error messages
		displayMessage(); ?>
		
		<div class="container p-0 manual end">
		
			<div class="m-sidebar"> <?php

				Manual::summary(); ?>

			</div>

			<div class="m-single">
			
				<h1>ユーザーマニュアル</h1>
				<div class="m-subtitle">User manual</div>

				<div class="m-intro">
					<p>このセクションでは、CyclingFriendsがどんな仕組みによって動いているのかについて、細かく記載させて頂いております。</p>
					<p>左側のサイドバーからアクセスできる様々なチャプターに構成されています。</p>
				</div>

			</div>
		</div>
	</div>
	
</body>
</html>