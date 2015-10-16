<?php
/**
 * ownCloud - sharing_group
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author simon <simon.l@inwinstack.com>
 * @copyright simon 2015
 */

namespace OCA\Sharing_Group\Controller;

use OCP\IRequest;
use OC\Settings\Controller\UsersController;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\Controller;
use \OCP\IUser;
use OC\Settings\Factory\SubAdminFactory;
use OC\AppFramework\Http;
use OCP\App\IAppManager;
use OCP\IConfig;
use OCP\IGroupManager;
use OCP\IL10N;
use OCP\ILogger;
use OCP\IURLGenerator;
use OCP\IUserManager;
use OCP\IUserSession;
use OCP\Mail\IMailer;
use OCA\Sharing_Group\Data;
use OCP\User;


class UserController extends UsersController {

    private $l10n;
	/** @var IUserSession */
	private $userSession;
	/** @var bool */
	private $isAdmin;
	/** @var IUserManager */
	private $userManager;
	/** @var IGroupManager */
	private $groupManager;
	/** @var IConfig */
	private $config;
	/** @var ILogger */
	private $log;
	/** @var \OC_Defaults */
	private $defaults;
	/** @var IMailer */
	private $mailer;
	/** @var string */
	private $fromMailAddress;
	/** @var IURLGenerator */
	private $urlGenerator;
	/** @var bool contains the state of the encryption app */
	private $isEncryptionAppEnabled;
	/** @var bool contains the state of the admin recovery setting */
	private $isRestoreEnabled = false;
	/** @var SubAdminFactory */
	private $subAdminFactory;

    /**
     * @param IUser $user
     * @param array $userGroups
     * @return array
     */

	public function __construct($appName,
								IRequest $request,
								IUserManager $userManager,
								IGroupManager $groupManager,
								IUserSession $userSession,
								IConfig $config,
								$isAdmin,
								IL10N $l10n,
								ILogger $log,
								\OC_Defaults $defaults,
								IMailer $mailer,
								$fromMailAddress,
								IURLGenerator $urlGenerator,
								IAppManager $appManager,
								SubAdminFactory $subAdminFactory) {
		parent::__construct($appName, $request, $userManager, $groupManager, $userSession, $config, $isAdmin, $l10n, $log, $defaults, $mailer, $fromMailAddress, $urlGenerator, $appManager, $subAdminFactory);
		$this->userManager = $userManager;
		$this->groupManager = $groupManager;
		$this->userSession = $userSession;
		$this->config = $config;
		$this->isAdmin = $isAdmin;
		$this->l10n = $l10n;
		$this->log = $log;
		$this->defaults = $defaults;
		$this->mailer = $mailer;
		$this->fromMailAddress = $fromMailAddress;
		$this->urlGenerator = $urlGenerator;
		$this->subAdminFactory = $subAdminFactory;

		// check for encryption state - TODO see formatUserForIndex
		$this->isEncryptionAppEnabled = $appManager->isEnabledForUser('encryption');
		if($this->isEncryptionAppEnabled) {
			// putting this directly in empty is possible in PHP 5.5+
			$result = $config->getAppValue('encryption', 'recoveryAdminEnabled', 0);
			$this->isRestoreEnabled = !empty($result);
		}
	}
   

    private function formatUserForIndex(IUser $user, array $userGroups = null){
        return [
            'name' => $user->getUID()
        ];
    }

   /**
    * @NoAdminRequired
    *
    * @param int $offset
    * @param int $limit
    * @param string $gid GID to filter for
    * @param string $pattern Pattern to search for in the username
    * @param string $backend Backend to filter for (class-name)
    * @return DataResponse
    *
    * TODO: Tidy up and write unit tests - code is mainly static method calls
    */
   
   public function index($offset = 0, $limit = 10, $gid = '', $pattern = '', $backend = '') {
       // FIXME: The JS sends the group '_everyone' instead of no GID for the "all users" group.
       if($gid === '_everyone') {
           $gid = '';
       }

       // Remove backends
       if(!empty($backend)) {
           $activeBackends = $this->userManager->getBackends();
           $this->userManager->clearBackends();
           foreach($activeBackends as $singleActiveBackend) {
               if($backend === get_class($singleActiveBackend)) {
                   $this->userManager->registerBackend($singleActiveBackend);
                   break;
               }
           }
       }

       $users = [];
       //if ($this->isAdmin) {

       if($gid !== '') {
           
            $users = Data::readGroupUsers($gid);
            $users =array_diff( $users , array($user)); 
            $users = array_slice($users, 0);
           
        } else {
            $users = Data::readAllUsers();
            
            $user = User::getUser();
            /*$key = array_search($user,$users);
            if($key!=FALSE)
                unset( $users[$key]);
            */
            //$batch = $this->userManager->search($pattern, $limit, $offset);
            $users =array_diff( $users , array($user)); 
            $users = array_slice($users, 0);
            //file_put_contents('test.txt', print_r($users, true)); 
        }
            /*
           foreach ($batch as $user) {
               $users[] = $this->formatUserForIndex($user);
           }
            */
       /*} else {
           $subAdminOfGroups = $this->subAdminFactory->getSubAdminsOfGroups(
               $this->userSession->getUser()->getUID()
           );
           // Set the $gid parameter to an empty value if the subadmin has no rights to access a specific group
           if($gid !== '' && !in_array($gid, $subAdminOfGroups)) {
               $gid = '';
           }

           // Batch all groups the user is subadmin of when a group is specified
           $batch = [];
           if($gid === '') {
               foreach($subAdminOfGroups as $group) {
                   $groupUsers = $this->groupManager->displayNamesInGroup($group, $pattern, $limit, $offset);
                   foreach($groupUsers as $uid => $displayName) {
                       $batch[$uid] = $displayName;
                   }
               }
           } else {
               $batch = $this->groupManager->displayNamesInGroup($gid, $pattern, $limit, $offset);
           }
           $batch = $this->getUsersForUID($batch);

           foreach ($batch as $user) {
               // Only add the groups, this user is a subadmin of
               $userGroups = array_values(array_intersect(
                   $this->groupManager->getUserGroupIds($user),
                   $subAdminOfGroups
               ));
               $users[] = $this->formatUserForIndex($user, $userGroups);
           }
       }
        */
       return new DataResponse($users);
   }

}
