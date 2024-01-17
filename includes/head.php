<head prefix="og: http://ogp.me/ns#" >

	<title>CyclingFriends</title>
  	<link rel="icon" type="image/x-icon" href="/media/cf.png">

	<!-- CSS -->
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-Zenh87qX5JnK2Jl0vWa8Ck2rdkQ2Bzep5IDxbcnCeuOxjzrPF/et3URy9Bv1WTRi" crossorigin="anonymous">
	<link rel="stylesheet" href="/assets/css/style.css" />
	<link rel="stylesheet" href="/assets/css/themes.css" />
	<link rel="stylesheet" href="/assets/css/footer.css" />
	<link rel="stylesheet" href="/assets/css/loaders.css" />
	<link rel="stylesheet" href="/assets/css/global-map.css" />
	<link rel="stylesheet" href="/assets/css/sidebars.css">
	<link rel="stylesheet" href="/assets/css/riders.css" />
	<link rel="stylesheet" href="/assets/css/routes.css" />
	<link rel="stylesheet" href="/assets/css/mailbox.css" />
	<link rel="stylesheet" href="/assets/css/map.css" />
	<link rel="stylesheet" href="/assets/css/tooltip.css" />
	<link rel='stylesheet' href='https://api.mapbox.com/mapbox-gl-js/v2.10.0/mapbox-gl.css'/>

	<meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1.0"> <?php
	
	// OGP meta tags
	$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
	$current_url = $protocol . "://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
	echo '<meta property="og:url" content="' .$current_url. '" />';
	echo '<meta property="og:type" content="website" />';
	echo '<meta property="og:site_name" content="CyclingFriends" />';
	echo '<meta name="twitter:card" content="summary_large_image" />';
	
	if (isset($ogp)) {
		if (isset($ogp['title'])) echo '<meta property="og:title" content="' .$ogp['title']. '" />';
		if (isset($ogp['description'])) echo '<meta property="og:description" content="' .truncateString($ogp['description'], 200). '" />';
		if (isset($ogp['image'])) echo '<meta property="og:image" content="' .$ogp['image']. '" />';
		if (isset($ogp['twitter_site'])) echo '<meta name="twitter:site" content="@' .$ogp['twitter_site']. '" />';
	} ?>
	
</head>

<!-- php -->
<?php
	require_once 'functions.php';
	require_once '../actions/treatCookieMessages.php'; ?>

<!-- js -->
<script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
<script defer src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
<script defer src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
<script defer src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-OERcA2EqjJCMA+/3y+gxIOqMEjwtxJY7qPCqsdltbNJuaOe923+mo//f6V8Qbsw3" crossorigin="anonymous"></script>
<script defer src="https://code.iconify.design/2/2.1.1/iconify.min.js"></script>
<script src="/assets/js/functions.js"></script>
<script defer src='/node_modules/togpx/togpx.js'></script>
<script defer async src="https://www.googletagmanager.com/gtag/js?id=G-LHWZVJYEBR"></script>
<script defer src="/assets/js/google-analytics.js"></script>