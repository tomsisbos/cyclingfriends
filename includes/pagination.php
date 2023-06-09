<div class="pages"> <?php

    if ($p > 3) { ?>
        <a href="<?= $url. '?p=1' ?>">
            <div class="pages-number">
                1
            </div>
        </a>
        ... <?php
    }
    if ($p > 2) { ?>
        <a href="<?= $url. '?p=' .($p - 2) ?>">
            <div class="pages-number">
                <?= $p - 2; ?>
            </div>
        </a> <?php
    }
    if ($p > 1) { ?>
        <a href="<?= $url. '?p=' .($p - 1) ?>">
            <div class="pages-number">
                <?= $p - 1; ?>
            </div>
        </a> <?php
    } ?>
    <a href="<?= $url. '?p=' .$p ?>">
        <div class="pages-number pages-number-selected">
            <?= $p ?>
        </div>
    </a> <?php
    if ($p < ceil($total_pages)) { ?>
        <a href="<?= $url. '?p=' .($p + 1) ?>">
            <div class="pages-number">
                <?= $p + 1; ?>
            </div>
        </a> <?php
    }
    if ($p < ceil($total_pages - 1)) { ?>
        <a href="<?= $url. '?p=' .($p + 2) ?>">
            <div class="pages-number">
                <?= $p + 2; ?>
            </div>
        </a> <?php
    }
    if (ceil($total_pages) - $p > 2) { ?>
        ...
        <a href="<?= $url. '?p=' .($total_pages) ?>">
            <div class="pages-number">
                <?= $total_pages; ?>
            </div>
        </a> <?php
    } ?>
</div>