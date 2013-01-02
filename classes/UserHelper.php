<?php

require_once('Database.php');
require_once('User.php');


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
    public function getUsers($sort_by_points = true){
        try{
            $sql = $this->dbc->prepare("SELECT * FROM user");
            $sql->execute();
            
            $sql->setFetchMode(PDO::FETCH_OBJ);
            $results = $sql->fetchAll();
            
            $users = array();
            
            foreach($results as $r){
                $user = new User($r->user_id, $r->username, $r->name);
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
                $user = new User($r->user_id, $r->username, $r->name);
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
