<!--Displays the navbar-->
<nav class="main-navbar navbar navbar-expand-lg navbar-light bg-white"> <?php

	if (isset($_SESSION['auth'])) $default_url = '/dashboard';
	else $default_url = '/' ?>
	
	<a href="<?= $default_url ?>">
		<p class="navbar-brand" >cycling<span class="f">f</span>riends</p>
		<img class="site-logo" src="/media/cf.png">
	</a> <?php

	// If the user is connected, displays the links 
	if (isset($_SESSION['auth'])) { ?>
		<button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#collapsedMenu">
			<span class="navbar-toggler-icon"></span>
		</button>
		<div class="collapse navbar-collapse" id="collapsedMenu">
			<ul class="navbar-nav cf-navbar">
				<li class="nav-item dropdown">
					<a class="nav-link" href="/world"><div class="mainitem">World</div><div class="subitem">サイクリングマップ</div></a>
					<a class="nav-link dropdown-toggle dropdown-toggle-split" href="#" data-bs-toggle="dropdown"></a>
					<div class="dropdown-menu">
						<a class="dropdown-item" href="/favorites/sceneries"><div class="mainitem">My favorite sceneries</div><div class="subitem">お気に入り絶景スポット</div></a>
						<a class="dropdown-item" href="/favorites/segments"><div class="mainitem">My favorite segments</div><div class="subitem">お気に入りセグメント</div></a>
					</div>
				</li>
				<li class="nav-item dropdown">
					<a class="nav-link nav-dropdown-link" href="/activities"><div class="mainitem">Activities</div><div class="subitem">アクティビティ</div></a>
					<a class="nav-link dropdown-toggle dropdown-toggle-split" href="#" data-bs-toggle="dropdown"></a>
					<div class="dropdown-menu">
						<a class="dropdown-item" href="/activity/new"><div class="mainitem">New</div><div class="subitem">新規作成</div></a>
						<a class="dropdown-item" href="/<?= $connected_user->login ?>/activities"><div class="mainitem">My activities</div><div class="subitem">マイアクティビティ</div></a>
					</div>
				</li>
				<li class="nav-item dropdown">
					<a class="nav-link nav-dropdown-link" href="/<?= $connected_user->login ?>/routes"><div class="mainitem">Routes</div><div class="subitem">ルート</div></a>
					<a class="nav-link dropdown-toggle dropdown-toggle-split" href="#" data-bs-toggle="dropdown"></a>
					<div class="dropdown-menu">
						<a class="dropdown-item" href="/route/new"><div class="mainitem">New</div><div class="subitem">新規作成</div></a>
					</div>
				</li>
				<li class="nav-item dropdown">
					<a class="nav-link nav-dropdown-link" href="/rides"><div class="mainitem">Rides</div><div class="subitem">ライド</div></a>
					<a class="nav-link dropdown-toggle dropdown-toggle-split" href="#" data-bs-toggle="dropdown"></a>
					<div class="dropdown-menu">
						<a class="dropdown-item" href="/ride/new"><div class="mainitem">New</div><div class="subitem">新規開催</div></a>
						<a class="dropdown-item" href="/<?= $connected_user->login ?>/rides"><div class="mainitem">My rides</div><div class="subitem">マイライド</div></a>
					</div>
				</li>
				<li class="nav-item dropdown">
					<a class="nav-link nav-dropdown-link" href="/neighbours"><div class="mainitem">Community</div><div class="subitem">コミュニティ</div></a>
					<a class="nav-link dropdown-toggle dropdown-toggle-split" href="#" data-bs-toggle="dropdown"></a>
					<div class="dropdown-menu"> <?php
						if ($connected_user->hasAdministratorRights()) { ?>
							<a class="dropdown-item bg-admin" href="/community"><div class="mainitem">Users list</div><div class="subitem">ユーザー一覧</div></a> <?php
						} ?>
						<a class="dropdown-item bg-admin" href="/dev/board"><div class="mainitem">Test reports</div><div class="subitem">開発ボード</div></a>
						<a class="dropdown-item" href="/news"><div class="mainitem">News</div><div class="subitem">ニュース</div></a>
						<a class="dropdown-item" href="/friends"><div class="mainitem">Friends</div><div class="subitem">お友達</div></a>
						<a class="dropdown-item" href="/scouts"><div class="mainitem">Scouts</div><div class="subitem">スカウト</div></a>
					</div>
				</li>
			</ul>
		</div>
		
		<!-- Profile picture icon -->
		<div class="nav-item d-flex align-items-center gap">
			<div>
				<a href="/rider/<?= $_SESSION['id']; ?>">
					<?php $connected_user->getPropicElement(60, 60, 60); ?>
				</a>
				<div id="notificationsContainer"></div>
			</div>
			<!-- Profile chevron dropdown -->
			<div class="dropdown">
				<a class="nav-link" href="#" data-bs-toggle="dropdown">
					<span class="iconify" style="color: black;" data-icon="charm:chevron-down" data-width="30" data-height="30"></span>
				</a>
				<div class="dropdown-menu dropdown-menu-end" id="profileDropdownMenuLink">
					<a class="dropdown-item" href="/rider/<?= $_SESSION['id'] ?>"><div class="mainitem">My profile</div><div class="subitem">プロファイル</div></a>
					<a class="dropdown-item" href="/settings"><div class="mainitem">Settings</div><div class="subitem">設定</div></a>
					<a class="dropdown-item" href="/manual"><div class="mainitem">Manual</div><div class="subitem">マニュアル</div></a> <?php
					// If the user is connected, displays the sign out button 
					if (isset($_SESSION['auth'])) { ?>
					<hr class="dropdown-divider">
						<a class="dropdown-item" href="/signout"><div class="mainitem">Sign out</div><div class="subitem">サインアウト</div></a> <?php
					} ?>
				</div>
			</div>
		</div> <?php
	} 
	
	$url = 'http://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI']; ?>

	<div class="header-buttons push"> <?php
		// If the user is not connected and is on the signin page, displays the sign up button 
		if (!isset($_SESSION['auth']) AND (strpos($url,'signin') == true)) { ?>
			<a href="/signup">
				<button class="btn button" name="validate" disabled>Sign up</button>
			</a> <?php

		// Else, displays the sign in button		
		} else if (!isset($_SESSION['auth'])) { ?>
			<a href="/signin">
				<button class="btn button" name="validate">Sign in</button>
			</a> <?php
		} ?>
	</div> 
		
</nav> <?php

// Display dev note adding icon on session pages
if (isset($_SESSION['auth'])) echo '<script src="/scripts/dev/note.js"></script>';

// Request and show notifications
if (isset($_SESSION['auth'])) echo '<script type="module" src="/scripts/user/notifications.js"></script>'; ?>