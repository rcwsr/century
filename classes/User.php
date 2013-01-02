<?php

require_once('classes/RideHelper.php');
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of User
 *
 * @author Robin
 */
class User {
    private $user_id;
    private $username;
    private $name;
    private $points;
    
    public function __construct($user_id, $username, $name) {
        $this->user_id = $user_id;
        $this->username = $username;
        $this->name = $name;
        $this->points = $this->points();
    }
    public function getUsername() {
        return $this->username;
    }

    public function setUsername($username) {
        $this->username = $username;
    }

        public function getUser_id() {
        return $this->user_id;
    }

    public function setUser_id($user_id) {
        $this->user_id = $user_id;
    }

    public function getName() {
        return $this->name;
    }

    public function setName($name) {
        $this->name = $name;
    }
    
    public function getPoints($month = null, $year = null) {
        if($month == null && $year == null){
            return $this->points;
        }
        else{
            return $this->points($month, $year);
        }
    }
    
    private function points($month = null, $year = null){
        
        $rideHelper = new RideHelper();
        
        if($month == null && $year == null){
             $rides = $rideHelper->getRides($this->user_id);
        }
        else{
            $rides = $rideHelper->getRides($this->user_id, $month, $year);
        }
       
        
        $points = 0;
        foreach($rides as $r){
           $points = $points + $r->getPoints();
        }
        
        return $points;
    }
    


}

?>
