<div class="steps-container mb-3"> <?php

    for ($i = 1; $i <= count($steps); $i++) { ?>

        <div class="step">
            <div class="step-circle<?php if ($step == $i) echo ' step-current' ?>">
                <div class="step-number"><?= $i ?></div>
            </div>
            <div class="step-name"><?= $steps[$i - 1] ?></div>
        </div> <?php

        if ($i < count($steps)) echo '<div class="arrow"></div>';

    } ?>

</div>