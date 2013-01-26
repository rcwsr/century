<?php

namespace Century;

use Symfony\Component\Security\Core\User\UserInterface;
use Century\Repository\RideRepo;

class User implements UserInterface
{

    private $user_id;
    private $username;
    private $password;
    private $salt;
    private $roles;

    private $email;
    private $name;
    private $forum_name;
    private $strava;
    private $points;
    private $rides;
    private $metric;

    public function __construct($user_id = null, $username, $password, array $roles,
                                $email, $name, $forum_name, $strava, array $rides, $metric = true)
    {
        $this->user_id = $user_id;
        $this->username = $username;
        $this->password = $password;
        $this->roles = $roles;

        $this->email = $email;
        $this->name = $name;
        $this->forum_name = $forum_name;
        $this->strava = $strava;

        $this->rides = $rides;
        $this->metric = (bool) $metric;
        $this->points = $this->getPoints();
    }

    public function getRoles()
    {
        return $this->roles;
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function getSalt()
    {
        return strtolower($this->username);
    }

    public function getUsername()
    {
        return $this->username;
    }

    public function eraseCredentials()
    {
    }

    public function equals(UserInterface $user)
    {
        if (!$user instanceof WebserviceUser) {
            return false;
        }

        if ($this->password !== $user->getPassword()) {
            return false;
        }

        if ($this->getSalt() !== $user->getSalt()) {
            return false;
        }

        if ($this->username !== $user->getUsername()) {
            return false;
        }

        return true;
    }

    public function getEmail()
    {
        return $this->email;
    }
    public function getName()
    {
        return ucwords($this->name);
    }
    public function getFirstName()
    {
        $names = explode(' ', $this->name);
        $firstname = ucwords(array_shift(array_values($names)));
        if($firstname == '' || !$firstname)
            return $this->username;
        else
            return $firstname;
    }
    public function getSurname()
    {
        $names = explode(' ', $this->name);
        $surname = ucwords(end($names));

        if($surname == '' || !$surname)
            return $this->username;
        else
            return $surname;
    }
    public function getPrivateName()
    {
       return ucwords($this->getFirstName().' '.substr($this->getSurname(), 0, 1).'.');
    }
    public function getForumName()
    {
        return $this->forum_name;
    }
    public function getStrava()
    {
        return $this->strava;
    }
    public function getUserId()
    {
        return $this->user_id;
    }
    public function getPoints($month = null, $year = null)
    {
        $points = 0;
        $rides = $this->getRides($month, $year);
        foreach($rides as $r){
            $points = $points + $r->getPoints();
        }

        if($this->username == 'milemuncher'){
            return $points - ($points * 2);
        }
        return $points;
    }
    public function getRides($month = null, $year = null)
    {
        $rides = $this->rides;
        $rides_array = array();

        foreach($rides as $r){
            if($month != null && $year != null){
                if($r->getDate()->format('n') == $month && $r->getDate()->format('Y') == $year){
                    $rides_array[] = $r;
                }
            }
            else{
                 $rides_array[] = $r;
            }
        }

        return $rides_array;
    }
    public function getTotalPoints($month = null, $year = null)
    {
        $rides = $this->getRides($month, $year);

        $points = 0;
        foreach($rides as $r){
            $points += $r->getPoints();
        }
        return $points;
    }
    public function getTotalDistance($month = null, $year = null, $metric = true)
    {
        $rides = $this->getRides($month, $year);

        $distance = 0;
        foreach($rides as $r){
            $distance += $r->getDistance($metric, true);
        }

        return $distance;
    }
    public function getNoOfCenturies($month = null, $year = null)
    {
        $rides = $this->getRides($month, $year);

        $centuries = 0;

        foreach($rides as $r){
            if($r->getKm() > 99){
                $centuries++;
            }
        }

        return $centuries;
    }
    public function getRank()
    {
        
    }
    public function isDisqualified(){

        $months = array();
        $year = (int) date('Y');
        foreach (range(1, (int) date('n')) as $month) {
            $months[] = array('month' => $month, 'points' => $this->getTotalPoints($month, $year));  
        }
       
        foreach($months as $month){
            if($month['month'] != (int) date('n')){
                if($month['points'] < 10)
                    return true;
            }
        }
    }
    public function isMetric(){
        return $this->metric;
    }

}