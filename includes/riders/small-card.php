<div class="small-rdr-card <?php if (isset($_SESSION['auth']) && $rider->isFriend(getConnectedUser())) echo 'bg-friend' ?>">
    <div class="small-rdr-sub">
        <a style="text-decoration: none;" href="/rider/<?= $rider->id ?>"><?php $rider->getPropicElement(80, 80, 80); ?></a>
        <div>
            <div class="rdr-login-section">
                <?= $rider->login ?>
            </div> <?php
            if (!empty($rider->level)) { ?>
                <span class="tag-<?= $rider->colorLevel($rider->level); ?>">
                    <?= $rider->getLevelString(); ?>
                </span> <?php
            } ?>
        </div>
    </div> <?php
    include '../includes/riders/friends/buttons.php'; ?>
</div>