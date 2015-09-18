<?php
namespace OCA\Show_All_Activity\Controller;

use OCP\AppFramework\Http\Templateresponse;
use OCP\AppFramework\Controller;
use OCP\IRequest;
use \OCA\Activity\Display;
use \OCA\Activity\UserSettings;
use \OCA\Activity\GroupHelper;
use \OCA\Activity\Data;

class Show extends Controller{
    const DEFAULT_PAGE_SIZE = 30;
    
    protected $data;

    protected $display;

    protected $settings;

    protected $helper;

     

    public function __construct($AppName, IRequest $request, Data $data, GroupHelper $helper, UserSettings $settings, DisplayHelper $display){
		parent::__construct($AppName, $request);
        $this->data = $data;
        $this->helper = $helper;
        $this->settings = $settings;
        $this->display = $display;
	}
    


    public function fetch($user, $page){
        
        $pageoffset = $page - 1;

	    $template = new TemplateResponse('activity','stream.list',[
         'activity' => $this->data->read($this->helper, $this->settings, self::DEFAULT_PAGE_SIZE*$pageoffset, self::DEFAULT_PAGE_SIZE, 'all', $user),
         'displayHelper' => $this->display],'');

        return $template->render(); 
   }
}
?>
