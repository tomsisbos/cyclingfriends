<?php

include '../includes/head.php';
include '../actions/database.php';
include '../actions/users/initAdminSession.php'; ?>

<!DOCTYPE html>
<html lang="en"> 

    <body> <?php

        include '../includes/navbar.php'; ?>

        <div class="main">

            <div class="container bg-admin">
            
                <h3 class="mb-2">同期済みファイル</h3> <?php

				// Define offset and number of articles to query
				$limit = 20;
				if (isset($_GET['p'])) $offset = ($_GET['p'] - 1) * $limit;
				else $offset = 0;
                $query = "SELECT id FROM activity_files ORDER BY id DESC";
                $getGarminActivities = $db->prepare($query. " LIMIT {$limit} OFFSET {$offset}");
                $getGarminActivities->execute();
                $activity_file_ids = $getGarminActivities->fetchAll(PDO::FETCH_COLUMN);
                $activity_files = array_map(function ($id) {
                    return new ActivityFile($id);
                }, $activity_file_ids);

                foreach ($activity_files as $activity_file) {
                    $activity = new Activity($activity_file->activity_id) ?>
                    <div class="d-flex gap bg-white mb-1 p-1 align-items-center px-4">
                        <div><?= $activity_file->id ?></div> <?php
                        $user = new User($activity_file->user_id);
                        echo '<a href="/rider/' .$user->id. '">';
                        $user->getPropicElement();
                        echo '</a>'; ?>
                        <div class="d-flex flex-column">
                            <div><?= $activity_file->filename ?></div>
                            <div class="<?php 
                                if ($activity_file->latest_error !== null) echo ' text-red';
                                else if ($activity_file->activity_id !== null && $activity->hasAccess(getConnectedUser())) echo ' text-green';
                                else echo ' text-black';
                            ?>"><?php
                            if ($activity->hasAccess(getConnectedUser())) echo '<a href="/activity/' .$activity_file->activity_id. '" target="_blank" style="color: inherit">' .$activity->title. '</a>';
                            else echo $activity->title; ?>
                            </div>
                        </div> <?php
                        if ($activity_file->latest_error) echo '<span class="text-red">' .$activity_file->latest_error. '</span>'; ?>
                        <div class="push"><?= $activity_file->posting_date ?></div>
                    </div> <?php
                }
			
                // Set pagination system
                if (isset($_GET['p'])) $p = $_GET['p'];
                else $p = 1;
                $url = strtok($_SERVER["REQUEST_URI"], '?');
                $getTotalPages = $db->prepare($query);
                $getTotalPages->execute();
                $total_pages = ceil($getTotalPages->rowCount() / $limit);
                
                // Build pagination menu
                include '../includes/pagination.php' ?>

            </div>

        </div>

    </body>

</html>