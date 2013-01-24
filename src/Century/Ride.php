<?php 

namespace Century;

class Ride
{
    private $ride_id;
    private $user_id;
    private $km;
    private $url;
    private $points;
    private $date;
    private $date_added;
    private $date_modified;
    private $details;
    private $average_speed;
    private $strava_ride_id;
    

    public function __construct($ride_id, 
                                $user_id, 
                                $km, 
                                $url, 
                                \DateTime $date, 
                                $details,
                                $average_speed,
                                $strava_ride_id = null)
    {
        $this->ride_id = $ride_id;
        $this->user_id = $user_id;
        $this->km = $km;
        $this->url = $url;
        $this->date = $date;
        $this->details = $details;
        $this->points = $this->setPoints($km);
        $this->average_speed = $average_speed;
        $this->strava_ride_id = $strava_ride_id;
        //$this->points = $points;
    }
   

    public function getRideId() 
    {
        return $this->ride_id;
    }

    public function setRidId($ride_id) 
    {
        $this->ride_id = $ride_id;
    }

    public function getUserId() 
    {
        return $this->user_id;
    }

    public function setUserId($user_id) 
    {
        $this->user_id = $user_id;
    }

    public function getKm() 
    {
        return $this->km;
    }

    public function getDistance($metric = true)
    {
        return $this->km;
    }
    public function setKm($km) 
    {
        $this->km = $km;
        $this->setPoints($km);
    }

    public function getUrl() 
    {
        return $this->url;
    }

    public function setUrl($url) 
    {
        $this->url = $url;
    }

    public function getPoints() 
    {
        return $this->points;
    }

    public function setPoints($km) 
    {

       //For 100km, you get 10 points
       if($km >= 100 && $km < 150)
            return 10;
       //for every 50km over 100km you get a further 5 points
       elseif($km >= 150){
           //take first 100km off to work out additional points
           $km = $km - 100;
           
           //Could instead / 10?
           return 10 + floor($km/50) *5; //add
       }
       //under 100km you get 0 points.
       else{
           return 0;
       }
    }
  
    public function getDate() 
    {
        return $this->date;
    }

    public function setDate(\DateTime $date)
    {
        $this->date = $date;
    }

    public function getDate_added() 
    {
        return $this->date_added;
    }

    public function setDate_added($date_added) 
    {
        $this->date_added = $date_added;
    }
    
    public function getDate_modified() 
    {
        return $this->date_modified;
    }

    public function setDate_modified($date_modified) 
    {
        $this->date_modified = $date_modified;
    }
    
    public function getDetails() 
    {
        return $this->details;
    }

    public function setDetails($details) 
    {
        $this->details = $details;
    }
    public function getAverageSpeed()
    {
        return $this->average_speed;
    }
    public function setAverageSpeed($average_speed)
    {
        $this->average_speed = $average_speed;
    }
    public function getStravaRideId()
    {
        return $this->strava_ride_id;
    }
    public function setStravaRideId($strava_ride_id)
    {
        $this->strava_ride_id = $strava_ride_id;
    }
    public function __toString() 
    {
        $str = 'Ride_ID ['. $this->ride_id.
                '], User_ID ['.$this->user_id.
                '], KM ['.$this->km.
                '], Points ['.$this->points.
                '], Date ['.$this->date->format('d-m-Y').']';
        return $str;
    }





}
?>
