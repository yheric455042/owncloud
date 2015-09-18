<?php
/**
 * ownCloud - sharing_group
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Eric <eric.y@inwinstack.com>
 * @copyright Eric 2015
 */

/**
 * Create your routes in here. The name is the lowercase name of the controller
 * without the controller part, the stuff after the hash is the method.
 * e.g. page#index -> OCA\Sharing_Group\Controller\PageController->index()
 *
 * The controller class has to be registered in the application.php file since
 * it's instantiated in there
 */

namespace OCA\Sharing_Group\AppInfo;

$application = new Application();

$application->registerRoutes($this,[
    'routes' => [
	   ['name' => 'page#index', 'url' => '/', 'verb' => 'GET'],
	   ['name' => 'page#do_echo', 'url' => '/echo', 'verb' => 'POST'],
	   ['name' => 'user#index', 'url' => '/user', 'verb' => 'GET'],
	   ['name' => 'SharingGroups#fetch', 'url' => '/fetch', 'verb' => 'GET'],
	   ['name' => 'SharingGroups#create', 'url' => '/create', 'verb' => 'POST'],
	   ['name' => 'SharingGroups#findID', 'url' => '/findID', 'verb' => 'POST'],
	   ['name' => 'SharingGroups#addUserToGroup', 'url' => '/addUserToGroup', 'verb' => 'POST'],
	   ['name' => 'SharingGroups#renameGroup', 'url' => '/renameGroup', 'verb' => 'POST'],
	   ['name' => 'SharingGroups#deleteGroup', 'url' => '/deleteGroup', 'verb' => 'POST'],
	   ['name' => 'SharingGroups#importGroup', 'url' => '/importGroup', 'verb' => 'POST'],
	   ['name' => 'SharingGroups#export', 'url' => '/export', 'verb' => 'GET'],
	   ['name' => 'SharingGroups#removeUserFromGroup', 'url' => '/removeUserFromGroup', 'verb' => 'POST']
    ]
]);