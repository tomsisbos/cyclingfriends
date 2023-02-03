
<a href="/ride/<?= $ride->id ?>/admin">
	<button class="mp-button admin" type="button">管理</button>
</a>
<a href="/ride/<?= $ride->id ?>/edit">
	<button class="mp-button admin" type="button">編集</button>
</a>
<button class="mp-button danger" type="button" id="deleteButton">削除</button>
	
<script src="/scripts/rides/delete.js" defer></script>