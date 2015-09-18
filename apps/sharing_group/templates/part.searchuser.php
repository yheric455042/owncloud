<div id="controls">
    <form id="newuser" autocomplete="off">
		<select class="groupsselect" id="groupsselect" data-placeholder="groups" title="<?php p($l->t('Groups'))?>" >
		</select>
		<select class="groupsselect" id="groupsselect_r" data-placeholder="groups" title="<?php p($l->t('Groups'))?>">
		</select>
	</form>
	
    <form autocomplete="off" id="usersearchform">
		<input type="text" class="input userFilter" placeholder="<?php p($l->t('Search Users')); ?>" />
	</form>
</div>
