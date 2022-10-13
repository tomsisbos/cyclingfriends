<?php

// Check for an AJAX request
function isAjax(){
	return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
}

?>