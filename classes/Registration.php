<?php
require_once(__DIR__ . '/UserHelper.php');
/**
 * Description of Registration
 *
 * @author Robin
 */
class Registration {
    /**
	  * @var object|null
	**/
	private $dbc;
	private $user_helper;
	/**
	  * Constructor of class, instantiates database connection
	  * @param Database $database
	  * @return void
	**/
	public function __construct(){
		$database = new Database();
                $this->dbc = $database->connect();
		$this->user_helper = new UserHelper($this->dbc);
	}
	/**
	  * Destructor destroys the connection when there are no more references to the class.
	  * @return void
	**/
	public function __destruct(){
		$this->dbc = null;
	}
	public function addUser($user){
		
		$key = $this->randomStr(16,true, false, false);
                
                //create new key. generates new key if it already exists.
		while($this->user_helper->keyExists($key)){
			$key = $this->randomStr(16,true, false, false);
		}
		
		//add the user
		$email_exists = false;
		$username_exists = false;

		if($this->user_helper->usernameExists($user)){
			$username_exists = true;
                        //echo "Username exists";
			throw new Exception('Username is already taken');
		}
		elseif($this->user_helper->emailExists($user)){
			$email_exists = true;
                        //echo "Email exists";
			throw new Exception('Email address already registered');
		}
		//If username and email don't exist, add the user.
		if(!$email_exists && !$username_exists){
                        $user->setPassword($this->saltPass($user->getUsername(), $user->getPassword()));
			$user->setKey($key);
			$this->sendActivation($user);
			$this->user_helper->addUser($user);
                        echo "User added";
                        //echo $user;
		}
		
	}
        public function saltPass($username, $password){
		$salt = hash('sha256', uniqid(mt_rand(), true) . 'shucks, you got me' . strtolower($username));
		$hash = $salt . $password;

		for ($i = 0; $i < 10000; $i ++){
			$hash = hash('sha256', $salt.$hash);
		}
		$hash = $salt . $hash;
		//$user->setPassword($hash);
		return $hash;
	}
        public function randomStr($length = 5, $alpha = true, $numeric = true, $symbols = true){
		$chars = '';

		if($alpha)
			$chars .= 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		if($numeric)
			$chars .= '1234567890';
		if($symbols)
			$chars .= '-_!?';

		$pw = '';
		if($length == null)
			$length = rand(15, 20);
		
		$count = mb_strlen($chars);

		for($i = 0; $i < $length; $i++){
			$index = rand(0, $count -1);
			$pw .= mb_substr($chars, $index, 1);
		}
		//echo $length.'<br />';
		return $pw;
	}
        public function checkPasswords($pass1, $pass2){
		if($pass1 === $pass2){
			return true;
		}
		else{
                    throw new Exception('The passwords do not match');
                   // echo "passwords do not match";
                }
	}
        public function sendActivation($user){
		$message = 'Click this link to activate your account: http://localhost/auth/activate.php?k='.$user->getKey();
		$to = $user->getEmail();
		$subject = 'Please activate your account';
		echo $message;
	}
	public function activateUser($key){
		$key = stripslashes($key);
		$key = strip_tags($key);
		if(is_numeric($key)){
			throw new Exception('The key is invalid numeric');
		}
		if(strlen($key)!=16){
			throw new Exception('The key is invalid not long');
		}
		//get user based on key
		//check if active already
		if($this->user_helper->keyExists($key)){
			$users = $this->user_helper->getUsers();
			//print_r($users);
			foreach($users as $u){
				if($u->getKey() == $key){
					//echo $u->getKey();
					$user = $u;break;
				}
			}
			if(!$user->isActive()){
				$user->activate();
				$this->user_helper->updateUser($user);
                                echo $user->getActive();
			}
			else{
				throw new Exception('Account has already been activated');
			}
		}
		else{
			throw new Exception('The key is invalid');
		}
		
	}
	public function deactivateUser($user){
		if($user->isActive()){
			$user->deactivate();
			$this->user_helper->updateUser($user);
		}
		else{
			throw new Exception('Cannot deactivate user.', RegistrationErrors::USER_DEACTIVATED);
		}
	}
}

?>
