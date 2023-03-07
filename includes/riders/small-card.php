<div class="rdr-card rdr-card-inner <?php if ($rider->isFriend($connected_user)) echo 'bg-friend' ?>">
    <a style="text-decoration: none;" href="/rider/<?= $rider->id ?>"><?php $rider->getPropicElement(80, 80, 80); ?></a>
    <div class="rdr-sub">
        <div class="rdr-maininfos-section">
            <div class="rdr-login-section">
                <?= $rider->login ?>
            </div> <?php
            if (!empty($rider->level)) { ?>
                <span class="tag-<?= $rider->colorLevel($rider->level); ?>">
                    <?= $rider->getLevelString(); ?>
                </span> <?php
            } ?>
        </div> <?php
        $friends_since = new Datetime($rider->friendsSince($user->id));
        echo $friends_since->format('Y-m-d'). ' から友達'; ?>
    </div> <?php
    include '../includes/riders/friends/buttons.php'; ?>
</div>