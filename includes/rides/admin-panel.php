<div class="container margin-bottom container-admin">
	<h3>管理ボード</h3>
	<form class="admin-panel nav flex" enctype="multipart/form-data" method="POST">
		<div class="d-flex flex-column">
			<div>
				<label class="form-label">プライバシー設定</label>
				<select class="admin-field" name="privacy" id="privacySelect">
					<option value="private"<?php
						// First check if user has selected something and display it
						if (isset($_POST['privacy']) && $_POST['privacy'] == 'private') echo ' selected';
						// If user hasn't selected anything yet, check for existing data in ride table
						else if (isset($ride->privacy) && $ride->privacy == 'private') echo ' selected';
						?>>非公開</option>
					<option value="friends_only"<?php // If some of the participants are not in ride author's friendlist
						if (!$ride->isEveryParticipantInFriendsList($ride->getAuthor())) echo ' disabled';
						// First check if user has selected something and display it
						if (isset($_POST['privacy']) && $_POST['privacy'] == 'friends_only') echo ' selected';
						// If user hasn't selected anything yet, check for existing data in ride table
						else if (isset($ride->privacy) && $ride->privacy == 'friends_only') echo ' selected';
						?>>友達のみ</option>
					<option value="public"<?php
						// First check if user has selected something and display it
						if (isset($_POST['privacy']) && $_POST['privacy'] == 'public') echo ' selected';
						// If user hasn't selected anything yet, check for existing data in ride table
						else if (isset($ride->privacy) && $ride->privacy == 'public') echo ' selected';
						?>>公開</option>
				</select>
			</div>
			
			<?php // If some of the participants are not in ride author's friendlist
			if (!$ride->isEveryParticipantInFriendsList($ride->getAuthor())) {
				// Display a warning message ?>
				<script src="/assets/js/friends-only-popup.js"></script><?php
			} ?>
			
			<?php // Set ride date -1j into a variable
			$current_date = new DateTimeImmutable('now', new DateTimezone('Asia/Tokyo'));
			$ride_date = new DateTimeImmutable($ride->date, new DateTimezone('Asia/Tokyo'));
			$oneDayBeforeRide = $ride_date->modify('-1 day'); ?>
		
			<div>
				<label class="form-label">募集期間</label>
				<input type="date" class="admin-field" name="entry_start" value="<?php
				// First check if user has selected something and display it
				if (isset($_POST['entry_start'])) echo $_POST['entry_start'];
				// If user hasn't selected anything yet, check for existing data in ride table
				else if (isset($ride->entry_start)) echo $ride->entry_start;
				// If there is no data in the table, set default date to current date
				else echo $current_date->format('Y-m-d'); ?>" min="" max="<?= $oneDayBeforeRide->format('Y-m-d'); ?>">
				<input type="date" class="admin-field" name="entry_end" value="<?php
				// First check if user has selected something and display it
				if (isset($_POST['entry_end'])) echo $_POST['entry_end'];
				// If user hasn't selected anything yet, check for existing data in ride table
				else if (isset($ride->entry_end)) echo $ride->entry_end;
				// If there is no data in the table, set default date to ride's previous day
				else echo $oneDayBeforeRide->format('Y-m-d'); ?>" min="<?php if (isset($ride->entry_start)) { echo $ride->entry_start; } ?>" max="<?= $oneDayBeforeRide->format('Y-m-d') ?>">
			</div>
		</div>
		
		<div class="push flex-end">
			<button class="btn button" type="submit" name="save">保存</button>				
		</div>
	</form>
		
</div>