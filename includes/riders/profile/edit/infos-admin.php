<div class="mb-3 row g-2">
	<div class="col" id="name">
		<label><strong>姓名 : </strong></label>
		<input name="last_name" type="text" id="floatingLastName" placeholder="姓" class="js-last-name admin-field" value="<?= $user->last_name; ?>">
		<input name="first_name" type="text" id="floatingFirstName" placeholder="名" class="js-first-name admin-field" value="<?= $user->first_name; ?>">
	</div>
	<div class="col">
		<label><strong>性別 : </strong></label>
		<select name="gender" class="js-gender admin-field">
			<option value="Undefined" <?php if (empty($user->gender)) { echo 'selected'; } ?>>特定なし</option>
			<option value="Man" <?php if ($user->gender == 'Man') { echo 'selected'; } ?>>男</option>
			<option value="Woman" <?php if ($user->gender == 'Woman') { echo 'selected'; } ?>>女</option>
		</select>
	</div>
</div>
<div class="mb-3 row g-2">
	<div class="col">
		<label><strong>生年月日 : </strong></label>
		<input name="birthdate" type="date" class="js-birthdate admin-field" min="1900-1-1" max="<?php date('Y-m-d'); ?>" value="<?= $user->birthdate; ?>">
	</div>
	<div class="col">
		<label><strong>活動拠点 : </strong></label>
			<button id="userLocationButton" class="btn smallbutton">地図で選択</button>
			<div id="userLocationString"><?php if ($user->location->city) echo $user->location->toString() ?></div>
	</div>
</div>
<div class="mb-3 row g-2">
	<div class="col">
		<label><strong>レベル : </strong></label>
		<select name="level" class="js-level admin-field" id="level">
			<option value="1" <?php if ($user->level == '1') { echo 'selected'; } ?>>初心者</option>
			<option value="2" <?php if ($user->level == '2') { echo 'selected'; } ?>>中級者</option>
			<option value="3" <?php if ($user->level == '3') { echo ' selected'; } ?>>上級者</option>
		</select>
	</div>
	<div class="col">
		<strong>登録日 : </strong>
		<?= $user->inscription_date ?>
	</div>
</div>
<div class="mb-3">
	<label><strong>紹介文 : </strong></label>
	<textarea name="description" class="js-description admin-field col-12"><?= $user->description ?></textarea>
</div>