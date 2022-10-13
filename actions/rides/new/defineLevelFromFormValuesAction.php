<?php

// Define variables and set default value to 0
$level_beginner = 0; $level_intermediate = 0; $level_athlete = 0;
// Only set to true values found into the form variable
for($i = 0; isset($ride_infos['level'][$i]); $i++){
	switch($ride_infos['level'][$i]){
		case 0: $level_beginner = true; $level_intermediate = true; $level_athlete = true; break;
		case 1: $level_beginner = true; break;
		case 2: $level_intermediate = true; break;
		case 3: $level_athlete = true; break;
	}
}