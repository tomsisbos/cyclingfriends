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
					<a class="nav-link interactive" href="/world"><div class="mainitem">サイクリングマップ</div><div class="subitem">World</div></a>
					<a class="nav-link dropdown-toggle dropdown-toggle-split" href="#" data-bs-toggle="dropdown"></a>
					<div class="dropdown-menu">
						<a class="dropdown-item interactive" href="/favorites/sceneries"><div class="mainitem">お気に入り絶景スポット</div><div class="subitem">My favorite sceneries</div></a>
						<a class="dropdown-item interactive" href="/favorites/segments"><div class="mainitem">お気に入りセグメント</div><div class="subitem">My favorite segments</div></a>
					</div>
				</li>
				<li class="nav-item dropdown">
					<a class="nav-link nav-dropdown-link interactive" href="/activities"><div class="mainitem">アクティビティ</div><div class="subitem">Activities</div></a>
					<a class="nav-link dropdown-toggle dropdown-toggle-split" href="#" data-bs-toggle="dropdown"></a>
					<div class="dropdown-menu">
						<a class="dropdown-item interactive" href="/activity/new"><div class="mainitem">新規作成</div><div class="subitem">New</div></a>
						<a class="dropdown-item interactive" href="/<?= $connected_user->login ?>/activities"><div class="mainitem">マイアクティビティ</div><div class="subitem">My activities</div></a>
					</div>
				</li>
				<li class="nav-item dropdown">
					<a class="nav-link nav-dropdown-link interactive" href="/<?= $connected_user->login ?>/routes"><div class="mainitem">ルート</div><div class="subitem">Routes</div></a>
					<a class="nav-link dropdown-toggle dropdown-toggle-split" href="#" data-bs-toggle="dropdown"></a>
					<div class="dropdown-menu">
						<a class="dropdown-item interactive" href="/route/new"><div class="mainitem">新規作成</div><div class="subitem">New</div></a>
					</div>
				</li>
				<li class="nav-item dropdown">
					<a class="nav-link nav-dropdown-link interactive" href="/rides"><div class="mainitem">ライド</div><div class="subitem">Rides</div></a>
					<a class="nav-link dropdown-toggle dropdown-toggle-split" href="#" data-bs-toggle="dropdown"></a>
					<div class="dropdown-menu">
						<a class="dropdown-item interactive" href="/ride/new"><div class="mainitem">新規開催</div><div class="subitem">New</div></a>
						<a class="dropdown-item interactive" href="<?= $router->generate('ride-organizations') ?>"><div class="mainitem">主催一覧</div><div class="subitem">My organizations</div></a>
						<a class="dropdown-item interactive" href="<?= $router->generate('ride-participations') ?>"><div class="mainitem">参加一覧</div><div class="subitem">My participations</div></a>
					</div>
				</li>
				<li class="nav-item dropdown">
					<a class="nav-link nav-dropdown-link interactive" href="/neighbours"><div class="mainitem">コミュニティ</div><div class="subitem">Community</div></a>
					<a class="nav-link dropdown-toggle dropdown-toggle-split" href="#" data-bs-toggle="dropdown"></a>
					<div class="dropdown-menu"> <?php
						if ($connected_user->hasAdministratorRights()) { ?>
							<a class="dropdown-item bg-admin interactive" href="/community"><div class="mainitem">ユーザー一覧</div><div class="subitem">Users list</div></a> <?php
						} ?>
						<a class="dropdown-item bg-admin interactive" href="/dev/board"><div class="mainitem">開発ボード</div><div class="subitem">Test reports</div></a>
						<a class="dropdown-item interactive" href="/news"><div class="mainitem">ニュース</div><div class="subitem">News</div></a>
						<a class="dropdown-item interactive" href="/friends"><div class="mainitem">お友達</div><div class="subitem">Friends</div></a>
						<a class="dropdown-item interactive" href="/scouts"><div class="mainitem">スカウト</div><div class="subitem">Scouts</div></a>
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
					<a class="dropdown-item interactive" href="/rider/<?= $_SESSION['id'] ?>"><div class="mainitem">プロファイル</div><div class="subitem">My profile</div></a>
					<a class="dropdown-item interactive" href="/settings"><div class="mainitem">設定</div><div class="subitem">Settings</div></a>
					<a class="dropdown-item interactive" href="/manual"><div class="mainitem">マニュアル</div><div class="subitem">Manual</div></a> <?php
					// If the user is connected, displays the sign out button 
					if (isset($_SESSION['auth'])) { ?>
					<hr class="dropdown-divider">
						<a class="dropdown-item interactive" href="/signout"><div class="mainitem">サインアウト</div><div class="subitem">Sign out</div></a> <?php
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