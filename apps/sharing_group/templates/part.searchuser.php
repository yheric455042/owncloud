<div id="controls">
    <input type="checkbox" id="checkuser">
    <button type="button" id="checkall" >All</button>
    <button type="button" id="clearall">Clear</button>
    <button type="button" id="inverse">Inverse</button>
    <button type="button" id="mutigroupselect">Submit</button>
    

    <!--
    <form id="groupsselectform"> 
        <button type="button" id="groupsGroupsSubmit">Submit</button>
    </form>
    --!>
  <!--  
    <form id="newuser" autocomplete="off">
		<select class="groupsselect" id="groupsselect" data-placeholder="groups" title="<?php //p($l->t('Groups'))?>" >
		</select>
		<select class="groupsselect" id="groupsselect_r" data-placeholder="groups" title="<?php //p($l->t('Groups'))?>">
		</select>
	</form>
	--!>
    <form autocomplete="off" id="usersearchform">
		<input type="text" class="input userFilter" placeholder="<?php p($l->t('Search Users')); ?>" />
	</form>
</div>
