<?php
namespace OCA\Sharing_Group\Controller;

use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\StreamResponse;
use OCP\IRequest;

class HahaController extends Controller{
	public function __construct($appName, IRequest $request) {
		parent::__construct($appName, $request);
	}
    
    public function download(){
        $resp = new StreamResponse('/tmp/admin.csv');

        $resp->addHeader('Content-Disposition', 'attachment; filename="owncloud.log"');

        return $resp;
    }
}
