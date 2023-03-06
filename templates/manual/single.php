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

            <div class="m-single"> <?php

                // Get chapter content
                $chapter_file = '../includes/manual/' .Manual::currentChapter(). '.php';

                // Title & intro
                Manual::chapterTitle(Manual::currentChapter());

                if (file_exists($chapter_file)) {

                    include $chapter_file;

                    Manual::intro($intro);

                    // Sections
                    foreach ($content as $section) { ?>
                        <div class="m-section"> <?php
                        if (isset($section['id'])) Manual::title(2, $section['title'], $section['id']);
                        else Manual::title(2, $section['title']);
                        if (isset($section['path'])) Manual::path($section['path']);
                        if (isset($section['text'])) Manual::intro($section['text']);

                        // Parts
                        if (isset($section['content'])) foreach ($section['content'] as $part) {?>
                            <div class="m-part"> <?php
                            if (isset($part['id'])) Manual::title(3, $part['title'], $part['id']);
                            else Manual::title(3, $part['title']);
                            if (isset($part['text'])) Manual::text($part['text']);

                            // Fractions
                            if (isset($part['content'])) foreach ($part['content'] as $fraction) {?>
                                <div class="m-fraction"> <?php
                                if (isset($fraction['id'])) Manual::title(4, $fraction['title'], $fraction['id']);
                                else Manual::title(4, $fraction['title']);
                                if (isset($fraction['text'])) Manual::text($fraction['text']);

                                // Subfractions
                                if (isset($fraction['content'])) foreach ($fraction['content'] as $subfraction) {?>
                                    <div class="m-subfraction"> <?php
                                    if (isset($subfraction['id'])) Manual::title(5, $subfraction['title'], $subfraction['id']);
                                    else Manual::title(5, $subfraction['title']);
                                    if (isset($subfraction['text'])) Manual::text($subfraction['text']);

                                    // Microfractions
                                    if (isset($subfraction['content'])) foreach ($subfraction['content'] as $microfraction) {?>
                                        <div class="m-microfraction"> <?php
                                        if (isset($microfraction['id'])) Manual::title(6, $microfraction['title'], $microfraction['id']);
                                        else Manual::title(5, $microfraction['title']);
                                        if (isset($microfraction['text'])) Manual::text($microfraction['text']); ?>
                                        </div> <?php
                                    } ?>
                                    </div> <?php
                                } ?>
                                </div> <?php
                            } ?>
                            </div> <?php
                        } ?>
                        </div> <?php
                    }
                } else include '../includes/manual/404.php'; 
            
            ?>
            </div>
		</div>
	
	</div>
	
</body>
</html>

<script src="/scripts/manual/single.js"></script>