<?php
namespace Century;

require_once('Database.php');
require_once('Ride.php');

class RideHelper{
        private $dbc;
    
	public function __construct(){
            $database = new Database();
            $this->dbc = $database->connect();
            //echo "Contruct!";
	}
	public function __destruct(){
            $this->dbc = null;
            //echo "Destruct!";
	}
        
        public function addRide(Ride $ride){
            try{
                $sql = $this->dbc->prepare("INSERT INTO ride (user_id, km, url, points, date, date_added, details) VALUES (?,?,?,?,?,NOW(),?)");
		$sql->execute(array(
                    $ride->getUser_id(),
                    $ride->getKm(), 
                    $ride->getUrl(),
                    $ride->getPoints(), 
                    $ride->getDate(),
                    $ride->getDetails()
                ));
            }
            catch(PDOException $e){
		echo $e->getMessage();
            }
        }
        public function getRides($user_id = null, $month = null, $year = null){
             
            try{
                if($user_id == null && $month == null && $year == null){
                     $sql = $this->dbc->prepare("SELECT * FROM ride");
                     $sql->execute();
                }
                elseif($user_id != null && $month == null && $year == null){
                     $sql = $this->dbc->prepare("SELECT * FROM ride WHERE user_id = ?");
                     $sql->execute(array($user_id));
                }
                elseif($user_id == null && $month != null && $year != null){
                     $sql = $this->dbc->prepare("SELECT * FROM ride WHERE month(date) = ? AND year(date) = ?");
                     $sql->execute(array($month, $year));
                }
                elseif($user_id != null && $month != null && $year != null){
                    $sql = $this->dbc->prepare("SELECT * FROM ride WHERE month(date) = ? AND year(date) = ? AND user_id = ?");
                    $sql->execute(array($month, $year, $user_id));
                }
               

                $sql->setFetchMode(PDO::FETCH_OBJ);
                $results = $sql->fetchAll();
                
             
                $rides = array();
                //print_r($ride);
                foreach($results as $r){
                   $ride = new Ride();
                   $ride->setRide_id($r->ride_id);
                   $ride->setUser_id($r->user_id);
                   $ride->setKm($r->km);
                   $ride->setUrl($r->url);
                   $ride->setDate($r->date);
                   $ride->setDate_added($r->date_added);
                   $ride->setDate_modified($r->date_modified);
                   $ride->setDetails($r->details);   
                   $rides[] = $ride;
                }
                
                return $rides;
            }
            catch(PDOException $e){
		echo $e->getMessage();
            }
        }
        public function getRide($id){
            try{
                $sql = $this->dbc->prepare("SELECT * FROM ride WHERE ride_id = ?");
                $sql->execute(array($id));

                $sql->setFetchMode(PDO::FETCH_OBJ);
                $results = $sql->fetchAll();
                
             
                
                //print_r($ride);
                foreach($results as $r){
                   $ride = new Ride();
                   $ride->setRide_id($r->ride_id);
                   $ride->setUser_id($r->user_id);
                   $ride->setKm($r->km);
                   $ride->setUrl($r->url);
                   $ride->setDate($r->date);
                   $ride->setDate_added($r->date_added);
                   $ride->setDate_modified($r->date_modified);
                   $ride->setDetails($r->details);   
                   break;
                }
                
                return $ride;
            }
            catch(PDOException $e){
		echo $e->getMessage();
            }
        }
        public function editRide(Ride $ride){
            //adjust points to new km.
            //$ride->points(100);
            //print_r($ride);
            try{
                $sql = $this->dbc->prepare("UPDATE ride SET km=?, url=?, points=?, date=?, date_modified=NOW(), details=? WHERE ride_id=?");
                $sql->execute(array(
                        $ride->getKm(), 
                        $ride->getUrl(),
                        $ride->getPoints(), 
                        $ride->getDate(),
                        $ride->getDetails(),
                        $ride->getRide_id()
                ));
               
            }
            catch(PDOException $e){
		echo $e->getMessage();
            }
            
        }
        
}
?>
