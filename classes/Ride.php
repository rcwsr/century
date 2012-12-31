<?php

class Ride{
    private $ride_id;
    private $user_id;
    private $km;
    private $url;
    private $points;
    private $date;
    private $date_added;
    private $date_modified;
    private $details;
    

    public function newRide($user_id, $km, $url, $date, $details){
        $this->setUser_id($user_id);
        $this->setKm($km);
        $this->setUrl($url);
        $this->setDate($date);
        $this->setDetails($details);
       // $this->setPoints($km);  
        return $this;
    }
   

    public function getRide_id() {
        return $this->ride_id;
    }

    public function setRide_id($ride_id) {
        $this->ride_id = $ride_id;
    }

    public function getUser_id() {
        return $this->user_id;
    }

    public function setUser_id($user_id) {
        $this->user_id = $user_id;
    }

    public function getKm() {
        return $this->km;
    }

    public function setKm($km) {
        $this->km = $km;
        $this->setPoints($km);
    }

    public function getUrl() {
        return $this->url;
    }

    public function setUrl($url) {
        $this->url = $url;
    }

    public function getPoints() {
        return $this->points;
    }

    public function setPoints($km) {

       //For 100km, you get 10 points
       if($km >= 100 && $km < 150)
            $this->points = 10;
       //for every 50km over 100km you get a further 5 points
       elseif($km >= 150){
           //take first 100km off to work out additional points
           $km = $km - 100;
           $this->points = 10 + floor($km/50) *5; //add
       }
       //under 100km you get 0 points.
       else{
           $this->points = 0;
       }
    }
  
    public function getDate() {
        return $this->date;
    }

    public function setDate($date) {
        $this->date = $date;
    }

    public function getDate_added() {
        return $this->date_added;
    }

    public function setDate_added($date_added) {
        $this->date_added = $date_added;
    }
    
    public function getDate_modified() {
        return $this->date_modified;
    }

    public function setDate_modified($date_modified) {
        $this->date_modified = $date_modified;
    }

        
    public function getDetails() {
        return $this->details;
    }

    public function setDetails($details) {
        $this->details = $details;
    }
    


}
?>
