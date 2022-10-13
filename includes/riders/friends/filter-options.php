<form method="POST" class="mb-3 row g-2">
	<div class="col-md">
		<div class="tr-row">
			<label class="form-label td-row element-20">
				Search
			</label>
			<input class="form-control td-row element-50" type="text" name="friend_search" value="<?php if(isset($_POST['friend_search'])){echo $_POST['friend_search'];} ?>" onfocusout="this.form.submit();" />
		</div>
		<div class="tr-row">
			<label class="form-label td-row element-20">Order by</label>
			<select class="form-select td-row element-50" name="friend_orderby" onfocusout="this.form.submit();">
				<option value="approval_date"
				<?php if(!empty($_POST)){if($_POST['friend_orderby'] == 'approval_date'){echo ' selected';}} ?>>
					Friendship duration</option>
				<option value="login"
				<?php if(!empty($_POST)){if($_POST['friend_orderby'] == 'login'){echo ' selected';}} ?>>
					Login</option>
				<option value="last_name"
				<?php if(!empty($_POST)){if($_POST['friend_orderby'] == 'last_name'){echo ' selected';}} ?>>
					Last name</option>
				<option value="first_name"
				<?php if(!empty($_POST)){if($_POST['friend_orderby'] == 'first_name'){echo ' selected';}} ?>>
					First name</option>
				<option value="place"
				<?php if(!empty($_POST)){if($_POST['friend_orderby'] == 'place'){echo ' selected';}} ?> disabled>
					Distance from me</option> <!-- Needs to implement maps system -->
				<option value="level"
				<?php if(!empty($_POST)){if($_POST['friend_orderby'] == 'level'){echo ' selected';}} ?>>
					Level</option>
				<option value="birthdate"
				<?php if(!empty($_POST)){if($_POST['friend_orderby'] == 'birthdate'){echo ' selected';}} ?>>
					Age</option>
			</select>
		</div>
	</div>
	<div class="col-md">
	</div>
</form>