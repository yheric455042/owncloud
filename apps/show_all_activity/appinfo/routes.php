<?php
/**
 * ownCloud - show_all_activity
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author eric <eric.y@inwinstack.com>
 * @copyright eric 2015
 */

/**
 * Create your routes in here. The name is the lowercase name of the controller
 * without the controller part, the stuff after the hash is the method.
 * e.g. page#index -> OCA\Show_All_Activity\Controller\PageController->index()
 *
 * The controller class has to be registered in the application.php file since
 * it's instantiated in there
 */
namespace OCA\Show_All_Activity\AppInfo;

$this->create('test_ajax', 'ajax/test.php')
	->actionInclude('show_all_activity/ajax/test.php');


$application = new Application();
$application->registerRoutes($this, [
    'routes' => [
       ['name' => 'Show#fetch','url' => '/fetch', 'verb' => 'GET']
    ]
]);
