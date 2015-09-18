<?php
$contact =  new \OCA\Contacts\Dispatcher();
$share_groups = $contact->dispatch('GroupController', 'getGroups');
echo $share_groups;
?>
