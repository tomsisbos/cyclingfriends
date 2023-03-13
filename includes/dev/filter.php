<form method="POST" class="mb-3 row g-2">
    <div class="tr-row flex-column">
        <div>
            <label class="form-label">表示期間</label>
        </div>
        <div class="d-flex flex-wrap">
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
    </div>
    <div class="tr-row">
        <div class="td-row">
            <label for="filter_type">表示タイプ</label>
            <select class="form-select" name="filter_type" onChange="this.form.submit();">
                <option value="all">全て</option>
                <option value="bug" <?php if(isset($_POST['filter_type']) AND $_POST['filter_type'] == 'bug'){echo 'selected';} ?>>バグ</option>
                <option value="opinion" <?php if(isset($_POST['filter_type']) AND $_POST['filter_type'] == 'opinion'){echo 'selected';} ?>>意見</option>
                <option value="proposal" <?php if(isset($_POST['filter_type']) AND $_POST['filter_type'] == 'proposal'){echo 'selected';} ?>>提案</option>
                <option value="other" <?php if(isset($_POST['filter_type']) AND $_POST['filter_type'] == 'other'){echo 'selected';} ?>>その他</option>
            </select>
        </div>
        <div class="td-row">
            <label for="filter_search">タイトル</label>
            <input class="form-control" type="text" id="filter_search" name="filter_search" onChange="this.form.submit();" value="<?php if (isset($_POST['filter_title'])) echo $_POST['filter_title'] ?>" />
        </div>
    </div>
</form>