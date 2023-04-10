
<div class="container smaller page">

<!-- Page title -->
<h1 class="text-center">Edit Ride</h1>

<!--Displays the Ride Infos form-->
<form class="container smaller inner" method="POST" action="<?= $base_uri . (CFG_STAGE_ID + 1) ?>">

	<legend>ライド情報</legend>
	
	<!--Displays an error message if needed-->
	<?php if (isset($errormessage)) echo `
		<div class="error-block">
			<p class="error-message">` .$errormessage. `</p>
		</div>`; ?>
	
	<div class="mb-3">
		<label class="form-label required">タイトル</label>
		<input type="text" class="form-control" name="ride-name" value="<?= $_SESSION['edit-forms'][CFG_STAGE_ID]['ride-name']; ?>">
	</div>
	<div class="mb-3">
		<label class="form-label required">開催日</label>
		<input type="date" class="form-control" name="date" value="<?= $_SESSION['edit-forms'][CFG_STAGE_ID]['date']; ?>" min="<?= date('Y-m-d'); ?>" max="2099-12-31">
	</div>
	<div class="mb-3 row g-2">
		<div class="col-md">
			<div class="form-floating">
				<input type="time" class="form-control" name="meeting-time" value="<?= $_SESSION['edit-forms'][CFG_STAGE_ID]['meeting-time']; ?>">
				<label for="floatingInputGrid" class="required">集合時間</label>
			</div>
		</div>
		<div class="col-md">
			<div class="form-floating">
				<input type="time" class="form-control" name="departure-time" value="<?= $_SESSION['edit-forms'][CFG_STAGE_ID]['departure-time']; ?>">
				<label for="floatingInputGrid" class="required">出発時間</label>
			</div>
		</div>
	</div>
	<div class="mb-3 form-floating">
		<input type="time" class="form-control" name="finish-time" value="<?= $_SESSION['edit-forms'][CFG_STAGE_ID]['finish-time']; ?>">
		<label for="floatingInputGrid" class="required">解散時間</label>
	</div> <?php

	// Set max riders number depending on if user has guide or moderator rights or not
	if ($connected_user->hasModeratorRights() || $connected_user->isGuide()) $nb_riders_max = 30;
	else $nb_riders_max = 7; ?>

	<div class="mb-3 row g-2">
		<div class="col-md">
			<div class="form-floating">
				<div class="with-range-output required">最低催行人数</div>
				<input type="range" class="form-range" min="1" max="<?= $nb_riders_max ?>" name="nb-riders-min" value="<?= $_SESSION['edit-forms'][CFG_STAGE_ID]['nb-riders-min']; ?>" oninput="this.nextElementSibling.value = this.value"><output><?= $_SESSION['edit-forms'][CFG_STAGE_ID]['nb-riders-min']; ?></output>
			</div>
		</div>
		<div class="col-md">
			<div class="form-floating">
				<div class="with-range-output required">定員</div>
				<input type="range" class="form-range" min="1" max="<?= $nb_riders_max ?>" name="nb-riders-max" value="<?= $_SESSION['edit-forms'][CFG_STAGE_ID]['nb-riders-max']; ?>" oninput="this.nextElementSibling.value = this.value"><output><?= $_SESSION['edit-forms'][CFG_STAGE_ID]['nb-riders-max']; ?></output>
			</div>
		</div>
	</div>
	<div class="mb-3 row g-2">
		<div class="col-md">
			<label class="form-label required">レベル</label>
			<select class="form-select" name="level[]" multiple>
				<option value="1"<?php
					if (in_array(1, $_SESSION['edit-forms'][CFG_STAGE_ID]['level'])) {echo ' selected="selected"';}
					?>>初心者</option>
				<option value="2" <?php
					if (in_array(2, $_SESSION['edit-forms'][CFG_STAGE_ID]['level'])) {echo ' selected="selected"';}
					?>>中級者</option>
				<option value="3" <?php
					if (in_array(3, $_SESSION['edit-forms'][CFG_STAGE_ID]['level'])) {echo ' selected="selected"';}
					?>>上級者</option>
			</select>
		</div>
		<div class="col-md">
			<label class="form-label required">参加可能車種</label>
			<select class="form-select" name="accepted-bikes[]" multiple>
				<option value="1" <?php
					if (in_array(1, $_SESSION['edit-forms'][CFG_STAGE_ID]['accepted-bikes'])) {echo ' selected="selected"';}
					?>>ママチャリ＆その他自転車</option>
				<option value="2" <?php
					if (in_array(2, $_SESSION['edit-forms'][CFG_STAGE_ID]['accepted-bikes'])) {echo ' selected="selected"';}
					?>>ロードバイク</option>
				<option value="3" <?php
					if (in_array(3, $_SESSION['edit-forms'][CFG_STAGE_ID]['accepted-bikes'])) {echo ' selected="selected"';}
					?>>マウンテンバイク</option>
				<option value="4" <?php
					if (in_array(4, $_SESSION['edit-forms'][CFG_STAGE_ID]['accepted-bikes'])) {echo ' selected="selected"';}
					?>>グラベル＆シクロクロスバイク</option>
			</select>
		</div>
	</div>
	<div class="mb-3">
		<label class="form-label required">ライド紹介</label>
		<textarea class="form-control" name="ride-description"><?= br2nl($_SESSION['edit-forms'][CFG_STAGE_ID]['ride-description']); ?></textarea>
	</div>
	
	
	<div class="btn-container">
		<button type="submit" class="btn button btnright btn-primary" name="next">進む</button>
	</div>	
		
</form>

</div>