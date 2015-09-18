<?php

/**
 * ownCloud - Updater plugin
 *
 * @author Victor Dubiniuk
 * @copyright 2014 Victor Dubiniuk victor.dubiniuk@gmail.com
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 */

namespace OCA\Updater;

class App {

	const APP_ID = 'updater';

	/**
	 * @var \OC_L10N
	 */
	public static $l10n;
	
	public static function init() {
		self::$l10n = \OCP\Util::getL10N(self::APP_ID);
		//Allow config page
		\OCP\App::registerAdmin(self::APP_ID, 'admin');
	}
	
	public static function flushCache(){
		\OC::$server->getConfig()->setAppValue('core', 'lastupdatedat', 0);
	}
	
	public static function getFeed($helper = false, $config = false){
		if (!$helper){
			$helper = \OC::$server->getHTTPHelper();
		}
		if (!$config){
			$config = \OC::$server->getConfig();
		}
		$updater = new \OC\Updater($helper, $config);
		$data = $updater->check('https://updates.owncloud.com/server/');
		if (!is_array($data)){
			$data = array();
		}
		return $data;
	}

	/**
	 * Get app working directory
	 * @return string
	 */
	public static function getBackupBase() {
		return \OCP\Config::getSystemValue("datadirectory", \OC::$SERVERROOT . "/data") . '/updater_backup/';
	}
	
	public static function getTempBase(){
		return \OC::$SERVERROOT . "/_oc-upgrade/";
	}
	
	public static function log($message, $level= \OCP\Util::ERROR) {
		\OCP\Util::writeLog(self::APP_ID, $message, $level);
	}
}
