<div class="container mt-3">
    <h3>エントリーに必要な情報</h3> <?php

    // Name
    if (empty(getConnectedUser()->first_name) || empty(getConnectedUser()->last_name)) { ?>
        <div class="d-flex justify-content-between">
            <div class="form-floating col-5 mt-3">
                <input name="last_name" type="text" id="floatingLastName" placeholder="姓" class="form-control js-field"<?php if (isset($entry_data['last_name'])) echo ' value="' .$entry_data['last_name']. '"'?>>
                <label class="form-label" for="floatingLastName">姓</label>
            </div>
            <div class="form-floating col-5 mt-3">
                <input name="first_name" type="text" id="floatingFirstName" placeholder="名" class="form-control js-field"<?php if (isset($entry_data['first_name'])) echo ' value="' .$entry_data['first_name']. '"'?>>
                <label class="form-label" for="floatingFirstName">名</label>
            </div>
        </div> <?php
    }

    // Birthdate
    if (!getConnectedUser()->birthdate) { ?>
        <div class="form-floating mt-3">
            <input name="birthdate" type="date" class="form-control js-field" id="floatingBirthdate" min="1900-1-1" max="<?php date('Y-m-d'); ?>"<?php if (isset($entry_data['birthdate'])) echo ' value="' .$entry_data['birthdate']. '"'?>>
            <label class="form-label" for="floatingBirthdate">生年月日</label>
        </div> <?php
    }

    // Emergency number ?>
    <div class="form-floating mt-3">
        <input name="emergency_number" type="tel" class="form-control js-field" id="floatingEmergencyNumber"<?php if (isset($entry_data['emergency_number'])) echo ' value="' .$entry_data['emergency_number']. '"'?>>
        <label class="form-label" for="floatingEmergencyNumber">緊急時連絡先</label>
    </div> <?php

    // Additional fields
    include '../includes/rides/entry/additional-fields.php'; ?>
</div>