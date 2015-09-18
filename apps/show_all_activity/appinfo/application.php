<?php
namespace OCA\Show_All_Activity\AppInfo;

use \OCP\AppFramework\App;
use OCP\IContainer;
use \OCA\Show_All_Activity\Controller\Show;

class Application extends App {

    public function __construct(array $urlParams=array()){
            parent::__construct('show_all_activity', $urlParams);

            $container = $this->getContainer();

            $container->registerService('ActivityApplication', function($c){
                return new \OCA\Activity\AppInfo\Application();
            });

            
            $container->registerService('ShowController', function($c){
                return new Show(
                    $c->query('AppName'),
                    $c->query('Request'),
                    $c->query('ActivityApplication')->getContainer()->query('ActivityData'),
                    $c->query('ActivityApplication')->getContainer()->query('GroupHelper'),
                    $c->query('ActivityApplication')->getContainer()->query('UserSettings'),
                    $c->query('ActivityApplication')->getContainer()->query('DisplayHelper')
                );
            });
    }
}
