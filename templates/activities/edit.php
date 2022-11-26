<!DOCTYPE html>
<html lang="en">

<?php 
session_start();
include '../actions/users/securityAction.php';
include '../actions/activities/getActivityAction.php'; ?>

<link rel="stylesheet" href="/assets/css/activity.css">
<link rel="stylesheet" href="/assets/css/ride.css" />

<body>

	<?php include '../includes/navbar.php'; ?>

    <div class="main">
        
        <h2 class="top-title">Edit activity</h2>

        <div id="activityForm">

            <div class="container">

                <div class="mb-1">
                    <label class="form-label">Title</label>
                    <input type="text" id="inputTitle" class="form-control bold" value="<?= $activity->title ?>" />
                </div>

                <div class="mb-3 gap d-flex">
                    <div class="col-6 gap d-flex align-items-center">
                        <label for="uploadPhotos">
                            <div class="btn smallbutton">Upload photos</div>
                        </label>
                        <input type="file" id="uploadPhotos" class="hidden" name="uploadPhotos" multiple />
                        <input type="hidden" name="MAX_FILE_SIZE" value="500000" />
                    </div>
                    <div class="col-3">
                        <label class="form-label">Bike</label>
                        <select id="selectBikes" class="form-select"> <?php
                            $bikes = $connected_user->getBikes();
                            foreach ($bikes as $entry) {
                                $bike = new Bike($entry['id']) ?>
                                <option value="<?= $bike->id ?>" <?php if ($activity->bike == $entry['id']) echo 'selected'; ?>><?= $bike->model . ' (' . $bike->type . ')' ?></option><?php
                            } ?>
                        </select>
                    </div>
                    <div class="col-3">
                        <label class="form-label">Privacy</label>
                        <select id="selectPrivacy" class="form-select">
                            <option value="private" <?php if ($activity->privacy == 'private') echo 'selected'; ?>>Private</option>
                            <option value="public" <?php if ($activity->privacy == 'public') echo 'selected'; ?>>Public</option>
                            <option value="friends_only" <?php if ($activity->privacy == 'friends_only') echo 'selected'; ?>>Friends only</option>
                        </select>
                    </div>
                </div>

                <div class="new-ac-properties-container">
                    <div class="col-4 d-flex flex-column">
                        <div id="divStart"><strong>Start : </strong></div>
                        <div id="divGoal"><strong>Goal : </strong></div>
                    </div>
                    <div class="col-4 d-flex flex-column">
                        <div id="divDistance"><strong>Distance : </strong></div>
                        <div id="divDuration"><strong>Duration : </strong></div>
                        <div id="divElevation"><strong>Elevation : </strong></div>
                    </div>
                    <div class="col-4 d-flex flex-column">
                        <div id="divMinTemperature"><strong>Min. Temperature : </strong></div>
                        <div id="divAvgTemperature"><strong>Avg. Temperature : </strong></div>
                        <div id="divMaxTemperature"><strong>Max. Temperature : </strong></div>
                    </div>
                </div>
            </div>

            <div class="container p-0">
                <div id="activityMapContainer">
                    <div id="activityMap"></div>
                    <div class="grabber"></div>
                </div>
            </div>

            <div class="container">
                <div id="divCheckpoints" class="container inner">
                </div>
                <div class="container inner">
                    <div class="new-ac-save-container">
                        <div id="saveActivity" class="btn smallbutton push">Save</div>
                    </div>
                </div>
            </div>
        </div>

    </div>

</body>
</html>

<script src="/scripts/map/vendor.js"></script>
<script src="/node_modules/exif-js/exif.js"></script>
<script type="module" src="/scripts/activities/edit.js"></script>