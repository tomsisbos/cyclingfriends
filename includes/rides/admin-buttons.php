
<a href="/ride/<?= $ride->id ?>/admin">
	<button class="mp-button admin" type="button">管理</button>
</a>
<a href="/ride/<?= $ride->id ?>/edit">
	<button class="mp-button admin" type="button">編集</button>
</a>
<button class="mp-button danger js-delete-ride" data-id="<?= $ride->id ?>" type="button">削除</button>
	
<script src="/scripts/rides/delete.js" defer></script>