	<form method="POST" class="mb-3 row g-2">
		<div class="col-md">
			<label class="form-label">Dates range</label>
			<div class="tr-row">
				<div class="td-row">
					<input type="date" class="form-control" name="filter_date_min" value="<?php if(isset($_POST['filter_date_min'])){echo $_POST['filter_date_min'];} ?>" min="" max="2099-12-31" onChange="this.form.submit();">
				</div>
				<div class="td-row">
					<div>➤</div>
				</div>
				<div class="td-row">
					<input type="date" class="form-control" name="filter_date_max" value="<?php if(isset($_POST['filter_date_max'])){echo $_POST['filter_date_max'];} ?>" min="" max="2099-12-31" onChange="this.form.submit();">
				</div>
			</div>
			<div class="tr-row">
				<div class="td-row">
					<input type="checkbox" id="filter_bike" name="filter_bike" onChange="this.form.submit();" <?php if(isset($_POST['filter_bike'])){echo 'checked';} ?>>
				</div>
				<div class="td-row">
					<label for="filter_bike">Only display rides accepting <a href="/profile/edit#addBike">my bikes</a> type</label>
				</div>
			</div>
			<div class="tr-row">
				<div class="td-row">
					<input type="checkbox" id="filter_friends_only" name="filter_friends_only" onChange="this.form.submit();" <?php if(isset($_POST['filter_friends_only'])){echo 'checked';} ?>>
				</div>
				<div class="td-row">
					<label for="filter_friends_only">Only display my friends ride</label>
				</div>
			</div>
			<!-- Needs to implement maps system
			<div class="tr-row">
				<div class="td-row">
					<input type="checkbox" name="filter_closest_first" onChange="this.form.submit();">
				</div>
				<div class="td-row">
					<label>Display closest rides first</label>
				</div>
			</div>
			-->
		</div>
		<div class="col-md">
			<div class="tr-row">
				<div class="td-row element-20">
					<label class="form-label">Ride status<label/>
				</div>
				<div class="td-row element-50">
					<select class="form-select" name="filter_status" onChange="this.form.submit();">
						<option value="No filter">No filter</option>
						<option value="Open" <?php if(isset($_POST['filter_status']) AND $_POST['filter_status'] == 'Open'){echo 'selected';} ?>>Open</option>
						<option value="Closed" <?php if(isset($_POST['filter_status']) AND $_POST['filter_status'] == 'Closed'){echo 'selected';} ?>>Closed</option>
						<option value="Finished" <?php if(isset($_POST['filter_status']) AND $_POST['filter_status'] == 'Finished'){echo 'selected';} ?>>Finished</option>
					</select>
				</div>
			</div>
			<div class="tr-row">
				<div class="td-row element-20">
					<label class="form-label">Level<label/>
				</div>
				<div class="td-row element-50">
					<select class="form-select" name="filter_level" onChange="this.form.submit();">
						<option value="No filter">No filter</option>
						<option value="Beginner" <?php if(isset($_POST['filter_level']) AND $_POST['filter_level'] == 'Beginner'){echo 'selected';} ?>>Beginner</option>
						<option value="Intermediate" <?php if(isset($_POST['filter_level']) AND $_POST['filter_level'] == 'Intermediate'){echo 'selected';} ?>>Intermediate</option>
						<option value="Athlete" <?php if(isset($_POST['filter_level']) AND $_POST['filter_level'] == 'Athlete'){echo 'selected';} ?>>Athlete</option>
					</select>
				</div>
			</div>
			<div class="tr-row">
				<div class="td-row element-20">
					<label class="form-label">Ride name<label/>
				</div>
				<div class="td-row element-50">
					<input class="form-control" type="text" name="filter_name" value="<?php if(isset($_POST['filter_name'])){echo $_POST['filter_name'];} ?>" onfocusout="this.form.submit();" />
				</div>
			</div>
		</div>
	</form>