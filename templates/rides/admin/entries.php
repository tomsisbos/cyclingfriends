<!DOCTYPE html>
<html lang="en"> <?php

include '../includes/rides/admin/head.php'; ?>

    <body> <?php

        include '../includes/navbar.php'; ?>

        <div class="main container-shrink"> <?php

            include '../includes/rides/admin/header.php';
            include '../includes/rides/admin/navbar.php';
            $additional_fields = $ride->getAdditionalFields();
            ?>

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
                                        <td class="text-center"><?= $participant->getGenderString() ?></td>
                                        <td class="text-center"><?= $participant->calculateAge() ?></td>
                                        <td><?php
                                            if (!empty($participant->location->city)) echo $participant->location->toString();
                                            else echo '-' ?>
                                        </td> <?php
                                        foreach ($additional_fields as $additional_field) {
                                            var_dump($additional_field->getAnswers()); ?>
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

    </body>

</html>