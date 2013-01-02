<?php

require_once('classes/RideHelper.php');

/**
 * Description of User
 *
 * @author Robin
 */
class User {
    private $user_id;
    private $username;
    private $email;
    private $password;
    private $key;
    private $active;
    private $forum_name;
    private $strava;
    private $name;
    private $points;
    
    function __construct($user_id = null, $username = null, $email = null, 
            $password = null, $key = null, $active = null, $forum_name = null,
            $strava = null, $name= null) {
        $this->user_id = $user_id;
        $this->username = $username;
        $this->email = $email;
        $this->password = $password;
        $this->key = $key;
        $this->active = $active;
        $this->forum_name = $forum_name;
        $this->strava = $strava;
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
    
    public function getEmail() {
        return $this->email;
    }

    public function setEmail($email) {
        $this->email = $email;
    }

    public function getPassword() {
        return $this->password;
    }

    public function setPassword($password) {
        $this->password = $password;
    }

    public function getKey() {
        return $this->key;
    }

    public function setKey($key) {
        $this->key = $key;
    }

    public function getActive() {
        return $this->active;
    }

    public function setActive($active) {
        $this->active = $active;
    }
    public function isActive(){
        if($this->active == 1){
                return true;
        }
    }
    public function getForum_name() {
        return $this->forum_name;
    }

    public function setForum_name($forum_name) {
        $this->forum_name = $forum_name;
    }

    public function getStrava() {
        return $this->strava;
    }

    public function setStrava($strava) {
        $this->strava = $strava;
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
    public function activate(){
        $this->active = 1;
    }
    public function deactivate(){
        $this->active = 0;
    }
    public function __toString() {
        return "<br>$this->user_id<br>$this->username<br>$this->password<br>$this->key";
    }


}

?>
