<!DOCTYPE html>
<html lang="en">

<?php 
session_start();
include '../includes/head.php';
include '../actions/users/securityAction.php'; ?>

<link rel="stylesheet" href="/assets/css/activity.css">
<link rel="stylesheet" href="/assets/css/ride.css" />

<body>

	<?php include '../includes/navbar.php'; ?>

    <div class="main">
        
        <h2 class="top-title">New activity</h2>
        
        <div class="container page">

            <div id="topContainer" class="container inner">

                <div class="new-ac-upload-container">
                    <label for="uploadActivity">
                        <div class="btn smallbutton">Upload activity</div>
                    </label>
                    <input type="file" id="uploadActivity" class="hidden" name="uploadActivity" />
                    <input type="hidden" name="MAX_FILE_SIZE" value="500000" />
                </div>
            
            </div>

                <div id="activityForm" style="display: none">

                    <div class="container inner">

                        <div class="mb-1">
                            <label class="form-label">Title</label>
                            <input type="text" id="inputTitle" class="form-control bold" />
                        </div>

                        <div class="new-ac-upload-photos-container">
                            <label for="uploadPhotos">
                                <div class="btn smallbutton">Upload photos</div>
                            </label>
                            <input type="file" id="uploadPhotos" class="hidden" name="uploadPhotos" multiple />
                            <input type="hidden" name="MAX_FILE_SIZE" value="500000" />
                            <div class="new-ac-inputgroup">
                                <label class="form-label">Bike</label>
                                <select id="selectBikes" class="form-select"> <?php
                                    $bikes = $connected_user->getBikes();
                                    foreach ($bikes as $entry) {
                                        $bike = new Bike($entry['id']) ?>
                                        <option value="<?= $bike->id ?>"><?= $bike->model . '(' . $bike->type . ')' ?></option><?php
                                    } ?>
                                </select>
                            </div>
                            <div class="new-ac-inputgroup">
                                <label class="form-label">Privacy</label>
                                <select id="selectPrivacy" class="form-select">
                                    <option value="private">Private</option>
                                    <option value="public" selected>Public</option>
                                    <option value="friends_only">Friends only</option>
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

                    <div id="activityMapContainer">
                        <div id="activityMap"></div>
                        <div class="grabber"></div>
                    </div>

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
    </div>

</body>
</html>

<script src="/map/vendor.js"></script>
<script src="/node_modules/exif-js/exif.js"></script>
<script type="module" src="/activities/new.js"></script>