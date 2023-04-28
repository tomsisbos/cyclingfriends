<div class="rdr-card <?php if ($connected_user->isFriend($rider)) { echo 'bg-friend'; } else { echo 'bg-rider'; } ?>">
    <div class="rdr-card-inner">

        <div class="rdr-card-main">

            <div class="rdr-card-top">

                <!-- Profile picture -->
                <div class="rdr-propic">
                    <a href="/rider/<?= $rider->id ?>"><?php $rider->getPropicElement(80, 80, 80); ?></a>
                </div>

                <!-- Left container -->
                <div class="rdr-container-left">
                    <div class="rdr-maininfos-section">
                        <a class="normal" href="/rider/<?= $rider->id ?>">
                            <div class="rdr-login-section"> <?php 
                                if (!empty($rider->gender)) { ?>
                                    <div class="rdr-gender">	<?php
                                        echo getGenderAsIcon($rider->gender); ?>
                                    </div> <?php
                                    } ?>
                                <div class="rdr-login js-login"><?= $rider->login; ?></div>
                                <div class="rdr-name"><?php
                                    if (!empty($rider->last_name && $rider->isRealNamePublic()) AND !empty($rider->first_name)) {
                                        echo '- (' .strtoupper($rider->last_name);
                                    }
                                    if (!empty($rider->first_name && $rider->isRealNamePublic())) {
                                        echo ' ' .ucfirst($rider->first_name. ')');
                                    } ?>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="rdr-sub">
                        <div class="d-flex gap"> <?php 
                            // Only display social links if filled
                            if (isset($rider->twitter) AND !empty($rider->twitter)) { ?>
                                <a target="_blank" href="<?= $rider->twitter ?>"><span class="social iconify twitter" data-icon="ant-design:twitter-circle-filled" data-width="20"></span></a> <?php
                            } if (isset($rider->facebook) AND !empty($rider->facebook)) { ?>
                                <a target="_blank" href="<?= $rider->facebook ?>"><span class="social iconify facebook" data-icon="akar-icons:facebook-fill" data-width="20"></span></a> <?php
                            } if (isset($rider->instagram) AND !empty($rider->instagram)){ ?>
                                <a target="_blank" href="<?= $rider->instagram ?>"><span class="social iconify instagram" data-icon="ant-design:instagram-filled" data-width="20"></span></a> <?php
                            } if (isset($rider->strava) AND !empty($rider->strava)){ ?>
                                <a target="_blank" href="<?= $rider->strava ?>"><span class="social iconify strava" data-icon="bi:strava" data-width="20"></span></a> <?php
                            } ?>
                        </div> <?php
                        if ($rider->isFriend($connected_user)) {
                            $friends_since = new Datetime($rider->friendsSince($connected_user->id));
                            echo $friends_since->format('Y-m-d'). ' から友達'; 
                        } ?>
                    </div>
                </div>
            </div>
                
            <!-- Right container -->
            <div class="rdr-container-right">
                <div class="rdr-sub"> <?php
                    if (!empty($rider->place)) { ?>
                        <div class="d-flex gap">
                            <span class="iconify" data-icon="gis:poi-map" data-width="20"></span>
                            <?= $rider->place; ?>
                        </div> <?php
                    } 
                    if (!empty($rider->birthdate)) { ?>
                        <strong>年齢 : </strong>
                        <?= $rider->calculateAge(). '才';
                    } ?>
                </div> <?php
                if (!empty($rider->level)) { ?>
                    <div>
                        <strong>レベル : </strong>
                        <span class="tag-<?= $rider->colorLevel($rider->level); ?>">
                            <?= $rider->getLevelString(); ?>
                        </span>
                    </div> <?php
                } 
                // If bike is set and bike type is filled
                if ($rider->getBikes()) { ?>
                    <div class="mt-1 mb-1">
                        <strong>バイク : </strong> <?php
                        foreach ($rider->getBikes() as $bike) {
                            $bike = new Bike($bike['id']);
                            if (!empty($bike->type)) { ?>
                                <div class="tag"><?= $bike->type; ?></div> <?php
                            } 
                        } ?>
                    </div> <?php
                } ?>
            </div>
        </div>
    
        <!-- Buttons --> <?php
        include '../includes/riders/friends/buttons.php'; ?>

    </div>
</div>