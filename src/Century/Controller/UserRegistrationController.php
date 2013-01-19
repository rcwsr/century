<?php

namespace Century\Controller;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;


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

	public function register(Request $request)
	{
		//User registration
	    //Needs validation
	    //Make sure username does not exist.
	    //Validate email maybe
	    //password confirmation
	     $form = $this->app['form.factory']->createBuilder('form')
	        ->add('username', 'text', array(
	            'label' => 'Username',
	            'required' => true
	        ))
	        ->add('name', 'text', array(
	            'label' => 'Full name (first name and surname - this will only be shown to logged in users.)',
	            'required' => false
	        ))
	        ->add('email', 'text', array(
	            'label' => 'E-mail address',
	            'required' => true
	        ))
	        ->add('password', 'text', array(
	            'label' => 'Password',
	            'required' => true
	        ))
	        ->add('forum_name', 'text', array(
	            'label' => 'LFCC forum username',
	            'required' => true
	        ))
	        ->add('strava', 'text', array(
	            'label' => 'Strava athlete ID',
	            'required' => false
	        ))
	        ->getForm();

	    if ($this->app['request']->getMethod() === 'POST') {
	        $form->bind($this->app['request']);
	        if ($form->isValid()) {
	            //get form data
	            $data = $form->getData();

	            //encode password
	            $password = $this->app['security.encoder.digest']->encodePassword($data['password'], strtolower($data['username']));

	           
	            $this->app['users']->insert(array(
	                'username'  => strtolower($data['username']),
	                'password'  => $password,
	                'roles'     => 'ROLE_USER',
	                'email'     => $data['email'],
	                'name'      => $data['name'],
	                'forum_name'=> $data['forum_name'],
	                'strava'    => $data['strava']
	            ));
	            

	            return $this->app->redirect('/');
	        }
	    }

	    return $this->app['twig']->render('register.html.twig', array('form' => $form->createView()));
	}
}