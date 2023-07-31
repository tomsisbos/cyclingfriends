<?php

switch ($course_infos['terrain']) {
	case 'Flat':        $terrain = 1; break;
	case 'Small hills':	$terrain = 2; break;
	case 'Hills':      	$terrain = 3; break;
	case 'Mountains':   $terrain = 4; break;
	default:            $terrain = $course_infos['terrain'];
}