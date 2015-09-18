<?php
namespace OCA\Sharing_Group;

use OCP\DB;
use OCP\User;
use OCP\Util;

class Data{
    
    public function removeUserFromGroup($gid, $uids) {
        $user = User::getUser();
        $query = DB::prepare('DELETE FROM `*PREFIX*sharing_group_user` WHERE `gid` = ? AND `uid`= ? AND `owner` = ? ');
        
        foreach($uids as $uid) {
            $query->execute(array($gid, $uid, $user));
        }

    }

    public function addUserToGroup($gid, $uids) {
        $user = User::getUser();
        $query = DB::prepare('INSERT INTO `*PREFIX*sharing_group_user` (`gid`, `uid`, `owner`) VALUES(?, ?, ?)');
        
        foreach($uids as $uid) {
            $query->execute(array($gid, $uid, $user));
        }

    }

    public static function readGroups($user = '', $filter = '') {

        $user = $user !== '' ? $user : User::getUser();
        $query = DB::prepare('SELECT `id`, `name` FROM `*PREFIX*sharing_groups` WHERE `uid` = ?');
        $result = $query->execute(array($user));

        return self::getGroupsQueryResult($result, $filter);
    }
    
    public static function createGroups($name){
        $user = User::getUser();
        $sql = 'INSERT INTO `*PREFIX*sharing_groups` (`name`, `uid`) VALUES(?, ?)';
        $query = DB::prepare($sql);
        $result = $query->execute(array($name, $user));

        
        if (DB::isError($result)) {
			Util::writeLog('SharingGroup', DB::getErrorMessage($result), Util::ERROR);
        } else{
            return true;
        }
    }
    
    public static function deleteGroup($gid){
        $user = User::getUser();
        $sql = 'SELECT `id`, `item_type` FROM `*PREFIX*share` WHERE `share_type` = ? AND `share_with` = ?';
        $query = DB::prepare($sql);
        $check = $query->execute(array('7', $gid));
        $share_check = self::getQueryResult($check); 
        file_put_contents('123.txt', print_r($share_check ,true));     

        if($share_check != NULL ) {
            for($i = 0; $i <= count($share_check); $i++) {
                $share_id = $share_check[$i]['id'];
                $sql = 'DELETE FROM `*PREFIX*share` WHERE `id` = ? OR `parent` = ?';
                $query = DB::prepare($sql);
                $result = $query->execute(array($share_id, $share_id));
            }
        }

        $sql = 'DELETE FROM `*PREFIX*sharing_group_user` WHERE `gid` = ?';
        $query = DB::prepare($sql);
        $check = $query->execute(array($gid));
        
        $sql = 'DELETE FROM `*PREFIX*sharing_groups` WHERE `id` = ?';
        $query = DB::prepare($sql);
        $check = $query->execute(array($gid));
   }

    public static function renameGroup($gid, $newname){
        $user = User::getUser();
        $sql = 'UPDATE `*PREFIX*sharing_groups` SET `name` = ? WHERE `id` = ? AND `uid` = ?';
        $query = DB::prepare($sql);
        $result = $query->execute(array($newname, $gid, $user));
    }

    public static function findGroupByName($name){
        $user = User::getUser();
        $sql = 'SELECT `id` FROM `*PREFIX*sharing_groups` WHERE `name` = ? AND `uid` = ?';
        
        $query = DB::prepare($sql);
        $result = $query->execute(array($name, $user));
        return self::getGroupsQueryResult($result, '');

    }
    
    public static function importGroup($data){
        $user = User::getUser();
        $importdata = explode("\n", $data);
        for($i = 0; $i < sizeof($importdata); $i++){
            
            list($id, $group, $uid) = explode("," , $importdata[$i]);
            
            if($importdata[$i] != NULL){
                $importdata[$i] = array();
                $importdata[$i]['id'] = $id;
                $importdata[$i]['group'] = $group;
                $importdata[$i]['uid'] = $uid;
            }
        }
        
        file_put_contents("12345.txt", print_r($importdata,true));
    }
 
    public static function export(){
        $user = User::getUser();
        //$filePath = "/tmp/" . $user . ".csv";
        //$fileName = $user . ".csv";
        
        $sql = 'SELECT id, name , *PREFIX*sharing_group_user.uid FROM *PREFIX*sharing_groups LEFT OUTER JOIN *PREFIX*sharing_group_user ON *PREFIX*sharing_groups.id = *PREFIX*sharing_group_user.gid WHERE *PREFIX*sharing_groups.uid = ?';
        //$sql = 'SELECT id, name , *PREFIX*sharing_group_user.uid INTO OUTFILE ? FIELDS TERMINATED BY "," ENCLOSED BY "\"" LINES TERMINATED BY "\n" FROM *PREFIX*sharing_groups LEFT OUTER JOIN *PREFIX*sharing_group_user ON *PREFIX*sharing_groups.id = *PREFIX*sharing_group_user.gid WHERE *PREFIX*sharing_groups.uid = ?';
        $query = DB::prepare($sql);
        //$result = $query->execute(array($filePath, $user));
        $result = $query->execute(array($user));
        $data = self::getExportGroupsQueryResult($result); 
        //file_put_contents('1234.txt', print_r($data, true));
        
        //$result = file_get_contents();

        //array_push($data, )
        return $data;
    }

    public static function findGroupById($id = '', $user = '') {
        $user = ($user !== '') ? $user : User::getUser();
        $sql = $id ? 'SELECT `id` ,`name` FROM `*PREFIX*sharing_groups` WHERE `id` = ?' : 'SELECT `id` ,`name` FROM `*PREFIX*sharing_groups` WHERE `uid` = ?';
        
        $query = DB::prepare($sql);
        $input = $id ? $id : $user;
        $result = $query->execute(array($input));

        return self::getGroupsQueryResult($result, '');
    }
    
    public static function readAllUsers() {
        $sql = 'SELECT `uid` FROM `*PREFIX*users`';
        $query = DB::prepare($sql);
        $result = $query->execute();
        
        return self::getGroupUserQueryresult($result); 
    }

    public static function readGroupUsers($id) {
        $sql = 'SELECT `uid` FROM `*PREFIX*sharing_group_user` WHERE `gid` = ?';
        $query = DB::prepare($sql);
        $result = $query->execute(array($id));
        
        return self::getGroupUserQueryresult($result); 
    }

    public static function readUserGroups($user) {
        $query = DB::prepare('SELECT `gid` FROM `*PREFIX*sharing_group_user` WHERE `uid` = ?');
        $result = $query->execute(array($user));
        
        return self::getUserGroupQueryresult($result);
    }

    public static function getGroupName($id) {
        $query = DB::prepare('SELECT `name` FROM `*PREFIX*sharing_groups` WHERE `id` = ?');
        $result = $query->execute(array($id));

        if(DB::isError($result)) {
		    Util::writeLog('SharingGroup', DB::getErrorMessage($result), Util::ERROR);
            return;
        } 

        $row = $result->fetch();
        
        return $row !== null ? $row['name'] : null;
    }

    public static function inGroup($uid, $gid) {
        $query = DB::prepare('SELECT `uid` FROM `*PREFIX*sharing_group_user` WHERE `gid` = ? AND `uid` = ?');
        $result = $query->execute(array($gid,$uid));

        if(DB::isError($result)) {
		    Util::writeLog('SharingGroup', DB::getErrorMessage($result), Util::ERROR);
            return false;
        }

        return $result !== null;
    }

    private static function getGroupUserQueryresult($result) {
        $data = [];

        if(DB::isError($result)) {
			Util::writeLog('SharingGroup', DB::getErrorMessage($result), Util::ERROR);

            return;
        }

        while($row = $result->fetch()) {
            $data[] = $row['uid'];
        }

        return $data;
    }

    private static function getUserGroupQueryresult($result) {
        $data = [];

        if(DB::isError($result)) {
			Util::writeLog('SharingGroup', DB::getErrorMessage($result), Util::ERROR);

            return;
        }

        while($row = $result->fetch()) {
            $data[] = $row['gid'];
        }

        return $data;
    }

    private static function getGroupsQueryResult($result, $filter) {
        $data = [];

        if (DB::isError($result)) {
			Util::writeLog('SharingGroup', DB::getErrorMessage($result), Util::ERROR);
            
            return;
        }


        while ($row = $result->fetchRow()) {
            $group = array('id' => $row['id'], 'name' => $row['name']);
            $filter ? strstr($row['name'], $filter) && array_push($data, $group) : array_push($data, $group);
        }
        
        return $data;
    }

    private static function getExportGroupsQueryResult($result) {
        $string = "";
        
        if (DB::isError($result)) {
			Util::writeLog('SharingGroup', DB::getErrorMessage($result), Util::ERROR);
            
            return;
        }


        while ($row = $result->fetchRow()) {
            if($row['uid'] != NULL){
                $string .= '"' . $row['id'] . '", "' . $row['name'] . '", "' . $row['uid'] . '"' . "\n" ;
            }
            else {
                
                $string .= '"' . $row['id'] . '", "' . $row['name'] . '", \N' . "\n" ;
            }
        }
        
        return $string;
    }


     private static function getQueryResult($result) {
        $data = [];

        if (DB::isError($result)) {
			Util::writeLog('SharingGroup', DB::getErrorMessage($result), Util::ERROR);
            
            return;
        }
        

        while ($row = $result->fetchRow()) {
            $share = array('id' => $row['id'], 'item_type' => $row['item_type']);
            array_push($data, $share);
        }
        
        return $data;
    }

}

