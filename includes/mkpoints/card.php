<div class="mk-card">
    <div class="mk-photo"><img src="data:image/jpeg;base64,<?= $mkpoint->getImages()[0]->blob ?>"></div>
    <div class="mk-data">
        <div class="mk-top"> <?php
            $mkpoint->user->displayPropic(); ?>
            <div class="mk-top-text">
                <div class="mk-title"><?= $mkpoint->name ?></div>
                <div class="mk-place-elevation"><?= $mkpoint->city . ' (' . $mkpoint->prefecture . ') - ' . $mkpoint->elevation . 'm' ?></div>
            </div>
        </div>
        <div class="mk-description"><?= $mkpoint->description ?></div>
        <div class="mk-reviews-title">Reviews</div>
        <div class="mk-reviews-form"></div>
    </div>
</div>