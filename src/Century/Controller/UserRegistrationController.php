<?php

namespace Century\Controller;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

class UserRegistrationController
{
	protected $app;

	public function __construct(Application $app)
	{
		$this->app = $app;
	}

	public function login(Request $request)
	{
		return $this->app['twig']->render('login.html.twig', array(
	        'error'         => $this->app['security.last_error']($request),
	        'last_username' => $this->app['session']->get('_security.last_username'),
	    ));
	}
	public function validateRegistrationForm(array $data)
	{	
	    $constraint = new Assert\Collection(array(
	                        'username' => array(new Assert\NotBlank(), new Assert\Regex(array('pattern' => '/^[a-z0-9_-]{3,30}$/'))),
	                        'email' => new Assert\NotBlank(),
	                        'name' => array(new Assert\NotBlank(), new Assert\Regex(array('pattern' => '/^((\b[a-zA-Z]{2,60}\b)\s*){2,}$/'))),
	                        'password' => new Assert\NotBlank(),
	                        'forum_name' => new Assert\NotBlank()
	                        ));
	    $validation_data = array(
	        'username' => $data['username'],
			'email' => $data['email'],
			'name' => $data['name'],
			'password' => $data['password'],
			'forum_name' => $data['forum_name']
	    );
	    $errors = $this->app['validator']->validateValue($validation_data, $constraint);
		
		return $errors;
	}
	public function validatePasswordResetForm(array $data)
	{	
	    $constraint = new Assert\Collection(array(
	                        'username' => array(new Assert\NotBlank(), new Assert\Regex(array('pattern' => '/^[a-z0-9_-]{3,30}$/'))),
	                        'email' => new Assert\NotBlank()
	                        ));
	    $validation_data = array(
	        'username' => $data['username'],
			'email' => $data['email']
	    );
	    $errors = $this->app['validator']->validateValue($validation_data, $constraint);
		
		return $errors;
	}

	public function userNameExists($username){
		$users = $this->app['users']->getAllUsers();
		foreach($users as $u){
			if($u->getUsername() == $username)
				return true;
		}
	}

	public function createRegistrationForm($return_data)
	{
		$form = $this->app['form.factory']->createBuilder('form', $return_data)
	        ->add('username', 'text', array(
	            'label' => 'Username (all lowercase, no spaces - use underscores instead.) *',
	            'required' => true
	        ))
	        ->add('name', 'text', array(
	            'label' => 'Full name (first name and surname - this will only be shown to logged in users.) *',
	            'required' => true
	        ))
	        ->add('email', 'text', array(
	            'label' => 'E-mail address *',
	            'required' => true
	        ))
	        ->add('password', 'password', array(
	            'label' => 'Password *',
	            'required' => true
	        ))
	        ->add('forum_name', 'text', array(
	            'label' => 'LFCC forum username *',
	            'required' => true
	        ))
	        ->add('metric', 'choice', array(
	        	'label' => 'Measurements *',
	            'choices' => array(true => 'Metric (km)', false => 'Imperial (miles)'),
	            'required' => true
	        ))
	        ->add('strava', 'text', array(
	            'label' => 'Strava athlete ID',
	            'required' => false
	        ))
	        ->getForm();
	    return $form;
	}
	public function register(Request $request)
	{
		//User registration
		//password confirmation
	     
		if ($this->app['request']->getMethod() === 'GET') {
			$return_data = null;
			$form = $this->createRegistrationForm($return_data);
			return $this->app['twig']->render('register.html.twig', array('form' => $form->createView(), 'errors' => null, 'username_error' => null));
		}
	    elseif ($this->app['request']->getMethod() === 'POST') {
	    	
	    	$registration_data = $request->get('form');
	    	$form = $this->createRegistrationForm($registration_data);
		
			//echo $registration_data['username'];
	    	if($this->userNameExists(strtolower($registration_data['username']))){
	    		$username_error = 'Username already exists';
	    	}
	    	else{
	    		$username_error = null;
	    	}

	    	$errors = $this->validateRegistrationForm($registration_data);

	    	if (count($errors) > 0 || $username_error) {
				return $this->app['twig']->render('register.html.twig', array('form' => $form->createView(), 'errors' => $errors, 'username_error' => $username_error));
		    }
		    else {
	           	//encode password
	            $password = $this->app['security.encoder.digest']->encodePassword($registration_data['password'], strtolower($registration_data['username']));
	            $this->app['users']->insert(array(
	                'username'  => strtolower($registration_data['username']),
	                'password'  => $password,
	                'roles'     => 'ROLE_USER',
	                'email'     => $registration_data['email'],
	                'name'      => $registration_data['name'],
	                'forum_name'=> $registration_data['forum_name'],
	                'strava'    => $registration_data['strava'],
	                'metric'	=> $registration_data['metric']
	            ));
	            
	            $names = explode(' ', $registration_data['name']);
        		$firstname = array_shift(array_values($names));
	            return $this->app['twig']->render('success.html.twig', array('message' => $firstname.', you have sucessfully registered your account, please login'));
		    }
	    }
	}
	public function resetPassword(Request $request)
	{
		if($this->app['request']->getMethod() === 'GET') {
			$return_data = null;

			$form = $this->createResetPasswordForm($return_data);
			return $this->app['twig']->render('reset_password.html.twig', array('form' => $form->createView(), 'errors' => null, 'other_error' => null));
		}
		elseif($this->app['request']->getMethod() === 'POST'){
			$reset_data = $request->get('form');
			$form = $this->createRegistrationForm($reset_data);


			$submitted_username = $reset_data['username'];
			$submitted_email = $reset_data['email'];

			$user = $this->app['users']->getUserByUsername($submitted_username);

			$errors = $this->validatePasswordResetForm($reset_data);

	    	if (count($errors) > 0) {
	    		//First validate form
	    		return $this->app['twig']->render('reset_password.html.twig', array('form' => $form->createView(), 'errors' => $errors, 'other_error' => null));
	    	}
	    	else{
	    		//If form is valid check user exists and username matches email.
				if(!$user){
					$other_error = 'The details you entered aren\'t correct.';
					return $this->app['twig']->render('reset_password.html.twig', array('form' => $form->createView(), 'errors' => null, 'other_error' => $other_error));
				}
				else{
					if($user->getEmail() !== $submitted_email){
						$other_error = 'The details you entered aren\'t correct.';
						return $this->app['twig']->render('reset_password.html.twig', array('form' => $form->createView(), 'errors' => null, 'other_error' => $other_error));
					}
				}

	    		//generate new password
				$new_password = $this->randomStr(24);
				$encrypted_password =  $this->app['security.encoder.digest']->encodePassword($new_password, strtolower($user->getUsername()));
				//update user with new password
				$this->app['users']->update(array('password' => $encrypted_password), array('user_id' => $user->getUserId()));
				
				//send email
				$message = \Swift_Message::newInstance()
			        ->setSubject('Century Challenge: Password Reset')
			        ->setFrom(array('century@robcaw.com'))
			        ->setTo(array('robincawser@gmail.com'))
			        ->setBody('Your new password: '.$new_password);
    			$this->app['mailer']->send($message);

				return $this->app['twig']->render('success.html.twig', array('message' => 'A new password has been emailed to you'));	

	    	}


		}
	}
	public function createResetPasswordForm($return_data)
	{
		$form = $this->app['form.factory']->createBuilder('form', $return_data)
	        ->add('username', 'text', array(
	            'label' => 'Username *',
	            'required' => true
	        ))
	        ->add('email', 'text', array(
	            'label' => 'E-mail address *',
	            'required' => true
	        ))
	        ->getForm();
	    return $form;
	}
	public function randomStr($length = 5){
		$chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$count = strlen($chars);
		$pw = '';
		for($i = 0; $i < $length; $i++){
			$index = rand(0, $count -1);
			$pw .= substr($chars, $index, 1);
		}
		return $pw;
	}

}
