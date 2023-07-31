<?php

// Get infos and display form
switch(CFG_STAGE_ID)
{
    case 3:
        // Get infos form previous form
        if (!empty($_POST) and !isset($_POST['validate'])) { 
            if ($_POST['method'] == 'pick') {
                $_SESSION['edit-forms'][CFG_STAGE_ID-1] = array(
                    'method'             => $_SESSION['edit-course']['method'],
                    'meetingplace'       => $_SESSION['edit-course']['meetingplace'],
                    'distance-about'     => $_POST['distance-about'],
                    'distance'           => $_POST['distance'],
                    'finishplace'        => $_SESSION['edit-course']['finishplace'],
                    'terrain'            => $_POST['terrain'],
                    'course-description' => $_POST['course-description'],
                    'options'            => $_SESSION['edit-course']['options'],
                    'checkpoints'        => $_SESSION['edit-course']['checkpoints']
                );
            } else if ($_POST['method'] == 'draw') {
                $_SESSION['edit-forms'][CFG_STAGE_ID-1] = array(
                    'method'             => $_SESSION['edit-course']['method'],
                    'meetingplace'       => $_SESSION['edit-course']['meetingplace'],
                    'distance-about'     => 'precise',
                    'distance'           => $_SESSION['edit-course']['distance'],
                    'finishplace'        => $_SESSION['edit-course']['finishplace'],
                    'terrain'            => $_SESSION['edit-course']['terrain'],
                    'course-description' => $_SESSION['edit-course']['course-description'],
                    'options'            => $_SESSION['edit-course']['options'],
                    'checkpoints'        => $_SESSION['edit-course']['checkpoints'],
                    'route-id'           => $_SESSION['edit-course']['route-id']
                );
            }
		}
        $ride_infos = $_SESSION['edit-forms'][1];
		$course_infos = $_SESSION['edit-forms'][2];
 
        // Displays summary page
        require('../includes/rides/edit/summary.php');
    break;
 
    case 2:
        // Default values
        if (empty($_SESSION['edit-forms'][CFG_STAGE_ID])) {
            $_SESSION['edit-forms'][CFG_STAGE_ID] = array(
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
			$_SESSION['edit-forms'][CFG_STAGE_ID-1] = array(
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
		
		require('../includes/rides/edit/course.php');
    break;
 
    case 1:
    default:
        // Default values
        if (empty($_SESSION['edit-forms'][CFG_STAGE_ID])) {
            $_SESSION['edit-forms'][CFG_STAGE_ID] = array(
                'ride-name'         => '',
                'date'              => '',
                'meeting-time'      => '',
                'departure-time'    => '',
                'finish-time'       => '',
                'nb-riders-min'     => '',
                'nb-riders-max'     => '',
                'level'             => [],
                'accepted-bikes'    => [],
                'ride-description'  => ''
            );
        }
 
        require('../includes/rides/edit/infos.php');
    break;
} ?>