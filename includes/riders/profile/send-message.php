<?php

// Message Modal ?>
<div id="messageModal" class="modal modal-small">
	<div id="messageModalBlock" class="modal-block modal-block-medium">
	    <span class="close cursor" onclick="closeModal()">&times;</span>
		<div class="container d-flex flex-column gap-20">
            <div class="d-flex align-items-center gap"> <?php
                $user->getPropicElement(60, 60, 60); ?>
                <h2> <?php
                    echo $user->login; ?>
                </h2>
            </div>
			<textarea class="form-control" placeholder="メッセージを送る..."></textarea>
            <button class="smallbutton btn m-0 push">Send</button>
		</div>
	</div>
</div>