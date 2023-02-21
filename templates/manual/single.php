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
		
		<h1 class="top-title">User manual</h2>
		
		<!-- Upper section -->
		<div class="container"> <?php

            // Get chapter content
            $chapter_name = explode('/manual/', $_SERVER['REQUEST_URI'])[1];
            include '../includes/manual/' .$chapter_name. '.php';
			
            // Display chapter content ?>
            <div class="manual"> <?php

                Manual::title(1, $title);

                Manual::intro($intro);

                foreach ($content as $chapter) {

                    Manual::title(2, $chapter['title']);
                    if (isset($chapter['path'])) Manual::path($chapter['path']);
                    if (isset($chapter['text'])) Manual::text($chapter['text']);
                    foreach ($chapter['content'] as $section){

                        Manual::title(3, $section['title']);
                        Manual::text($section['text']);

                    }
                }
			
			?>
            </div>
		</div>
	
	</div>
	
</body>
</html>