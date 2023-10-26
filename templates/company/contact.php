<?php

include '../actions/users/initPublicSession.php';
include '../includes/head.php'; 
include '../actions/company/contact.php'; ?>

<!DOCTYPE html>
<html lang="en">

<link rel="stylesheet" href="/assets/css/home.css" />

<body class="black-theme"><?php

    include '../includes/navbar.php';
    
    // Space for general error messages
    include '../includes/result-message.php'; ?>

    <!-- Main container -->
    <div class="container home-container end">
        <form method="POST" class="container smaller">

            <h1 class="text-center mb-3">お問い合わせ</h1>

            <p>ご自由にお問い合わせください。1営業日以内に対応させて頂きます。</p> <?php

            include '../includes/contact-form.php'; ?>

        </form>
    </div> <?php

    include '../includes/foot.php'; ?>

</body>
</html>

<script src="/assets/js/fade-on-scroll.js"></script>