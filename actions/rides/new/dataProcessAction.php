<?php

// Get infos and display form
switch(CFG_STAGE_ID)
{
    case 3:
        // Get infos form previous form
        if (!empty($_POST) and !isset($_POST['validate'])) { 
            if ($_POST['method'] == 'pick') {
                $_SESSION['forms'][CFG_STAGE_ID-1] = array(
                    'method'             => $_SESSION['course']['method'],
                    'meetingplace'       => $_SESSION['course']['meetingplace'],
                    'distance-about'     => $_POST['distance-about'],
                    'distance'           => $_POST['distance'],
                    'finishplace'        => $_SESSION['course']['finishplace'],
                    'terrain'            => $_POST['terrain'],
                    'course-description' => $_POST['course-description'],
                    'options'            => $_SESSION['course']['options'],
                    'checkpoints'        => $_SESSION['course']['checkpoints']
                );
            } else if ($_POST['method'] == 'draw') {
                $_SESSION['forms'][CFG_STAGE_ID-1] = array(
                    'method'             => $_SESSION['course']['method'],
                    'meetingplace'       => $_SESSION['course']['meetingplace'],
                    'distance-about'     => 'precise',
                    'distance'           => $_SESSION['course']['distance'],
                    'finishplace'        => $_SESSION['course']['finishplace'],
                    'terrain'            => $_SESSION['course']['terrain'],
                    'course-description' => $_SESSION['course']['course-description'],
                    'options'            => $_SESSION['course']['options'],
                    'checkpoints'        => $_SESSION['course']['checkpoints'],
                    'route-id'           => $_SESSION['course']['route-id']
                );
            }
		}
        $ride_infos = $_SESSION['forms'][1];
		$course_infos = $_SESSION['forms'][2];
 
        // Displays summary page
        require('../includes/rides/new/summary.php');
    break;
 
    case 2:
        // Default values
        if (empty($_SESSION['forms'][CFG_STAGE_ID])) {
            $_SESSION['forms'][CFG_STAGE_ID] = array(
                'method'             => '',
                'meetingplace'       => '',
                'distance-about'     => '',
                'distance'           => '',
                'finishplace'        => '',
                'terrain'            => '',
                'course-description' => ''
			);
        }
		
        // Get infos form previous form
        if (!empty($_POST)) {	
			// Set multiple selects as empty if nothing has been selected
			if (!isset($_POST['level'])) $_POST['level'] = [0 => 'Anyone'];
			if (!isset($_POST['accepted-bikes'])) $_POST['accepted-bikes'] = [0 => 'All bikes'];
			$_SESSION['forms'][CFG_STAGE_ID-1] = array(
				'ride-name'         => $_POST['ride-name'],
				'date'              => $_POST['date'],
				'meeting-time'      => $_POST['meeting-time'],
				'departure-time'    => $_POST['departure-time'],
				'finish-time'       => $_POST['finish-time'],
				'nb-riders-min'     => $_POST['nb-riders-min'],
				'nb-riders-max'     => $_POST['nb-riders-max'],
				'level'             => $_POST['level'],
                'accepted-bikes'    => $_POST['accepted-bikes'],
				'ride-description'  => $_POST['ride-description']
			);
		}
		
		require('../includes/rides/new/course.php');
    break;
 
    case 1:
    default:
        // Default values
        if (empty($_SESSION['forms'][CFG_STAGE_ID])) {
            $_SESSION['forms'][CFG_STAGE_ID] = array(
                'ride-name'         => '',
                'date'              => '',
                'meeting-time'      => '',
                'departure-time'    => '',
                'finish-time'       => '',
                'nb-riders-min'     => '',
                'nb-riders-max'     => '',
                'level'             => array(),
                'accepted-bikes'    => array(),
                'ride-description'  => ''
            );
        }
 
        require('../includes/rides/new/infos.php');
    break;
}

?>