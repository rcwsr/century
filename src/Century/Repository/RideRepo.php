<?php

namespace Century\Repository;

use Knp\Repository;
use Century\Ride;

class RideRepo Extends Repository
{
	public function getTableName()
    {
        return 'ride';
    }
    public function getAllRides($user_id = null, $month = null, $year = null)
    {
    	if($user_id == null && $month == null && $year == null){
            $sql = 'SELECT * FROM ride ORDER BY date desc';
       		$result = $this->db->fetchAll($sql);
        }
        elseif($user_id != null && $month == null && $year == null){
            $sql = 'SELECT * FROM ride WHERE user_id = ? ORDER BY date desc';
        	$result = $this->db->fetchAll($sql, array($user_id));
        }
        elseif($user_id == null && $month != null && $year != null){
            $sql = 'SELECT * FROM ride WHERE month(date) = ? AND year(date) = ? ORDER BY date desc';
        	$result = $this->db->fetchAll($sql, array($month, $year));
        }
        elseif($user_id == null && $month == null && $year != null){
            $sql = 'SELECT * FROM ride WHERE year(date) = ? ORDER BY date desc';
            $result = $this->db->fetchAll($sql, array($year));
        }
        elseif($user_id != null && $month != null && $year != null){
             $sql = 'SELECT * FROM ride WHERE month(date) = ? AND year(date) = ? AND user_id = ? ORDER BY date desc';
        	$result = $this->db->fetchAll($sql, array($month, $year, $user_id));
        }

        $rides = array();
        foreach($result as $r){
        	$ride = new Ride($r['ride_id'],
                             $r['user_id'],
                             $r['km'], 
                             $r['url'], 
                             \DateTime::createFromFormat('Y-m-d H:i:s', $r['date']), 
                             $r['details'], 
                             $r['average_speed'],
                             $r['strava_ride_id']
                             );

       		$rides[] = $ride;
        }

        
        return $rides;



    }
    public function getLatest($username = null, $limit = null, $offset = null)
    {

    }

    public function getRideById($ride_id)
    {
        $rides = $this->getAllRides();

        $ride = null;
        foreach($rides as $r){
            if($r->getRideId() == $ride_id){
                $ride = $r;
            }
        }
        return $ride;
    }

}