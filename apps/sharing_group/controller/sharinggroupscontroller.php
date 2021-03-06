<?php
namespace OCA\Sharing_Group\Controller;

use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\Http\DownloadResponse;
use OCP\AppFramework\Http\DataDownloadResponse;
use OCP\AppFramework\Http\StreamResponse;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\AppFramework\Http\JSONResponse;
use OCA\Sharing_Group\Data;
use OCP\IRequest;
use OCP\User;

class SharingGroupsController extends Controller{

   	protected $data;
    protected $user;

	public function __construct($appName, IRequest $request, Data $data, $user) {
		parent::__construct($appName, $request);
		$this->data = $data;
		$this->user = $user;
	}
    
    /**
     * @NoAdminRequired
     */
    public function getCategory($filter = '') {

        $result = $this->data->readForSearchlist($this->user , $filter);

        return new DataResponse($result); 
    }
    
    /**
     * @NoAdminRequired
     */
    public function countGroupUsers($gid){
        $count = $this->data->readGroupUsers($gid);
        //file_put_contents('123.txt', print_r($result, true));
        $result = sizeof($count);
        return new JSONResponse($result);
    }

    /**
     * @NoAdminRequired
     */
    public function controlGroupUser($multigroup){
        //var_dump($mutigroup);
        
        //file_put_contents('1234.txt',print_r($multigroup,true));
        foreach($multigroup as $gid => $action) {
            $temp = [];
            $action = explode(':',$action);
            $users = explode(',',$action[1]);
            $temp[$action[0]] = $users;
            $multigroup[$gid] = $temp;
        }
        //file_put_contents('123.txt',print_r($multigroup,true));
        if($multigroup != null) {
            
            $result = $this->data->controlGroupUser($multigroup);
            return new JSONResponse($result);
        }
        return new JSONResponse();
        
    }
    
    /**
     * @NoAdminRequired
     */
    public function findID($name){
        $result = $this->data->findGroupByName($name);
        return new JSONResponse($result);
    }
    
    /**
     * @NoAdminRequired
     */
    public function create($name){
        $check = $this->data->findGroupByName($name);
        
        if(sizeof($check) == 0){
            $result = $this->data->createGroups($name);
            return new JSONResponse($result);
        }
        return new JSONResponse(false);
    }
    
    /**
     * @NoAdminRequired
     */
    public function deleteGroup($gid){
        
        $result = $this->data->deleteGroup($gid);
        
        return new JSONResponse($result);
    }
     
    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     */
    public function importGroup($data){
        $files = $this->request->getUploadedFile('fileToUpload'); 
        $data = file_get_contents($files['tmp_name']);
        
        //file_put_contents('123.txt', $data);
        
        $result = $this->data->importGroup($data);
        return new JSONResponse($result);
    } 
    
    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     */
    public function export(){
        $data = $this->data->export();
        $fileName = User::getUser() . ".csv";
        $Download = new DataDownloadResponse($data , $fileName, 'text/csv');
        
        return $Download;
    }
    
    /**
     * @NoAdminRequired
     */
    public function renameGroup($gid, $newname){
        $check = $this->data->findGroupByName($newname);
        
        if(sizeof($check) == 0){
            $result = $this->data->renameGroup($gid, $newname);
            return new JSONResponse($result);
        }
    }
    
    /**
     * @NoAdminRequired
     */
    public function removeUserFromGroup($gid, $uids){
        $this->data->removeUserFromGroup($gid, $uids);

        return true;
    }
    
    /**
     * @NoAdminRequired
     */
    public function getAllGroupsInfo(){
        $result = $this->data->getAllGroupInfo(); 
        //file_put_contents('123.txt', print_r($result, true)); 
        return new JSONResponse($result);
    }
    
    /**
     * @NoAdminRequired
     */
    public function addUserToGroup($gid, $uids){
        $this->data->addUserToGroup($gid, $uids); 
        
        return true;
    }

    /**
     * @NoAdminRequired
     */
    public function fetch($id = '') {
        $result = $this->data->findGroupById($id, $this->user);
        
        //file_put_contents('123.txt', print_r($result,true));
        //$result = count($result) === 1 ? $result : $result;
        return new JSONResponse($result);
    }

    /**
     * @NoAdminRequired
     */

    public function fetchAll() {
        $result = $this->data->findAllGroup();
        
        return new JSONResponse($result);
    }

}
?>
