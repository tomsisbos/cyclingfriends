<div class="summary-checkpoints gallery"> <?php
    for ($i = 0; $i < count($ride->checkpoints); $i++) {
        $checkpoint = $ride->checkpoints[$i];
        $rand[$i] = rand(1,9); ?>
        <div class="summary-checkpoint" id="<?= $i; ?>">
            <div class="summary-checkpoint-image"> <?php
                if (isset($checkpoint->img->blob)) { ?>
                    <img thumbnailId="<?= $i + 1 ?>" class="js-clickable-thumbnail" src="<?= 'data:' .$checkpoint->img->type.  ';base64,' .$checkpoint->img->blob ?>"> <?php
                } else { ?>		
                    <img thumbnailId="<?= $i + 1 ?>" class="js-clickable-thumbnail" src="\includes\media\default-photo-<?= $rand[$i] ?>.svg"> <?php
                }
                if ($i > 0 AND $i < count($ride->checkpoints) - 1) { ?>
                    <div class="summary-checkpoint-number"> 
                        <?= $i; ?>
                    </div> <?php
                } else { 
                    if ($i === 0) { ?>
                        <div class="summary-checkpoint-tag tag-start">
                            <?= 'START' ?>
                        </div> <?php
                    } else if ($i === count($ride->checkpoints) - 1) { ?>
                        <div class="summary-checkpoint-tag tag-goal">
                            <?= 'GOAL' ?>
                        </div> <?php
                    } 
                } ?>
                <div class="summary-checkpoint-name"> <?php
                    if (isset($checkpoint->name) && $checkpoint->name != 'Checkpoint n°0' && $checkpoint->name != 'Checkpoint n°'. (count($ride->checkpoints) - 1)) { 
                        echo $checkpoint->name. ' - ';
                    } else {
                        if ($i === 0) {
                            echo 'Start - ';
                        } else if ($i == (count($ride->checkpoints) - 1)) {
                            echo 'Goal - ';
                        } else {
                            echo 'Checkpoint n°' .$i. ' - ';
                        }
                    } 
                    if (isset($ride->route)) {
                        if ($checkpoint->number != 0 && $checkpoint->distance == 0) { ?>
                            <span style="font-weight: normal"><?= 'km ' .round($ride->distance, 1); ?></span> <?php
                        } else { ?>
                            <span style="font-weight: normal"><?= 'km ' .round($checkpoint->distance, 1); ?></span> <?php
                        }
                    } else { ?>
                        <span style="font-weight: normal"><?= 'alt. ' .$checkpoint->elevation. 'm'; ?></span> <?php
                    } ?>
                </div>
            </div>
            <?php
            if (!empty($checkpoint->description)) { ?>
                <div class="summary-checkpoint-description"> 
                    <?= $checkpoint->description ?>
                </div> <?php
            } ?>
        </div> <?php
        if ($i != (count($ride->checkpoints) - 1)) { ?>
            <svg height="120" width="10">
                <polygon points="0,00 10,60 0,120" />
            </svg> <?php
        }
    } ?>
</div>

<!-- Modal window
Only display currently selected thumbnail picture, if a corresponding blob exists in the database -->

<div id="myModal" class="modal">
    <span class="close cursor" onclick="closeModal()">&times;</span>
    <div class="modal-block">

        <?php // Get total number of photos in a variable for displaying correct number
        for ($i = 0; $i < count($ride->checkpoints); $i++) {
            $checkpoint = $ride->checkpoints[$i]; ?>
            <div class="mySlides">
                <div class="numbertext"><?= ($i+1). ' / ' .count($ride->checkpoints);?></div> <?php
                if (isset($checkpoint->img->blob)) { ?>
                    <img src="<?= 'data:image/jpeg;base64,' . $checkpoint->img->blob; ?>" style="width:100%"> <?php
                } else { ?>
                    <img src="\includes\media\default-photo-<?= $rand[$i]; ?>.svg"> <?php
                } ?>
            </div> <?php
        } ?>
            
        <a class="prev nav-link">&#10094;</a>
        <a class="next nav-link">&#10095;</a>

        <?php // Display name as an input if current user have ride admin rights
        if ($connected_user == $ride->author) { ?>
            <div class="lightbox-admin-panel container-admin name-container"> <?php
                for ($i = 0; $i < count($ride->checkpoints); $i++) {
                    $checkpoint = $ride->checkpoints[$i]; ?>
                    <input type="text" class="admin-field js-name column-field form-control text-center" style="display: none" name="input<?= $i; ?>" placeholder="Write a name..." value="<?= $checkpoint->name; ?>" /> <?php
                } ?>
            </div> <?php
        } else { // Else, display name ?>
            <div class="name-container"> <?php
                for ($i = 0; $i < count($ride->checkpoints); $i++) {
                    $checkpoint = $ride->checkpoints[$i]; ?>
                    <p class="js-name" style="display: none" id="name"><?= $checkpoint->name; ?></p> <?php
                } ?>
            </div> <?php
        } ?>

        <!-- Modal thumbnails
        Only display the picture if a corresponding blob exists in the database
        If has admin rights, displays an editable text area with current caption as default content for editing -->

        <div class="d-flex justify"> <?php
            for ($i = 0; $i < count($ride->checkpoints); $i++) {
                $checkpoint = $ride->checkpoints[$i]; ?>
                <div class="column">
                    <img class="demo cursor" src="<?php if (isset($checkpoint->img->blob)) { echo 'data:image/jpeg;base64,' . $checkpoint->img->blob; } else { echo '\includes\media\default-photo-' .$rand[$i]. '.svg'; } ?>" style="width:100%" demoId="<?= $i + 1 ?>" alt="<?= $checkpoint->description ?>">
                </div> <?php
            } ?>
        </div>

        <?php // Display caption as a text area if current user have ride admin rights
        if ($connected_user == $ride->author) { ?>
            <div class="lightbox-admin-panel container-admin caption-container"> <?php
                for ($i = 0; $i < count($ride->checkpoints); $i++) {
                    $checkpoint = $ride->checkpoints[$i]; ?>
                    <textarea class="admin-field js-caption column-field form-control" style="display: none" name="textarea<?= $i; ?>" placeholder="Write a caption..."><?= $checkpoint->description ?></textarea> <?php
                } ?>
            </div> <?php
        } else { // Else, display caption ?>
            <div class="caption-container"> <?php
                for ($i = 0; $i < count($ride->checkpoints); $i++) {
                    $checkpoint = $ride->checkpoints[$i]; ?>
                    <p class="js-caption" style="display: none"><?= $checkpoint->description ?></p> <?php
                } ?>
            </div> <?php
        } ?>
    
    </div>
</div>