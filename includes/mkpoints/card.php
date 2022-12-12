<div class="mk-card">
    <div class="mk-photo"><a href="/scenery/<?= $mkpoint->id ?>"><img src="data:image/jpeg;base64,<?= $mkpoint->getImages()[0]->blob ?>"></a></div>
    <div class="mk-data">
        <div class="mk-top"> 
            <a href="/rider/<?= $mkpoint->user->id ?>"><?php
                $mkpoint->user->displayPropic(); ?>
            </a>
            <div class="mk-top-text">
                <a href="/scenery/<?= $mkpoint->id ?>"><div class="mk-title"><?= $mkpoint->name ?></div></a>
                <div class="mk-place-elevation"><?= $mkpoint->city . ' (' . $mkpoint->prefecture . ') - ' . $mkpoint->elevation . 'm' ?></div>
            </div>
        </div>
        <div class="mk-description"><?= $mkpoint->description ?></div>
    </div>
</div>