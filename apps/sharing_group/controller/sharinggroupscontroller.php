<?php
namespace OCA\Sharing_Group\Controller;

use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\Http\DownloadResponse;
use OCP\AppFramework\Http\DataDownloadResponse;
use OCP\AppFramework\Http\StreamResponse;
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
    
    public function getCategory($filter = '') {

        $result = $this->data->readForSearchlist($this->user , $filter);

        return new DataResponse($result); 
    }
  
    public function findID($name){
        $result = $this->data->findGroupByName($name);

        return new JSONResponse($result);
    }

    public function create($name){
        $check = $this->data->findGroupByName($name);
        
        if(sizeof($check) == 0){
            $result = $this->data->createGroups($name);
            return new JSONResponse($result);
        }
        return new JSONResponse(false);
    }
  
    public function deleteGroup($gid){
        
        $result = $this->data->deleteGroup($gid);
        
        return new JSONResponse($result);
    }
    
    public function importGroup($data){
        $files = $this->request->getUploadedFile('fileToUpload'); 
        $data = file_get_contents($files['tmp_name']);
        
        //file_put_contents('123.txt', $data);
        
        $result = $this->data->importGroup($data);
        //return new JSONResponse($result);
    } 
    
    /**
     * @NoCSRFRequired
     */
    public function export(){
        $data = $this->data->export();
        $fileName = User::getUser() . ".csv";
        //$filePath = "/tmp/". $fileName;
        //header("Content-Disposition: attachment; filename= $fileName");
        //header("Content-Transfer-Encoding: Binary");
        //header("Content-Type: application/force-download");
        //readfile($filePath);
        //$fp = fopen() 
        
        $Download = new DataDownloadResponse($data , $fileName, 'text/csv');
        //$Download->addHeader("Content-Type: application/force-download");
        //readfile($filePath);
        
        //$Download = new StreamResponse($filePath);
        //$Download->addHeader('Content-Disposition', "attachment; filename= $fileName");
        
        return $Download;
   }

    public function renameGroup($gid, $newname){
        $check = $this->data->findGroupByName($newname);
        
        if(sizeof($check) == 0){
            $result = $this->data->renameGroup($gid, $newname);
            return new JSONResponse($result);
        }
    }

    public function removeUserFromGroup($gid, $uids){
        $this->data->removeUserFromGroup($gid, $uids);

        return true;
    }
    
    public function addUserToGroup($gid, $uids){
        $this->data->addUserToGroup($gid, $uids); 
        
        return true;
    }

    public function fetch($id = '') {
        $result = $this->data->findGroupById($id, $this->user);
        //$result = count($result) === 1 ? $result : $result;
        return new JSONResponse($result);
    }
}
?>
