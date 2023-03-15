<div class="container container-admin pf-bike-container js-bike-container" bike_id="<?php
    if (isset($bike)) echo $bike->id;
    else echo 'new'; ?>
">
    <form title="Upload image" class="js-bike-image-form" <?php if (!isset($bike)) echo 'id="newBikeForm" '; ?>name="bike-image-form" enctype="multipart/form-data" method="post" onchange="'submit()'">
        <div class="pf-bike-image-container"> <?php
            if (isset($bike)) $bike->displayImage();
            else echo '<img class="pf-bike-image" src="/media/default-bike-' .rand(1, 9). '.svg">' ?>
            <div class="image-icon-container">
                <label for="bikeImageFile<?php
                    if (isset($bike)) echo $bike->id;
                    else echo 'New' ?>">
                    <span class="image-icon iconify" data-icon="ic:baseline-add-a-photo" data-width="20" data-height="20"></span>
                </label>
                <input id="bikeImageFile<?php
                    if (isset($bike)) echo $bike->id;
                    else echo 'New'?>" type="file" class="js-bike-input hidden" name="bikeimagefile" size=50 />
                <input type="hidden" name="MAX_FILE_SIZE" value="500000" />
                <input type="hidden" name="bike-id" value="<?php
                    if (isset($bike)) echo $bike->id;
                    else echo 'new'; ?>" />
                <div title="Delete bike" class="js-delete-bike" >
                    <span class="image-icon iconify" data-icon="el:remove-circle" data-width="20" data-height="20"></span>
                </div>
            </div>
        </div>
        <div class="js-file-preview filename"></div>
    </form>

    <form method="post" class="fullwidth">
        <div class="pf-bike-row">
            <div class="pf-bike-column">
                <label><strong>車種 : </strong></label>
                <select name="bike-type" class="js-bike-type admin-field">
                    <option value="Other"<?php
                        if (isset($bike) AND $bike->type == 'Other') { echo ' selected="selected"'; }
                        ?>>その他</option>
                    <option value="City bike"<?php
                        if (isset($bike) AND $bike->type == 'City bike') { echo ' selected="selected"'; }
                        ?>>ママチャリ</option>
                    <option value="Road bike"<?php
                        if (isset($bike) AND $bike->type == 'Road bike') { echo ' selected="selected"'; }
                        ?>>ロードバイク</option>
                    <option value="Mountain bike" <?php
                        if (isset($bike) AND $bike->type == 'Mountain bike') { echo ' selected="selected"'; }
                        ?>>マウンテンバイク</option>
                    <option value="Gravel/Cyclocross bike" <?php
                        if (isset($bike) AND $bike->type == 'Gravel/Cyclocross bike') { echo ' selected="selected"'; }
                        ?>>グラベル／シクロクロスバイク</option>
                </select>
            </div>
            <div class="pf-bike-column">
                <label><strong>モデル : </strong></label>
                <input type="text" name="bike-model" class="js-bike-model admin-field" value="<?php if (isset($bike)) echo $bike->model; ?>">
            </div>
        </div>
        <div class="pf-bike-row">
            <div class="pf-bike-column">
                <label><strong>ホイール : </strong></label>
                <input type="text" name="bike-wheels" class="js-bike-wheels admin-field" value="<?php if (isset($bike)) echo $bike->wheels; ?>">
            </div>
            <div class="pf-bike-column">
                <label><strong>コンポネント : </strong></label>
                <input type="text" name="bike-components" class="js-bike-components admin-field" value="<?php if (isset($bike)) echo $bike->components; ?>">
            </div>
        </div>
        <div class="pf-bike-row">
            <div class="pf-bike-column">
                <label><strong>紹介文 : </strong></label>
                <textarea name="bike-description" class="js-bike-description admin-field"><?php if (isset($bike)) echo $bike->description; ?></textarea>
            </div>
        </div>
    </form>
</div>