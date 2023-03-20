<?php

if (basename($_SERVER['REQUEST_URI']) == 'join') {
	require '../actions/databaseAction.php';	
	// If connected user has not already joined,
	if (!$ride->isParticipating($connected_user)) {
		// If ride is not already full,
		if (!$ride->isFull()) {
			$ride->join($connected_user);
			$successmessage = $ride->name. 'にエントリーしました！楽しいライドになることは間違いありません！';		
		} else $errormessage = $ride->name. 'が既に定員に達成しています。';
	} else $errormessage = $ride->name. 'に既にエントリーしています。';
}

if (basename($_SERVER['REQUEST_URI']) == 'quit') {
	require '../actions/databaseAction.php';	
	// If rider is on the ride,
	if ($ride->isParticipating($connected_user)) {
		$ride->quit($connected_user);
		$successmessage = $ride->name. 'への参加を取り消しました。エントリー期間中であれば、いつでもエントリーできます。';		
	} else $errormessage = $ride->name. 'への参加を既に取り消しています。';
}

?>