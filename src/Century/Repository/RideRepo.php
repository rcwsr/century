<?php

namespace Century\Repository;

use Knp\Repository;
use Century\Ride;

class RideRepo Extends Repository{
	public function getTableName()
    {
        return 'ride';
    }
    public function getAllRides($username = null, $month = null, $year = null){
    	if($username == null && $month == null && $year == null){
            $sql = 'SELECT * FROM ride ORDER BY date_added desc';
       		$result = $this->db->fetchAll($sql);
        }
        elseif($username != null && $month == null && $year == null){
            $sql = 'SELECT * FROM ride WHERE username = ? ORDER BY date_added desc';
        	$result = $this->db->fetchAll($sql, array($username));
        }
        elseif($username == null && $month != null && $year != null){
            $sql = 'SELECT * FROM ride WHERE month(date) = ? AND year(date) = ? ORDER BY date_added desc';
        	$result = $this->db->fetchAll($sql, array($month, $year));
        }
        elseif($username != null && $month != null && $year != null){
             $sql = 'SELECT * FROM ride WHERE month(date) = ? AND year(date) = ? AND username = ? ORDER BY date_added desc';
        	$result = $this->db->fetchAll($sql, array($month, $year, $username));
        }

        $rides = array();
        foreach($result as $r){
        	$ride = new Ride($r['ride_id'], $r['user_id'], $r['km'], $r['url'], \DateTime::createFromFormat('Y-m-d H:i:s',$r['date']), $r['details']);
       		$rides[] = $ride;
        }

        
        return $rides;



    }
    public function getLatest($username = null, $limit = null, $offset = null){

    }



}