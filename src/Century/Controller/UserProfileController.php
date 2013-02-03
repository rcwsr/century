<?php

namespace Century\Controller;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;


class UserProfileController
{
	protected $app;

	public function __construct(Application $app)
	{
		$this->app = $app;
	}
	public function getLoggedInUser()
	{
		$token = $this->app['security']->getToken();
	    if (null !== $token) {
	        $user = $token->getUser();
	    }
	    return $user;
	}
	public function validateProfileForm(array $data)
	{	
	    $constraint = new Assert\Collection(array(
	                        'email' => new Assert\NotBlank(),
	                        'name' => new Assert\NotBlank(),
	                        'forum_name' => new Assert\NotBlank()
	                        ));
	    $validation_data = array(
			'email' => $data['email'],
			'name' => $data['name'],
			'forum_name' => $data['forum_name']
	    );
	    $errors = $this->app['validator']->validateValue($validation_data, $constraint);
		
		return $errors;
	}
	public function displayProfile(Request $request){
		//Show Rides for specific user
	    
	    $username = $this->app->escape($request->get('username'));

	    $user = $this->app['users']->getUserByUsername($username);
	    
	    
	    if(!$user){
	         $this->app->abort(404, "User $username does not exist");
	    }

	    $months = array();
	    $year = (int) date('Y');
	    foreach (range((int) date('n'), 1) as $month) {
	        $months[$month] = array(
	            'date' => date('F', mktime(0, 0, 0, $month)),          
	            'rides' => $this->app['rides']->getAllRides($user->getUserId(), $month, $year)
	        );
	    }
	    $year = (int) date('Y');
	    $month =  (int) date('n');
	    $page_data = array(
	        'total_distance_year' => $user->getTotalDistance(null, $year),
	        'total_distance_month' => $user->getTotalDistance($month, $year),
	        'total_points_year' => $user->getTotalPoints(null, $year),
	        'total_points_month' => $user->getTotalPoints($month, $year),
	        'centuries_year' => $user->getNoOfCenturies(null, $year),
	        'centuries_month' => $user->getNoOfCenturies($month, $year),
	        );
	    return $this->app['twig']->render('profile.html.twig', array(
	        'page_data' => $page_data,
	        'user' => $user,
	        'months' => $months,
	        'userRepo' => $this->app['users']
	    ));
	}
	public function editProfile(Request $request)
	{
		$username = $this->app->escape($request->get('username'));
		$user = $this->app['users']->getUserByUsername($username);
		$logged_in_user_id = $this->getLoggedInUser()->getUserId();

		if ($this->app['request']->getMethod() === 'GET') {
			
			if($user->getUserId()!== $logged_in_user_id){
	        	$this->app->abort(401, "You can't edit other user's profiles!");
	    	}
		    
		    
		    if(!$user){
		         $this->app->abort(404, "User $username does not exist");
		    }

		    $user_data = array(

		    	'name' => $user->getName(),
		    	'email' => $user->getEmail(),
		    	'forum_name' => $user->getForumName(),
		    	'metric' => $user->isMetric(),
		    	'strava' => $user->getStrava()
		    	);

		    $form = $this->createEditProfileForm($user_data);
		    return $this->app['twig']->render('edit_profile.html.twig', array(
                'form' => $form->createView(), 
                'errors' => null,
            ));

		}
		elseif($this->app['request']->getMethod() === 'POST'){

			if($user->getUserId()!== $logged_in_user_id){
	        	$this->app->abort(401, "You can't edit other user's profiles!");
	    	}

			$user_data = $request->get('form');
	    	$form = $this->createEditProfileForm($user_data);

	    	$errors = $this->validateProfileForm($user_data);

			if (count($errors) > 0) {
				return $this->app['twig']->render('edit_profile.html.twig', array(
	                'form' => $form->createView(), 
	                'errors' => $errors,
            	));
		    }
		    else {
		    	$this->app['users']->update($user_data, array('user_id' => $user->getUserId()));
		    	return $this->app['twig']->render('success.html.twig', array('message' => $user->getFirstname().', you have sucessfully updated your profile'));
		    }
		}

	    
	}
	public function createEditProfileForm($return_data)
	{
		$form = $this->app['form.factory']->createBuilder('form', $return_data)
	        ->add('name', 'text', array(
	            'label' => 'Full name (first name and surname - this will only be shown to logged in users.) *',
	            'required' => true
	        ))
	        ->add('email', 'text', array(
	            'label' => 'E-mail address *',
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
	public function changePassword(Request $request)
	{
		$username = $this->app->escape($request->get('username'));
		$user = $this->app['users']->getUserByUsername($username);
		$logged_in_user_id = $this->getLoggedInUser()->getUserId();

		if($user->getUserId()!== $logged_in_user_id){
	        	$this->app->abort(401, "You can't edit other user's profiles!");
	    }
	    if(!$user){
	         $this->app->abort(404, "User $username does not exist");
	    }


	    $form = $this->app['form.factory']->createBuilder('form', null)
			->add('current_password', 'password', array(
			    'label' => 'Current password *',
			    'required' => false,
			    'constraints' => array(new Assert\NotBlank())
			))
	        ->add('new_password', 'repeated', array(
			    'type' => 'password',
			    'invalid_message' => 'The password fields must match.',
			    'required' => false,
			    'first_options'  => array('label' => 'New password *'),
			    'second_options' => array('label' => 'Repeat password *'),
			    'constraints' => array(new Assert\NotBlank())
			))
	        ->getForm();


		if($this->app['request']->getMethod() === 'POST'){
			$form->bind($request);

			if ($form->isValid()) {
           		$data = $form->getData();

           		$current_password = $data['current_password'];
           		$encrypted_password =  $this->app['security.encoder.digest']->encodePassword($current_password, strtolower($user->getUsername()));

           		if($encrypted_password == $user->getPassword()){
       				$new_password = $data['new_password'];
       				$encrypted_password_new =  $this->app['security.encoder.digest']->encodePassword($new_password, strtolower($user->getUsername()));
       				$this->app['users']->update(array('password' => $encrypted_password_new), array('user_id' => $user->getUserId()));
       				return $this->app['twig']->render('success.html.twig', array('message' => $user->getFirstname().', you have sucessfully changed your password.'));
           		}
           		else{
           			$errors = 'The password you entered is incorrect';
           			return $this->app['twig']->render('change_password.html.twig', array('form' => $form->createView(), 'errors' => $errors));
           		}

        	}
        	else{
        		
        		//$this->app->abort(500, "Not valid");
        		//return $this->app['twig']->render('success.html.twig', array('message' => $user->getFirstname().', you have sucessfully changed your password.'));
        	}
		}


		return $this->app['twig']->render('change_password.html.twig', array('form' => $form->createView(), 'errors' => null));
	}
}