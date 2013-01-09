<?php

require_once(__DIR__ . '/Database.php');
require_once(__DIR__ . '/User.php');


/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of UserHelper
 *
 * @author Robin
 */
class UserHelper {
    //put your code here
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
    public function addUser(User $user){
        try{
            $sql = $this->dbc->prepare("INSERT INTO user (username, email, password, user_key, name, forum_name, strava) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $sql->execute(array($user->getUsername(),
                                $user->getEmail(),
                                $user->getPassword(),
                                $user->getKey(),
                                $user->getName(),
                                $user->getForum_name(),
                                $user->getStrava() 
                                ));
        }
        catch(PDOException $e){
                echo $e->getMessage();
        }
    }
    public function updateUser(User $user){
        try{
            $sql = $this->dbc->prepare("UPDATE user SET username=?, email=?, password=?, user_key=?, name=?, forum_name=?, strava=?, active=? WHERE user_id = ?");
            $sql->execute(array($user->getUsername(),
                                $user->getEmail(),
                                $user->getPassword(),
                                $user->getKey(),
                                $user->getName(),
                                $user->getForum_name(),
                                $user->getStrava(),
                                $user->getActive(),
                                $user->getUser_id()
                        ));
        }
        catch(PDOException $e){
                echo $e->getMessage();
        }
	}
    public function usernameExists(User $user){
		$users = $this->getUsers(false);

		foreach($users as $u){
			if($user->getUsername() == $u->getUsername()){
				return true;break;
			}
		}
	}
	public function emailExists(User $user){
		$users = $this->getUsers(false);

		foreach($users as $u){
			if($user->getEmail() == $u->getEmail()){
				return true;break;
			}
		}
	}
	public function keyExists($key){
		$users = $this->getUsers(false);

		foreach($users as $u){
			if($key == $u->getKey()){
				return true;break;
			}
		}
	}
    public function getUsers($sort_by_points = true){
        try{
            $sql = $this->dbc->prepare("SELECT * FROM user");
            $sql->execute();
            
            $sql->setFetchMode(PDO::FETCH_OBJ);
            $results = $sql->fetchAll();
            
            $users = array();
            
            foreach($results as $r){
                $user = new User($r->user_id,
                                 $r->username,
                                 $r->email,
                                 $r->password,
                                 $r->user_key,
                                 $r->active,
                                 $r->forum_name,
                                 $r->strava,
                                 $r->name);
                $users[] = $user;
            }
            
            //sort by points desc
            if($sort_by_points){
                usort($users, function($b, $a){
                    return strcmp($a->getPoints(), $b->getPoints());
                });
            }
           
            
            return $users;
        }
        catch(PDOException $e){
		echo $e->getMessage();
        }
        
    }
    
     public function getUser($username){
        try{
            if(is_numeric($username)){
                $sql = $this->dbc->prepare("SELECT * FROM user WHERE user_id = ?");
            }
            else{
                $sql = $this->dbc->prepare("SELECT * FROM user WHERE username = ?");
            }
            $sql->execute(array($username));
            
            $sql->setFetchMode(PDO::FETCH_OBJ);
            $results = $sql->fetchAll();

            foreach($results as $r){
                $user = new User($r->user_id,
                                 $r->username,
                                 $r->email,
                                 $r->password,
                                 $r->user_key,
                                 $r->active,
                                 $r->forum_name,
                                 $r->strava,
                                 $r->name);
                break;
            }

            
            return $user;
        }
        catch(PDOException $e){
		echo $e->getMessage();
        }
        
    }
    
}

?>
