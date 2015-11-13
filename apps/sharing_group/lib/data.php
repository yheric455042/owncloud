<?php
namespace OCA\Sharing_Group;

use OCP\DB;
use OCP\User;
use OCP\Util;

class Data{
    
    public function controlGroupUser($data) {
        $user = User::getUser();
        $sql_add = 'INSERT INTO `*PREFIX*sharing_group_user` (`gid`, `uid`, `owner`) VALUES';
        $sql_share = 'DELETE FROM `*PREFIX*share` WHERE (`parent` = ? ';
        $sql_remove = 'DELETE FROM `*PREFIX*sharing_group_user` WHERE (`gid` = ? ';
        $add_arr = [];
        $share_arr = [];
        $gids = [];
        $remove_arr = [];
        foreach($data as $gid => $action) {
            $checkuid = self::readGroupUsers($gid);
            $add = isset($action['add']) ? $action['add'] : [];
            $remove = isset($action['remove']) ? $action['remove'] : [];
            
            foreach($add as $uid){
                if(!in_array($uid,$checkuid)) {
                    $sql_add .= '(?, ?, ?) ,';
                    array_push($add_arr,$gid,$uid,$user);
                }
            }
            
            if(!empty($remove)) {
                $sql = 'SELECT `id` FROM `*PREFIX*share` WHERE `share_type` = ? AND `share_with` = ? AND `item_type` = ?';
                $query = DB::prepare($sql);
                $check = $query->execute(array('7', $gid, 'folder'));
                $share_check = self::getSharingQueryResult($check); 
                
                if(!empty($share_check)) {
                    /*
                    for($i = 0; $i < count($share_check); $i++){
                        array_push($share_arr,$share_check[$i]['id']);
                    }
                    */
                    array_push($share_arr,$share_check[0]['id']);
                    for($i = 1; $i < count($share_check); $i++){
                        array_push($share_arr,$share_check[$i]['id']);
                        $sql_share .= 'OR `parent` = ?';
                    }
                }
                array_push($gids,$gid);
            }
        }
        
        if(!empty($add_arr)) {
            $sql = substr($sql_add,0,-1);
            $query = DB::prepare($sql);
            $result_add = $query->execute($add_arr);
        }
        
        if(!empty($remove)) {
            if(!empty($share_arr)) {
                $sql_share .= ') AND (`share_with` = ?';
                for($i = 1; $i < count($remove); $i++) {
                    $sql_share .= ' OR `share_with` = ?';
                }
                $sql_share .= ')';
                $share_arr = array_merge($share_arr, $remove);
                $query = DB::prepare($sql_share);
                $query->execute($share_arr);
            }
            for($i = 1; $i < count($gids); $i++) {
                $sql_remove .= ' OR `gid` = ?';
            }
            $sql_remove .= ') AND ( `uid` = ?';

            for($i = 1; $i < count($remove); $i++) {
                $sql_remove .= ' OR `uid` = ?';
            }
            $sql_remove .= ')';
            $remove_arr = array_merge($gids, $remove);
            $query = DB::prepare($sql_remove);
            $result_remove = $query->execute($remove_arr);

        }
        if(DB::isError($result_remove) || DB::isError($result_add)) {
			Util::writeLog('SharingGroup', DB::getErrorMessage($result_remove), Util::ERROR);
			Util::writeLog('SharingGroup', DB::getErrorMessage($result_add), Util::ERROR);
            
            return 'false';
        }
        return 'success';

    }

    public function removeUserFromGroup($gid, $uids) {
        $user = User::getUser();
        $query = DB::prepare('DELETE FROM `*PREFIX*sharing_group_user` WHERE `gid` = ? AND `uid`= ? AND `owner` = ? ');
        
        foreach($uids as $uid) {
            $query->execute(array($gid, $uid, $user));
        }

    }

    public static function addUserToGroup($gid, $uids) {
        $user = User::getUser();
        $sql = 'INSERT INTO `*PREFIX*sharing_group_user` (`gid`, `uid`, `owner`) VALUES';
        $sqlarr = [];
        $checkuid = self::readGroupUsers($gid);
        foreach($uids as $uid) {
            if(in_array($uid,$checkuid)){
                continue;
            }
            $sql .='(?, ? , ?) ,';
            array_push($sqlarr, $gid, $uid, $user); 
        }
        if(!empty($sqlarr)){
            $sql = substr($sql,0,-1);
            $query = DB::prepare($sql);
            $query->execute($sqlarr);
        }     
    }

    public static function readGroups($user = '', $filter = '') {

        $user = $user !== '' ? $user : User::getUser();
        $query = DB::prepare('SELECT `id`, `name` FROM `*PREFIX*sharing_groups` WHERE `uid` = ?');
        $result = $query->execute(array($user));

        return self::getGroupsQueryResult($result, $filter);
    }
    
    public static function createGroups($name){
        if(empty(self::findGroupByName($name))){
            $user = User::getUser();
            $sql = 'INSERT INTO `*PREFIX*sharing_groups` (`name`, `uid`) VALUES(?, ?)';
            $query = DB::prepare($sql);
            $result = $query->execute(array($name, $user));
        }
        if (DB::isError($result)) {
			Util::writeLog('SharingGroup', DB::getErrorMessage($result), Util::ERROR);
        } else{
            return true;
        }
    }
    
    public static function deleteGroup($gid){
        $user = User::getUser();
        $sql = 'SELECT `id` FROM `*PREFIX*share` WHERE `share_type` = ? AND `share_with` = ?';
        $query = DB::prepare($sql);
        $check = $query->execute(array('7', $gid));
        $share_check = self::getSharingQueryResult($check); 
        //file_put_contents('123.txt', print_r($share_check ,true));     

        if($share_check != NULL ) {
            for($i = 0; $i < count($share_check); $i++) {
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
        return self::getGroupIdQueryResult($result);

    }
    
    public static function importGroup($data, $type = 'ignore'){
        $user = User::getUser();
        $importdata = explode("\n", $data);
        unset($importdata[sizeof($importdata) -1]);
        $importdata = self::importDataHanlder($importdata); 
        $length = sizeof($importdata);
        
        $sql = 'SELECT `name` FROM `*PREFIX*sharing_groups` WHERE `uid` = ?';
        $query = DB::prepare($sql);
        $result = $query->execute(array($user));
        $allgroup = self::getAllGroupsQueryresult($result);


        if($type == 'ignore') {
           
           for($i = 0; $i < $length; $i++) {
                if(in_array($importdata[$i]['group'], $allgroup)) {
                    continue;
                }
                self::createGroups($importdata[$i]['group']);
                $gid = self::findGroupByName($importdata[$i]['group']);
                if($importdata[$i]['uid'] != '\N'){
                    self::addUserToGroup($gid[0], $importdata[$i]['uid']);
                }
            }
           
        }
        
        if($type == 'merge') {
            for($i = 0; $i < $length; $i++) { 
                if(in_array($importdata[$i]['group'], $allgroup)) {
                    $gid = self::findGroupByName($importdata[$i]['group']);
                    if($importdata[$i]['uid'] != '\N') {
                        self::addUserToGroup($gid[0], $importdata[$i]['uid']);
                    }
                    continue;
                }
                
                self::createGroups($importdata[$i]['group']);
                $gid = self::findGroupByName($importdata[$i]['group']);
                if($importdata[$i]['uid'] != '\N'){
                    self::addUserToGroup($gid[0], $importdata[$i]['uid']);
                }
                
            }
        }
        
        if($type == 'cover'){
            for($i = 0; $i < $length; $i++){
                 if(in_array($importdata[$i]['group'], $allgroup)) {
                    $gid = self::findGroupByName($importdata[$i]['group']);
                    self::deleteGroup($gid[0]); 
                }
                
                self::createGroups($importdata[$i]['group']);
                $gid = self::findGroupByName($importdata[$i]['group']);
                if($importdata[$i]['uid'] != '\N'){
                    self::addUserToGroup($gid[0], $importdata[$i]['uid']);
                }
            
            }
        }

        //file_put_contents("12345.txt", print_r($gid,true));
    }
 
    public static function export() {
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
    
    public static function getAllGroupInfo() {
        $user = User::getUser();
        $sql = 'SELECT id, name , *PREFIX*sharing_group_user.uid FROM *PREFIX*sharing_groups LEFT OUTER JOIN *PREFIX*sharing_group_user ON *PREFIX*sharing_groups.id = *PREFIX*sharing_group_user.gid WHERE *PREFIX*sharing_groups.uid = ?';
        $query = DB::prepare($sql);
        //$result = $query->execute(array($filePath, $user));
        $result = $query->execute(array($user));
        $data = self::getGroupsInfoQueryResult($result); 
        
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
    
    public static function findAllGroup() {
        
        $query = DB::prepare('SELECT `id` ,`name` FROM `*PREFIX*sharing_groups`');
        $result = $query->execute();
        return self::getGroupsQueryResult($result, '');
    }

    
    public static function countAllUsers() {
        $sql = 'SELECT COUNT(uid) FROM `*PREFIX*users`';
        $query = DB::prepare($sql);
        $result = $query->execute();
        return self::getEveryoneCountQueryResult($result); 
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
    
    private static function getAllGroupsQueryresult($result) {
        $data = [];

        if(DB::isError($result)) {
			Util::writeLog('SharingGroup', DB::getErrorMessage($result), Util::ERROR);

            return;
        }

        while($row = $result->fetch()) {
            $data[] = $row['name'];
        }
        return $data;
    }
    
    private static function getGroupIdQueryresult($result) {
        $data = [];

        if(DB::isError($result)) {
			Util::writeLog('SharingGroup', DB::getErrorMessage($result), Util::ERROR);

            return;
        }

        while($row = $result->fetch()) {
            $data[] = $row['id'];
        }

        return $data;
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
        natcasesort($data);
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
    
    private static function getGroupsInfoQueryResult($result) {
        $data = [];
        
        if (DB::isError($result)) {
			Util::writeLog('SharingGroup', DB::getErrorMessage($result), Util::ERROR);
            
            return;
        }

        while ($row = $result->fetchRow()) {
            
            if(!array_key_exists($row['name'], $data)) {
                $data[$row['name']] = [];
                $info = array('id' => $row['id'], 'name' => $row['name'],
                            'count' => 0, 'user' => '');
            }
            
            if($row['uid'] != NULL) {
                $info['count']++;
                $info['user'] .= $row['uid'].',';
            }
            
            $data[$row['name']]= $info;
        }
        ksort($data,SORT_NATURAL | SORT_FLAG_CASE);   
        //file_put_contents('132.txt', print_r($data, true));
        
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
    
    private static function getEveryoneCountQueryResult($result) {
    
        if (DB::isError($result)) {
			Util::writeLog('SharingGroup', DB::getErrorMessage($result), Util::ERROR);
            
            return;
        }

        while ($row = $result->fetchRow()) {
            $data = $row['COUNT(uid)'] - 1 ;     
        }
        return $data;
    }
    
    private static function getSharingQueryResult($result) {
        $data = [];

        if (DB::isError($result)) {
			Util::writeLog('SharingGroup', DB::getErrorMessage($result), Util::ERROR);
            
            return;
        }
        

        while ($row = $result->fetchRow()) {
            $share = array('id' => $row['id']);
            array_push($data, $share);
        }
        
        return $data;
    }

    private static function importDataHanlder($data) {
        $result = [];
        for($i = 0; $i < count($data); $i++) {
            list($id, $group, $uid) = explode(", " , $data[$i]);
            $id = substr($id, 1, -1);
            $group = substr($group, 1, -1);
            $uid = str_replace("\"", "", $uid);
            $temp = [];
            $temp['id'] = $id;
            $temp['group'] = $group;
            $temp['uid'] = $uid == '\N' ? '\N' : array($uid);

            for($j = 0; $j < count($result); $j++) {
                if($group == $result[$j]['group'] && $uid != NULL) {
                    $result[$j]['uid'][] = $uid;
                    break;
                 }
            }
            if($j < count($result)) {
                continue;
            }
            $result[] = $temp;
        }

        return $result;
    }

}

