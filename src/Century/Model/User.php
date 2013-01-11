<?php

namespace Century\User;

use Symfony\Component\Security\Core\User\UserInterface;

class User implements UserInterface
{
    private $username;
    private $password;
    private $salt;
    private $roles;

    private $email;
    private $name;
    private $forum_name;
    private $strava;

    public function __construct($username, $password, $salt, array $roles
                                $email, $name, $forum_name, $strava)
    {
        $this->username = $username;
        $this->password = $password;
        $this->salt = $salt;
        $this->roles = $roles;

        $this->email = $email;
        $this->name = $name;
        $this->forum_name = $forum_name;
        $this->strava = $strava;
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
        return $this->salt;
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

    public function getEmail(){
        return $this->email;
    }
    public function getName(){
        return $this->name;
    }
    public function getForumName(){
        return $this->forum_name;
    }
    public function getStrava(){
        return $this->strava;
    }
}