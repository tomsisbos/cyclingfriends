<?php

	// Check if user is already participating
	if (!$ride->isParticipating($connected_user)) {
		
		// If entries are open, display entry infos
		if ($ride->isOpen() == 'open') {
			
			// If ride is full, display a message
			if ($ride->isFull()) { ?>
				<p class="bold text-danger">このライドは定員に達しました！誰かがキャンセルすることを待つか、他のライドにエントリーしてみましょう。</p> <?php
			// Else, display Join button
			} else { ?>
				<button id="join" class="mp-button success">参加する</button> <?php 
			}
			
		// If entries are not open, display a text message instead of button
		} else if ($ride->isOpen() == 'not yet') { ?>
			<div class="tag-light"><div class="bold text-danger">エントリー期間はまだ開始していません。<?= $ride->entry_start ?>から開始される予定です。</div></div> <?php
		} else if ($ride->isOpen() == 'closed') { ?>
			<div class="tag-light"><div class="bold text-danger">エントリー期間が終了しました。<a href="/rides">他のライド</a>に参加してみましょう !</div></div> <?php
		} 
		
	// Else, display Quit button
	} else { ?>
		<button id="rd-quit" class="mp-button danger">キャンセルする</button> <?php
	}
	
// Script ?>

<script type="module" src="/scripts/rides/join.js"></script>