<?php // Set up previous page variable for the back button
$previous_page = intval($stage_slug) - 1; ?>

<div class="container smaller page">

	<!-- Page title -->
	<h1 class="text-center">ライド編集</h1>
	<legend>コース情報</legend>

	<!-- Displays an error message if needed -->
	<?php if (isset($errormessage)) echo '<div class="error-block"><p class="error-message">' .$errormessage. '</p></div>'; ?>

	<!-- Course infos input method selection form -->
	<form id='form' class="container smaller inner" method="POST" action="<?= $base_uri . (CFG_STAGE_ID + 1) ?>">
		<label class="form-label">コース選定方法</label>
		<select id="formMethodSelect" class="form-select" name="method">
			<option <?php if (empty($_SESSION['edit-forms'][CFG_STAGE_ID]['method'])) {echo 'selected';} ?> hidden disabled value="none">方法を選ぶ...</option>
			<option <?php if ($_SESSION['edit-forms'][CFG_STAGE_ID]['method'] == 'pick') {echo 'selected';} ?> value="pick">地図上にクリックしてチェックポイントを作る</option>
			<option <?php if ($_SESSION['edit-forms'][CFG_STAGE_ID]['method'] == 'import') {echo 'selected';} ?> value="import" disabled>*.gpxファイルをインポートする</option>
			<option <?php if ($_SESSION['edit-forms'][CFG_STAGE_ID]['method'] == 'draw') {echo 'selected';} ?> value="draw">自分のルートの中から選ぶ</option>
		</select>

		<!-- Pick method -->
		<div id="js-pick" style="display: none;">
			<div class="rd-course-map-container">
				<div class="rd-course-map" id="newPickMap" class="mb-3">
				</div>
			</div>
			<div class="rd-course-fields">
				<div class="mb-3 row g-2">
					<div class="col-md">
						<label class="form-label required">距離</label>
						<span style="padding: 0px 20px;">
							<input type="hidden" name="distance-about" value="precise">
							<input type="checkbox" name="distance-about" id="distance-about" value="about" <?php if ($_SESSION['edit-forms'][CFG_STAGE_ID]['distance-about'] == 'about') {echo 'checked';} ?>>
							<label class="form-label">約</label>
						</span>
						<input type="number" id="distance" class="form-control withunit" name="distance" value="<?php echo $_SESSION['edit-forms'][CFG_STAGE_ID]['distance']; ?>"><div class="unit"><p>km</p></div>
					</div>
					<div class="col-md">
						<label class="form-label required">起伏</label>
						<select class="form-select" id="terrain" name="terrain">
							<option <?php if($_SESSION['edit-forms'][CFG_STAGE_ID]['terrain'] == '1') {echo 'selected';} ?> value="1">平坦</option>
							<option <?php if($_SESSION['edit-forms'][CFG_STAGE_ID]['terrain'] == '2') {echo 'selected';} ?> value="2">多少の坂あり</option>
							<option <?php if($_SESSION['edit-forms'][CFG_STAGE_ID]['terrain'] == '3') {echo 'selected';} ?> value="3">坂あり</option>
							<option <?php if($_SESSION['edit-forms'][CFG_STAGE_ID]['terrain'] == '4') {echo 'selected';} ?> value="4">山岳</option>
						</select>
					</div>
				</div>
				<div class="mb-3">
					<label class="form-label required">コース紹介</label>
					<textarea class="form-control" id="course-description" name="course-description"><?php echo $_SESSION['edit-forms'][CFG_STAGE_ID]['course-description']; ?></textarea>
				</div>
				
				<div class="btn-container">
					<a href="<?= $previous_page ?>">
						<button type="button" class="btn button btnleft">戻る</button>
					</a>	
					<button type="submit" id="next" class="btn button btnright btn-primary" name="next">保存して進む</button>
				</div>
			</div>
		</div>

		<!-- Draw method -->
		<div id="js-draw" style="display: none;">
			<div class="rd-course-map-container">
				<div class="rd-course-map" id="newDrawMap" class="mb-3"></div>
				<div id="profileBox" class="rd-course-profile p-0">
					<canvas id="elevationProfile"></canvas>
				</div>
			</div>
			<div class="rd-course-fields"> <?php
				$routes = $connected_user->getRoutes(0, 100); ?>				
				<div class="mb-3">
					<label class="form-label required">自分のルートの中から選ぶ</label>
					<select class="form-select" id="selectRoute" name="my-routes">
						<option selected hidden disabled value="none">ルートを選ぶ...</option> <?php
						forEach ($routes as $route) { ?>
							<option value="<?= $route['id'] ?>"> <?php
								$posting_date = date_format(new Datetime($route['posting_date']), 'Y/m/d');
								echo $posting_date. ' - ' .$route['name']. ' (' .round($route['distance'], 1). 'km)' ?>
							</option> <?php
						} ?>
					</select>
				</div>
				<div class="mb-3 row g-2">
					<div class="col-md">
						<label class="form-label">距離 : <div class="rd-automatic-field" id="distanceDiv"></div></label>
					</div>
					<div class="col-md">
						<label class="form-label">起伏 : <div class="rd-automatic-field" id="terrainDiv"></div></label>
					</div>
				</div>
				<div class="mb-3">
					<label class="form-label required">コース紹介</label>
					<textarea class="form-control" id="courseDescriptionTextarea"><?= br2nl($_SESSION['edit-forms'][CFG_STAGE_ID]['course-description']); ?></textarea>
				</div>
				
				<div class="btn-container">
					<a href="<?= $previous_page ?>">
						<button type="button" class="btn button btnleft">Back</button>
					</a>	
					<button type="submit" id="next" class="btn button btnright btn-primary" name="next">保存して進む</button>
				</div>
			</div>
		</div>
	</form>
</div>

<script src="/scripts/map/vendor.js"></script>
<script type="module" src="/scripts/rides/edit/course.js"></script>