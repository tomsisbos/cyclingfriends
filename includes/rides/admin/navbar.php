<div class="container navbar rd-ad-navbar navbar-expand-lg">

	<button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#collapsedRideAdminMenu">
		<span class="navbar-toggler-icon"></span>
	</button>
	<div class="collapse navbar-collapse" id="collapsedRideAdminMenu">
		<ul class="navbar-nav cf-navbar">
			<li class="nav-item">
				<a class="nav-link" href="/ride/<?= $ride->id ?>/admin/entries">エントリーリスト</a>
			</li>
			<li class="nav-item">
				<a class="nav-link" href="/ride/<?= $ride->id ?>/admin/forms">質問項目</a>
			</li> <?php
			if ($connected_user->isGuide()) { ?>
				<li class="nav-item">
					<a class="nav-link" href="/ride/<?= $ride->id ?>/admin/guides">ガイド</a>
				</li>
				<li class="nav-item">
					<a class="nav-link" href="/ride/<?= $ride->id ?>/admin/report">レポート</a>
				</li> <?php
			} ?>
		</ul>
	</div>
		
</div>


