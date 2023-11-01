
<?php

include '../includes/head.php'; ?>

<!DOCTYPE html>
<html lang="en">

<link rel="stylesheet" href="/assets/css/home.css" />

<body> <?php

    include '../includes/navbar.php'; ?>

    <!-- Main container -->
    <div class="container end">

        <h2 class="text-center">バイクレンタル規約</h2>

        <div class="mt-4"> <?php
            include '../public/api/rides/rental_contract.html'; ?>
        </div>

    </div> <?php

    include '../includes/foot.php'; ?>

</body>
</html>