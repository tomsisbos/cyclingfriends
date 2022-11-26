<!DOCTYPE html>
<html lang="en">

<?php 
session_start();
include '../actions/users/securityAction.php';
?>

<body>

	<?php include '../includes/navbar.php'; ?>

	<div class="main">
		
		<h2 class="top-title">Neighbours</h2>
		
		<div class="container">
		
			<!-- Filter options --->
			<?php // include 'includes/riders/neighbours/filter-options.php'; ?>
			
		</div>
		
			<?php 
			// Select riders from database according to filter queries
			include '../actions/riders/displayNeighboursAction.php'; ?>
			
		<div class="container end bg-white">
			
			<div class="tr-row justify th-row">
				<div class="td-row element-8">
				</div>
				<div class="td-row element-30 justify-center">
					Login
				</div>
				<div class="td-row element-30">
					Place
				</div>
				<div class="td-row element-10 justify-center">
					Age
				</div>
				<div class="td-row element-10 justify-center">
					Gender
				</div>
				<div class="td-row element-10 justify-center">
					Level
				</div>
				<div class="td-row element-15 justify-center">
					Bike
				</div>
			</div>

			<!-- Displays all rides within a t-row with necessary infos data -->
		
			<?php
			if($getRiders->rowCount() > 0){
				while($rider = $getRiders->fetch()){
					$rider = new User ($rider['id']); ?>
		
				<div class="tr-row justify smallfont">
					<div class="td-row element-8">
						<a href="/rider/<?= $rider->id ?>"><?php $rider->displayPropic(60, 60, 60); ?></a>
					</div>
					<div class="td-row element-30">
					<a href="<?= 'rider/' .$rider->id;?>" class="fullwidth">
						<?php // Truncate rider login if more than 40 characters
							$rider_login_truncated = truncate($rider->login, 0, 40); ?>
							<button class="btn button fullwidth"><?= $rider_login_truncated ?></button>
						</a>
					</div>
					<div class="td-row element-30 bg-lightgrey">
						<?= $rider->place; ?>
					</div>
					<div class="td-row element-10 bg-lightgrey justify-center">
						<?php if($rider->birthdate){ echo $rider->calculateAge(). ' years old';} ?>
					</div>
					<div class="td-row element-10 bg-lightgrey justify-center">
						<?= $rider->gender; ?>
					</div>
					<div class="td-row element-10 bg-lightgrey justify-center">
						<?= $rider->level; ?>
					</div>
					<div class="td-row element-15 bg-lightgrey justify-center text-center">
						<?php if ($bikes = $rider->getBikes()) {
							$firstBike = new Bike ($bikes[0]['id']);
							echo $firstBike->type;
							if ($firstBike->model) {
								echo '<br>(' .$firstBike->model. ')';
							}
						} ?>
					</div>
				</div>
					
			<?php
				}
			}else{
				
				$errormessage = 'There is no rider to display.'; ?>
							
				<?php if(isset($errormessage)){
					echo '<div class="error-block fullwidth text-center"><p class="error-message">' .$errormessage. '</p></div>'; 
				} ?> 
				
			<?php
			}
			?>
			
		</div>
	
	</div>
	
</body>
</html>