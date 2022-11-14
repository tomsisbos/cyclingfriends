<!--Displays the navbar-->
<nav class="navbar navbar-expand-lg navbar-light bg-white">

	<a href="/dashboard.php">
		<p class="navbar-brand" >Cyclingfriends</p>
		<img class="site-logo" src="/includes/media/cf.png">
	</a> <?php

	// If the user is connected, displays the links 
	if (isset($_SESSION['auth'])) { ?>
		<button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#collapsedMenu">
			<span class="navbar-toggler-icon"></span>
		</button>
		<div class="collapse navbar-collapse" id="collapsedMenu">
			<ul class="navbar-nav cf-navbar">
				<li class="nav-item dropdown">
					<a class="nav-link" href="/map.php">Map</a>
				</li>
				<li class="nav-item dropdown">
					<a class="nav-link nav-dropdown-link" href="/activities.php">Activities</a>
					<a class="nav-link dropdown-toggle dropdown-toggle-split" href="#" data-bs-toggle="dropdown"></a>
					<div class="dropdown-menu">
						<a class="dropdown-item" href="/activities/myactivities.php">My Activities</a>
						<a class="dropdown-item" href="/activities/new.php">New</a>
					</div>
				</li>
				<li class="nav-item dropdown">
					<a class="nav-link nav-dropdown-link" href="/routes.php">Routes</a>
					<a class="nav-link dropdown-toggle dropdown-toggle-split" href="#" data-bs-toggle="dropdown"></a>
					<div class="dropdown-menu">
						<a class="dropdown-item" href="/routes/new.php">Build</a>
					</div>
				</li>
				<li class="nav-item dropdown">
					<a class="nav-link nav-dropdown-link" href="/rides.php">Rides</a>
					<a class="nav-link dropdown-toggle dropdown-toggle-split" href="#" data-bs-toggle="dropdown"></a>
					<div class="dropdown-menu">
						<a class="dropdown-item" href="/rides/new.php">Organize</a>
						<a class="dropdown-item" href="/rides/myrides.php">My Rides</a>
					</div>
				</li>
				<li class="nav-item dropdown">
					<a class="nav-link nav-dropdown-link" href="/community.php">Community</a>
					<a class="nav-link dropdown-toggle dropdown-toggle-split" href="#" data-bs-toggle="dropdown"></a>
					<div class="dropdown-menu">
						<a class="dropdown-item" href="/riders/neighbours.php">Neighbours</a>
						<a class="dropdown-item" href="/riders/friends.php">Friends</a>
					</div>
				</li>
			</ul>
		</div>
		
		<!-- Profile picture icon -->
		<div class="nav-item d-flex align-items-center gap">
			<a href="/riders/profile.php?id=<?= $_SESSION['id']; ?>">
				<?php $connected_user->displayPropic(60, 60, 60); ?>
			</a>
			<!-- Profile chevron dropdown -->
			<div class="dropdown">
				<a class="nav-link" href="#" data-bs-toggle="dropdown">
					<span class="iconify" style="color: black;" data-icon="charm:chevron-down" data-width="30" data-height="30"></span>
				</a>
				<div class="dropdown-menu dropdown-menu-end" id="profileDropdownMenuLink">
					<a class="dropdown-item" href="/riders/profile.php?id=<?= $_SESSION['id']; ?>">My profile</a>
					<a class="dropdown-item" href="/users/mailbox.php">Mailbox</a>
					<a class="dropdown-item" href="/users/settings.php">Settings</a> <?php
					// If the user is connected, displays the sign out button 
					if (isset($_SESSION['auth'])) { ?>
					<hr class="dropdown-divider">
						<a class="dropdown-item" href="/actions/users/signoutAction.php">Sign out</a> <?php
					} ?>
				</div>
			</div>
		</div> <?php
	} 
	
	$url = 'http://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
	
	// If the user is connected and is on the signin page, displays the sign up button 
	if (!isset($_SESSION['auth']) AND (strpos($url,'signin') == true)) { ?>
		<a href="/signup.php">
			<button class="btn button" name="validate">Sign up</button>
		</a> <?php

	// Else, displays the sign in button 
	} else if (!isset($_SESSION['auth'])) { ?>
		<a href="/signin.php">
			<button class="btn button" name="validate">Sign in</button>
		</a> <?php
	} ?>
		
</nav>


