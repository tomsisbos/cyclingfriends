<?php

include '../includes/head.php';
include '../includes/rides/admin/head.php'; ?>

<!DOCTYPE html>
<html lang="en"> 

    <body> <?php

        include '../includes/navbar.php'; ?>

        <div class="main rd-ad-main container-shrink"> <?php

            include '../includes/rides/admin/header.php';
            include '../includes/rides/admin/navbar.php';
            $additional_fields = $ride->getAdditionalFields();
            ?>

            <!-- Main section -->
            <div class="container rd-ad-container">

                <h3>エントリーリスト</h3>

                <div class="responsive-table-container">
                    <table class="rd-ad-entry-table gridtable">
                        <tbody>
                            <tr>
                                <th class="sticky-th-row"></th>
                                <th class="sticky-th-row">ユーザーネーム</th>
                                <th class="sticky-th-row">姓</th>
                                <th class="sticky-th-row">名</th>
                                <th class="sticky-th-row">メール</th>
                                <th class="sticky-th-row text-center">性別</th>
                                <th class="sticky-th-row text-center">年齢</th>
                                <th class="sticky-th-row">場所</th>
                                <th class="sticky-th-row">緊急時連絡先</th> <?php
                                foreach ($additional_fields as $additional_field) { ?>
                                    <th class="sticky-th-row"><?= $additional_field->question ?></th> <?php
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
                                        <td><?= $participant->email ?></td>
                                        <td class="text-center"><?= $participant->getGenderString() ?></td>
                                        <td class="text-center"><?= $participant->calculateAge() ?></td>
                                        <td><?php
                                            if (!empty($participant->location->city)) echo $participant->location->toString();
                                            else echo '-' ?>
                                        </td>
                                        <td class="text-center"><?= $participant->emergency_number ?></td> <?php
                                        foreach ($additional_fields as $additional_field) { ?>
                                            <td> <?php
                                                if ($additional_field->getAnswer($participant->id)) echo $additional_field->getAnswer($participant->id)->content; ?>
                                            </td> <?php
                                        } ?>
                                    </tr> <?php
                                }
                            } else echo '<tr><td colspan="99" class="text-center">参加者はまだいません。</td></tr>';
                            foreach ($ride->getGuides() as $guide) { ?>
                                <tr>
                                    <th class="sticky-th-column text-center">ガ）</td>
                                    <td><?= $guide->login ?></td>
                                    <td><?= $guide->last_name ?></td>
                                    <td><?= $guide->first_name ?></td>
                                    <td><?= $guide->email ?></td>
                                    <td class="text-center"><?= $guide->getGenderString() ?></td>
                                    <td class="text-center"><?= $guide->calculateAge() ?></td>
                                    <td><?php
                                        if (!empty($guide->location->city)) echo $guide->location->toString();
                                        else echo '-' ?>
                                    </td>
                                    <td class="text-center"><?= $guide->emergency_number ?></td> <?php
                                    foreach ($additional_fields as $additional_field) { ?>
                                        <td> <?php
                                            if ($additional_field->getAnswer($guide->id)) echo $additional_field->getAnswer($guide->id)->content; ?>
                                        </td> <?php
                                    } ?>
                                </tr> <?php

                            } ?>
                        </tbody>
                    </table>
                </div>

            </div>

        </div>

    </body>

</html>