<!DOCTYPE html>
<html lang="en">
    
<link rel="stylesheet" href="/assets/css/ride.css" />

<?php
session_start();
include '../actions/users/securityAction.php';

// Get ride from slug
$slug = basename($_SERVER['REQUEST_URI']);
$url_fragments = explode('/', $_SERVER['REQUEST_URI']);
$slug = array_slice($url_fragments, -2)[0];
if (is_numeric($slug)) $ride = new Ride($slug);

// Only allow access to ride admin
if ($ride->author->id == $connected_user->id) { ?>

    <body> <?php

        include '../includes/navbar.php'; ?>

        <div class="main container-shrink"> <?php

            // Space for general error messages
            displayMessage() ?>

            <!-- Header -->
            <div class="container bg-admin">
                <div class="d-flex">
                    <div class="rd-ad-name">
                        <h2><?= $ride->name ?></h2>
                        <p>管理ページ</p>
                    </div>
                    <a class="push" href="/ride/<?= $ride->id ?>">
                        <button class="btn button box-shadow" type="button" name="edit">ライドページへ戻る</button>
                    </a>
                </div>
            </div>

            <!-- Main section -->
            <div class="container end">
                <h3>エントリーリスト</h3>
                <div class="responsive-table-container">
                    <table class="rd-ad-entry-table gridtable">
                        <tbody>
                            <tr>
                                <th class="sticky-th-row"></th>
                                <th class="sticky-th-row">ユーザーネーム</th>
                                <th class="sticky-th-row">姓</th>
                                <th class="sticky-th-row">名</th>
                                <th class="sticky-th-row text-center">性別</th>
                                <th class="sticky-th-row text-center">年齢</th>
                                <th class="sticky-th-row">場所</th> <?php
                                foreach ($ride->getAdditionalFields() as $additional_field) { ?>
                                    <th class="sticky-th-row"><?= $additional_field['name'] ?></th> <?php
                                } ?>
                            </tr> <?php
                            $number = 0;
                            if (is_array($ride->getParticipants())) {
                                foreach ($ride->getParticipants() as $entry) {
                                    $participant = new User($entry);
                                    $number++ ?>
                                    <tr>
                                        <th class="sticky-th-column text-center"><?= $number ?></td>
                                        <td><?= $participant->login ?></td>
                                        <td><?= $participant->last_name ?></td>
                                        <td><?= $participant->first_name ?></td>
                                        <td class="text-center"><?= $participant->getGenderString() ?></td>
                                        <td class="text-center"><?= $participant->calculateAge() ?></td>
                                        <td><?= $participant->location->toString() ?></td> <?php
                                        foreach ($ride->getAdditionalFields() as $additional_field) { ?>
                                            <td><?php if (isset($additional_field[$participant->id])) echo $additional_field[$participant->id] ?></th> <?php
                                        } ?>
                                    </tr> <?php
                                }
                            } else echo '<tr><td colspan="99" class="text-center">参加者はまだいません。</td></tr>' ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

    </body> <?php

} else header('location: /' . $connected_user->login . '/rides') ?>

</html>