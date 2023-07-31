<?php

// Define variables and set default value to 0
$citybike = 0;	$roadbike = 0;	$mountainbike = 0;	$gravelcxbike = 0;
// Only set to true values found into the form variable
for($i = 0; isset($ride_infos['accepted-bikes'][$i]); $i++){
	switch($ride_infos['accepted-bikes'][$i]){
		case 0: $citybike = true; $roadbike = true; $mountainbike = true; $gravelcxbike = true; break;
		case 1:	$citybike = true; break;
		case 2:	$roadbike = true; break;
		case 3:	$mountainbike = true; break;
		case 4:	$gravelcxbike = true; break;
	}
}