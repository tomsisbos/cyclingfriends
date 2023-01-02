<!--Displays the navbar-->
<nav class="navbar navbar-expand-lg navbar-light bg-white">

	<a href="/dashboard">
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
					<a class="nav-link" href="/world">World</a>
				</li>
				<li class="nav-item dropdown">
					<a class="nav-link nav-dropdown-link" href="/activities">Activities</a>
					<a class="nav-link dropdown-toggle dropdown-toggle-split" href="#" data-bs-toggle="dropdown"></a>
					<div class="dropdown-menu">
						<a class="dropdown-item" href="/<?= $connected_user->login ?>/activities">My Activities</a>
						<a class="dropdown-item" href="/activity/new">New</a>
					</div>
				</li>
				<li class="nav-item dropdown">
					<a class="nav-link nav-dropdown-link" href="/<?= $connected_user->login ?>/routes">Routes</a>
					<a class="nav-link dropdown-toggle dropdown-toggle-split" href="#" data-bs-toggle="dropdown"></a>
					<div class="dropdown-menu">
						<a class="dropdown-item" href="/route/new">Build</a>
					</div>
				</li>
				<li class="nav-item dropdown">
					<a class="nav-link nav-dropdown-link" href="/rides">Rides</a>
					<a class="nav-link dropdown-toggle dropdown-toggle-split" href="#" data-bs-toggle="dropdown"></a>
					<div class="dropdown-menu">
						<a class="dropdown-item" href="/ride/new">Organize</a>
						<a class="dropdown-item" href="/<?= $connected_user->login ?>/rides">My Rides</a>
					</div>
				</li>
				<li class="nav-item dropdown">
					<a class="nav-link nav-dropdown-link" href="/community">Community</a>
					<a class="nav-link dropdown-toggle dropdown-toggle-split" href="#" data-bs-toggle="dropdown"></a>
					<div class="dropdown-menu">
						<a class="dropdown-item" href="/neighbours">Neighbours</a>
						<a class="dropdown-item" href="/friends">Friends</a>
					</div>
				</li>
			</ul>
		</div>
		
		<!-- Profile picture icon -->
		<div class="nav-item d-flex align-items-center gap">
			<a href="/rider/<?= $_SESSION['id']; ?>">
				<?php $connected_user->displayPropic(60, 60, 60); ?>
			</a>
			<!-- Profile chevron dropdown -->
			<div class="dropdown">
				<a class="nav-link" href="#" data-bs-toggle="dropdown">
					<span class="iconify" style="color: black;" data-icon="charm:chevron-down" data-width="30" data-height="30"></span>
				</a>
				<div class="dropdown-menu dropdown-menu-end" id="profileDropdownMenuLink">
					<a class="dropdown-item" href="/rider/<?= $_SESSION['id'] ?>">My profile</a>
					<a class="dropdown-item" href="/favorites/sceneries">My favorite sceneries</a>
					<a class="dropdown-item" href="/favorites/segments">My favorite segments</a>
					<a class="dropdown-item" href="/settings">Settings</a> <?php
					// If the user is connected, displays the sign out button 
					if (isset($_SESSION['auth'])) { ?>
					<hr class="dropdown-divider">
						<a class="dropdown-item" href="/signout">Sign out</a> <?php
					} ?>
				</div>
			</div>
		</div> <?php
	} 
	
	$url = 'http://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
	
	// If the user is connected and is on the signin page, displays the sign up button 
	if (!isset($_SESSION['auth']) AND (strpos($url,'signin') == true)) { ?>
		<div class="header-buttons">
			<a href="/signup">
				<button class="btn button" name="validate">Sign up</button>
			</a>
		</div> <?php

	// Else, displays the sign in button 
	} else if (!isset($_SESSION['auth'])) { ?>
		<div class="header-buttons">
			<a href="/signin">
				<button class="btn button" name="validate">Sign in</button>
			</a>
		</div> <?php
	} ?>
		
</nav>


