
<a href="/activity/<?= $activity->id ?>/edit">
	<button class="btn button box-shadow" type="button" name="edit">編集</button>
</a>
<button class="btn button box-shadow" data-id="<?= $activity->id ?>" type="button" name="delete" id="deleteButton">削除</button>

<script src="/scripts/activities/delete.js"></script>