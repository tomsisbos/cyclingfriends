<?php

include '../includes/head.php';
include '../includes/rides/admin/head.php';
include '../actions/database.php'; 
include '../actions/rides/admin/guides.php';

// If ride author is not guide, don't show guide admin page
if (!$ride->getAuthor()->isGuide()) header('location: ' .$router->generate('ride-admin', ['ride_id' => $ride->id])) ?>

<!DOCTYPE html>
<html lang="en"> 
    
    <body> <?php

        include '../includes/navbar.php'; ?>

        <div class="main rd-ad-main container-shrink"> <?php

            include '../includes/rides/admin/header.php';
            include '../includes/rides/admin/navbar.php'; ?>

            <!-- Main section -->
            <div class="container rd-ad-container">

                <h3>ガイド管理</h3>
                <p>このツアーを担当するガイドの管理</p>

                <div class="rd-ad-section">

                <div class="rd-ad-form mb-3"> <?php
                    foreach ($ride->getGuides() as $registered_guide) { ?>
                        <div class="rd-ad-form-row">
                            <div class="rd-ad-form-type"><?= $registered_guide->getPositionString() ?></div>
                            <div class="rd-ad-form-question"><?= $registered_guide->login ?></div>
                            <form method="POST" id="remove">
                                <input type="hidden" name="guide" value="<?= $registered_guide->id ?>" />
                                <input type="submit" class="btn smallbutton" value="削除" name="remove" />
                            </form>
                        </div> <?php
                    } ?>
                </div>

                    <form method="POST" class="mb-3 row g-2" id="add">

                        <label class="form-label required">ガイドを追加</label>
                        <div class="d-flex">
                            <div class="col-6">
                                <select class="form-select" name="guide" id="addGuide">
                                    <option value="default" disabled selected>ガイドを選択...</option> <?php
                                    foreach ($guides as $guide) { ?>
                                        <option data-rank="<?= $guide->rank ?>" value="<?= $guide->id ?>"><?= '@' .$guide->login. ' (' .$guide->last_name. ' ' .$guide->first_name. '）・' .$guide->getRankString() ?></option> <?php
                                    } ?>
                                </select>
                            </div>
                            <div class="col-4">
                                <select class="form-select hidden" name="position" id="position">
                                    <option value="default" disabled selected>ポジションを選択...</option>
                                        <option value="1" disabled>チーフ</option>
                                        <option value="2" disabled>アシスタント</option>
                                        <option value="3" disabled>研修生</option>
                                </select>
                            </div>
                            <button type="submit" class="col-2 btn smallbutton" name="add">追加</button>
                        </div>
                        
                    </form>

                </div>

            </div>

        </div>

    </body>

</html>

<script src="/scripts/rides/admin/guides.js"></script>