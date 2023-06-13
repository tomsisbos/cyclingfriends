<!--Displays the navbar-->
<nav class="main-navbar navbar navbar-expand-lg navbar-light bg-white"> <?php

	if (isset($_SESSION['auth'])) $default_url = '/dashboard';
	else $default_url = '/' ?>
	
		<div class="navbar-brand" >
			<a href="<?= $default_url ?>">
				<img class="site-logo" src="/media/cf.png">
			</a>
			<div class="navbar-brand position-absolute pe-none">cyclingfriends</div>
		</div> <?php

	// If the user is connected, displays the links 
	if (isset($_SESSION['auth'])) { ?>
		<button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#collapsedMenu">
			<span class="navbar-toggler-icon"></span>
		</button>
		<div class="collapse navbar-collapse" id="collapsedMenu">
			<ul class="navbar-nav cf-navbar">
				<li class="nav-item dropdown">
					<a class="nav-link interactive" href="/world"><div class="mainitem">サイクリングマップ</div></a>
					<a class="nav-link dropdown-toggle dropdown-toggle-split" href="#" data-bs-toggle="dropdown"></a>
					<div class="dropdown-menu">
						<a class="dropdown-item interactive" href="/favorites/sceneries"><div class="mainitem">お気に入り絶景スポット</div></a>
						<a class="dropdown-item interactive" href="/favorites/segments"><div class="mainitem">お気に入りセグメント</div></a>
					</div>
				</li>
				<li class="nav-item dropdown">
					<a class="nav-link nav-dropdown-link interactive" href="/activities"><div class="mainitem">アクティビティ</div></a>
					<a class="nav-link dropdown-toggle dropdown-toggle-split" href="#" data-bs-toggle="dropdown"></a>
					<div class="dropdown-menu">
						<a class="dropdown-item interactive" href="/activity/new"><div class="mainitem">新規作成</div></a>
						<a class="dropdown-item interactive" href="/journal/<?= $connected_user->id ?>"><div class="mainitem">活動日記</div></a>
						<a class="dropdown-item interactive" href="/<?= $connected_user->login ?>/activities"><div class="mainitem">マイアクティビティ</div></a>
					</div>
				</li>
				<li class="nav-item dropdown">
					<a class="nav-link nav-dropdown-link interactive" href="/<?= $connected_user->login ?>/routes"><div class="mainitem">ルート</div></a>
					<a class="nav-link dropdown-toggle dropdown-toggle-split" href="#" data-bs-toggle="dropdown"></a>
					<div class="dropdown-menu">
						<a class="dropdown-item interactive" href="/route/new"><div class="mainitem">新規作成</div></a>
					</div>
				</li>
				<li class="nav-item dropdown">
					<a class="nav-link nav-dropdown-link interactive" href="/rides"><div class="mainitem">ライド</div></a>
					<a class="nav-link dropdown-toggle dropdown-toggle-split" href="#" data-bs-toggle="dropdown"></a>
					<div class="dropdown-menu">
						<a class="dropdown-item interactive" href="/ride/new"><div class="mainitem">新規開催</div></a>
						<a class="dropdown-item interactive" href="<?= $router->generate('ride-organizations') ?>"><div class="mainitem">主催一覧</div></a>
						<a class="dropdown-item interactive" href="<?= $router->generate('ride-participations') ?>"><div class="mainitem">参加一覧</div></a>
					</div>
				</li>
				<li class="nav-item dropdown">
					<a class="nav-link nav-dropdown-link interactive" href="/neighbours"><div class="mainitem">コミュニティ</div></a>
					<a class="nav-link dropdown-toggle dropdown-toggle-split" href="#" data-bs-toggle="dropdown"></a>
					<div class="dropdown-menu"> <?php
						if ($connected_user->hasAdministratorRights()) { ?>
							<a class="dropdown-item bg-admin interactive" href="/community"><div class="mainitem">ユーザー一覧</div></a> <?php
						} ?>
						<a class="dropdown-item bg-admin interactive" href="/dev/board"><div class="mainitem">開発ボード</div></a>
						<a class="dropdown-item interactive" href="/news"><div class="mainitem">ニュース</div></a>
						<a class="dropdown-item interactive" href="/friends"><div class="mainitem">お友達</div></a>
						<a class="dropdown-item interactive" href="/scouts"><div class="mainitem">スカウト</div></a>
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
					<a class="dropdown-item interactive" href="/rider/<?= $_SESSION['id'] ?>"><div class="mainitem">プロフィール</div></a>
					<a class="dropdown-item interactive" href="/settings"><div class="mainitem">設定</div></a>
					<a class="dropdown-item interactive" href="/manual"><div class="mainitem">マニュアル</div></a> <?php
					// If the user is connected, displays the sign out button 
					if (isset($_SESSION['auth'])) { ?>
						<hr class="dropdown-divider">
						<a class="dropdown-item interactive" href="<?= $_SERVER['REQUEST_URI'] ?>/signout">
							<div class="mainitem">サインアウト</div>
						</a> <?php
					} ?>
				</div>
			</div>
		</div> <?php

	// If the user is not connected, display default navbar
	} else { ?>

		<button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#collapsedMenu">
			<span class="navbar-toggler-icon"></span>
		</button>
		<div class="collapse navbar-collapse" id="collapsedMenu">
			<ul class="navbar-nav cf-navbar">
				<li class="nav-item dropdown">
					<a class="nav-link nav-dropdown-link interactive" href="/company"><div class="mainitem">会社について</div></a><a class="nav-link dropdown-toggle dropdown-toggle-split" href="#" data-bs-toggle="dropdown"></a>
					<div class="dropdown-menu">
						<a class="dropdown-item interactive" href="/company/business"><div class="mainitem">事業構想</div></a>
						<a class="dropdown-item interactive" href="/news"><div class="mainitem">ニュース</div></a>
						<a class="dropdown-item interactive" href="/company/contact"><div class="mainitem">お問い合わせ</div></a>
					</div>
				</li>
				<li class="nav-item dropdown">
					<a class="nav-link nav-dropdown-link interactive" href="/manual"><div class="mainitem">マニュアル</div></a>
					<a class="nav-link dropdown-toggle dropdown-toggle-split" href="#" data-bs-toggle="dropdown"></a>
					<div class="dropdown-menu"> <?php
						foreach (Manual::$chapters as $slug => $chapter) { ?>
							<a class="dropdown-item interactive" href="/manual/<?= $slug ?>"><div class="mainitem"><?= $chapter['title'] ?></div></a> <?php
						} ?>
					</div>
				</li>
				<li class="nav-item dropdown">
					<a class="nav-link interactive" href="/rides/calendar"><div class="mainitem">ツアーカレンダー</div></a>
				</li>
			</ul>
		</div> <?php
	}
	
	$url = 'http://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI']; ?>

	<div class="header-buttons push"> <?php
		// If the user is not connected and is on the signin page, displays the sign up button 
		if (!isset($_SESSION['auth']) AND (strpos($url,'signin') == true)) { ?>
			<a href="/signup">
				<button class="btn button" name="validate">新規登録</button>
			</a> <?php

		// Else, displays the sign in button		
		} else if (!isset($_SESSION['auth'])) {
			if (session_status() == PHP_SESSION_ACTIVE && $_SERVER['REQUEST_URI'] != '/') { ?>
				<a href="<?= $_SERVER['REQUEST_URI']?>/signin"> <?php
			} else { ?>
				<a href="/signin"> <?php
			} ?>
				<button class="btn button" name="validate">ログイン</button>
			</a> <?php
		} ?>
	</div> 
		
</nav> <?php

// Display dev note adding icon on session pages
if (isset($_SESSION['auth'])) echo '<script src="/scripts/dev/note.js"></script>';

// Request and show notifications
if (isset($_SESSION['auth'])) echo '<script type="module" src="/scripts/user/notifications.js"></script>'; ?>