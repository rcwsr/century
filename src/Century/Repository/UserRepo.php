<?php

namespace Century\Repository;

use Knp\Repository;
use Century\User;

class UserRepo Extends Repository{
	public function getTableName()
    {
        return 'user';
    }
    public function getAllUsers($rideRepo = null){
    	    $sql = 'SELECT * FROM user';
        	$result = $this->db->fetchAll($sql);
        

        $users = array();
        foreach($result as $r){
        	$user = new User($r['user_id'], $r['username'], $r['password'], explode(',', $r['roles']), $r['email'], $r['name'], $r['forum_name'], $r['strava']);
       		$users[] = $user;
        }
        return $users;



    }
    public function getLatest(){

    }

}