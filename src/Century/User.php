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

    public function __construct($user_id = null, $username, $password, array $roles,
                                $email, $name, $forum_name, $strava, array $rides)
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
        return $this->name;
    }
    public function getFirstName()
    {
        return $this->name;
    }
    public function getSurame()
    {
        //E.g. Robin C.
        return $this->name;
    }
    public function getPrivateName()
    {
        return $this->name;
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

}