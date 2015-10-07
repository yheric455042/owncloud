<?php

script('sharing_group', [
    'users',
    'groups',
    'filter',
]);

script('files',[
    'jquery.fileupload'
]);

script('core', [
    'oc-dialogs',
    'multiselect',
    'singleselect'
]);
style('sharing_group', [
    'sharing_group',
    'style',
]);


$userlistParams = array();
$allGroups=array();
/*foreach($_["groups"] as $group) {
    $allGroups[] = $group['name'];
}
foreach($_["adminGroup"] as $group) {
    $allGroups[] = $group['name'];
}
$userlistParams['subadmingroups'] = $allGroups;
$userlistParams['allGroups'] = json_encode($allGroups);
$items = array_flip($userlistParams['subadmingroups']);
unset($items['admin']);
$userlistParams['subadmingroups'] = array_flip($items);

translation('settings');
*/
?>

<div id="app">
	<div id="app-navigation">
		<?php print_unescaped($this->inc('part.grouplist')); ?>
		<?php print_unescaped($this->inc('part.settings')); ?>
	</div>

	<div id="app-content">
		<div id="app-content-wrapper">
			<?php print_unescaped($this->inc('part.searchuser')); ?>
			<?php print_unescaped($this->inc('part.userlist')); ?>
		</div>
	</div>
</div>
