<div class="rd-cards"> <?php

    if ($getRides->rowCount() > 0) {
        while ($ride = $getRides->fetch()) {

            $ride = new Ride ($ride['id']);
            
            // Only display rides accepting bike types matching connected user's registered bikes
            if (!(isset($_POST['filter_bike']) AND !$connected_user->checkIfAcceptedBikesMatches($ride))) {
            
                // Only display 'Friends only' rides if connected user is on the ride author's friends list
                if ($ride->privacy != 'Friends only' OR ($ride->privacy == 'Friends only' AND ($ride->author_id == $connected_user->id OR $ride->getAuthor()->isFriend($connected_user)))) {

                    $is_ride = true; // Set "is_ride" variable to true as long as one ride to display has been found 

                    include 'card.php';
            
                }
            }
        
        }
				
        if ($getResultsNumber->rowCount() > $limit) {
    
            // Set pagination system
            if (isset($_GET['p'])) $p = $_GET['p'];
            else $p = 1;
            $url = strtok($_SERVER["REQUEST_URI"], '?');
            $total_pages = $getResultsNumber->rowCount() / $limit;
            
            // Build pagination menu
            include '../includes/pagination.php';

        }
        
    } ?>

</div>

<script src="/scripts/rides/display-rides.js"></script>