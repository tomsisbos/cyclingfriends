<a href="/activity/<?= $activity->id ?>/edit">
	<button class="mp-button admin" type="button" name="edit">編集</button>
</a>
<button class="mp-button danger" data-id="<?= $activity->id ?>" type="button" name="delete" id="deleteButton">削除</button>

<script src="/scripts/activities/delete.js"></script>