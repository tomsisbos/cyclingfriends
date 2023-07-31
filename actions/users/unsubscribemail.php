<?php

require '../actions/database.php';
 
// Signup form validation
if (isset($_POST['validate'])) {
	 
	 // Check if user completed all fields
	if (!empty($_POST['email'])) {
		
		// Check if email address is valid
		if (filter_var(htmlspecialchars($_POST['email']), FILTER_VALIDATE_EMAIL)) {
		 
			// Email data
			$address = htmlspecialchars($_POST['email']);
            $mailing_entry = new MailingListEntry($address);
			
            // Check if email address is already used
            if ($mailing_entry->isRegistered()) {
                $mailing_entry->unsubscribe();
                $mailing_entry->sendUnregistrationMail();
                $successmessage = 'メーリングリストから"' . $address . '"を除外しました。これからCyclingFriendsからのメールを送信しません。';
            } else $errormessage = 'このメールアドレスは登録されていません。';
                
		} else $errormessage = "正しいフォーマットのメールアドレスをご記入ください。";
    } else $errormessage = "メールアドレスをご記入ください。";
}

?>