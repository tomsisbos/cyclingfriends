<!--Displays the navbar-->
<nav class="navbar navbar-expand-lg navbar-light bg-white">
	<div class="container-fluid">
		<a href="/dashboard.php">
			<p class="navbar-brand" >Cyclingfriends</p>
			<img class="site-logo" src="/includes/media/cf.png">
		</a>
	
		<?php		
		// If the user is connected, displays the links 
		if (isset($_SESSION['auth'])) { ?>
			<button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
				<span class="navbar-toggler-icon"></span>
			</button>
			<div class="collapse navbar-collapse" id="navbarSupportedContent">
				<ul class="navbar-nav me-auto mb-2 mb-lg-0">
					<li class="nav-item">
						<a class="nav-link" href="/dashboard.php">Dashboard</a>
					</li>
					<li class="nav-item dropdown">
						<a class="nav-link nav-dropdown-link" href="/map.php">Map</a>
						<a class="nav-link dropdown-toggle nav-dropdown-toggle" href="#" id="mapDropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"></a>
						<div class="dropdown-menu" aria-labelledby="mapDropdownMenuLink">
							<a class="dropdown-item" href="/map/routes.php">Routes</a>
							<div class="dropdown-item" aria-labelledby="ridesDropdownMenuLink">
								<a class="dropdown-item" href="/map/routes/new.php">Build</a>
							</div>
						</div>
					</li>
					<li class="nav-item dropdown">
						<a class="nav-link nav-dropdown-link" href="/activities.php">Activities</a>
						<a class="nav-link dropdown-toggle nav-dropdown-toggle" href="#" id="activitiesDropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"></a>
						<div class="dropdown-menu" aria-labelledby="activitiesDropdownMenuLink">
							<a class="dropdown-item" href="/activities/myactivities.php">My Activities</a>
							<a class="dropdown-item" href="/activities/new.php">New</a>
						</div>
					</li>
					<li class="nav-item dropdown">
						<a class="nav-link nav-dropdown-link" href="/community.php">Community</a>
						<a class="nav-link dropdown-toggle nav-dropdown-toggle" href="#" id="communityDropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"></a>
						<div class="dropdown-menu" aria-labelledby="communityDropdownMenuLink">
							<div class="nav-item dropdown">
								<a class="dropdown-item" href="/rides.php">Rides</a>
								<div class="dropdown-item" aria-labelledby="ridesDropdownMenuLink">
									<a class="dropdown-item" href="/rides/new.php">Organize</a>
									<a class="dropdown-item" href="/rides/myrides.php">My Rides</a>
								</div>
							</div>
          					<div class="dropdown-divider"></div>
							<a class="dropdown-item" href="/riders/neighbours.php">Neighbours</a>
							<a class="dropdown-item" href="/riders/friends.php">Friends</a>
						</div>
					</li>
				</ul>
			</div>
			
			<!-- Profile picture icon -->
			<div class="nav-item nav d-flex">
				<a href="/riders/profile.php?id=<?= $_SESSION['id']; ?>">
					<?php $connected_user->displayPropic(60, 60, 60); ?>
				</a>
				<!-- Profile chevron dropdown -->
				<div class="dropdown">
					<a class="nav-link" href="#" id="profileDropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><span class="iconify" style="color: black;" data-icon="charm:chevron-down" data-width="30" data-height="30"></span></a>
					<div class="dropdown-menu" aria-labelledby="profileDropdownMenuLink">
						<a class="dropdown-item" href="/riders/profile.php?id=<?= $_SESSION['id']; ?>">My profile</a>
						<a class="dropdown-item" href="/users/mailbox.php">Mailbox</a>
						<a class="dropdown-item" href="/users/settings.php">Settings</a>
					</div>
				</div>
			</div> <?php
		} 
		
		$url = 'http://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
		
		// If the user is connected, displays the sign out button 
		if (isset($_SESSION['auth'])) { ?>
			<a href="/actions/users/signoutAction.php">
				<button class="btn button" name="validate">Sign out</button>
			</a> <?php
		
		// If the user is connected and is on the signin page, displays the sign up button 
		} else if (!isset($_SESSION['auth']) AND (strpos($url,'signin') == true)) { ?>
			<a href="/signup.php">
				<button class="btn button" name="validate">Sign up</button>
			</a> <?php

		// Else, displays the sign in button 
		} else { ?>
			<a href="/signin.php">
				<button class="btn button" name="validate">Sign in</button>
			</a> <?php
		} ?>
	</div>
		
</nav>


