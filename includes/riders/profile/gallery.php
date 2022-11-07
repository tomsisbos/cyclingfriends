<?php 

$photos = $user->getProfileGallery(); ?>

<!-- Profile page form
Only display the picture if a corresponding blob exists in the database -->


<div id="gallery" class="gallery d-flex margin-bottom justify-content-around">

  <?php
  for ($i = 0; $i < count($photos); $i++) {
    $photo = new UserImage($photos[$i]['id']); ?>
    <div class="column">
      <img src="<?= 'data:image/jpeg;base64,' . base64_encode($photo->blob) ?>" style="width:100%" thumbnailId="<?= $i+1 ?>" class="js-clickable-thumbnail hover-shadow cursor">
    </div> <?php
  } ?>

</div>

<!-- Modal image
Only display currently selected thumbnail picture, if a corresponding blob exists in the database -->

<div id="myModal" class="modal">
  <span class="close cursor" onclick="closeModal()">&times;</span>
  <div class="modal-block"> <?php

    for ($i = 0; $i < count($photos); $i++) {
      $photo = new UserImage($photos[$i]['id']); ?>
      <div class="mySlides" id="<?= $photo->number ?>">
        <div class="numbertext"><?= ($i+1). ' / ' .count($photos); ?></div>
        <img src="<?= 'data:image/jpeg;base64,' . base64_encode($photo->blob) ?>">
        <div class="name-container">
          <p id="name"><?= $photo->caption ?></p>
        </div>
      </div> <?php
    } ?>
    
    <a class="prev nav-link">&#10094;</a>
    <a class="next nav-link">&#10095;</a>

    <!-- Modal thumbnails
    Only display the picture if a corresponding blob exists in the database
    If has admin rights, displays an editable text input with current caption as default value for editing -->

    <div class="columns-container justify-content-around <?php if ($user == $connected_user) { echo 'bg-admin'; } ?>"> <?php
      for ($i = 0; $i < count($photos); $i++) {
        $photo = new UserImage($photos[$i]['id']); ?>
        <div class="column">
          <img class="demo cursor" src="<?= 'data:image/jpeg;base64,' . base64_encode($photo->blob) ?>" demoId="<?= $i+1 ?>"alt="<?= $photo->caption ?>"> <?php
          if ($user == $connected_user) { ?>
            <div class="lightbox-admin-panel container-admin">
              <input type="text" class="admin-field column-field form-control" name="input<?= $i ?>" placeholder="Write a caption..." value="<?= $photo->caption ?>">
            </div> <?php
          } ?>
        </div> <?php
      } ?>
    </div>
    
  </div>
</div>

<script>

</script>