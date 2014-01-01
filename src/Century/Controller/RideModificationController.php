<?php

namespace Century\Controller;

use Century\Validator\Constraints\DateRange;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Endurance\Strava\StravaClient;
use Buzz\Browser;
use Buzz\Message\Form\FormRequest;
use Buzz\Message\Response;
use Buzz\Util\Url;
use Buzz\Client\Curl;

class RideModificationController
{
	protected $app;

	public function __construct(Application $app)
	{
		$this->app = $app;
	}

	public function createRideForm($return_data)
	{
		$form = $this->app['form.factory']->createBuilder('form', $return_data)
                ->add('date', 'text', array(
                    'label' => 'Date of ride',
                    'required' => false
                ))
                ->add('km', 'text', array(
                    'label' => 'Distance',
                    'required' => false
                ))
                ->add('average_speed', 'text', array(
                    'label' => 'Average Speed',
                    'required' => false
                ))
                ->add('url', 'text', array(
                    'label' => 'Link to ride',
                    'required' => false
                ))
                ->add('details', 'textarea', array(
                    'label' => 'Notes',
                    'required' => false
                ))
                ->getForm();
        return $form;
	}

	public function createStravaRideForm($return_data)
	{
		$form = $this->app['form.factory']->createBuilder('form', $return_data)
                ->add('strava_ride_id', 'text', array(
                    'required' => false
                ))
                ->getForm();
        return $form;
	}

	public function validateManualForm(array $data)
	{	
	    $constraint = new Assert\Collection(array(
	                        'date' => array(new Assert\Date(), new Assert\NotBlank(), new DateRange()),
	                        'km' => array(new Assert\Regex(array('pattern' => '(0|[1-9][0-9]*)')),new Assert\NotBlank()),
	                        'average_speed' => array(new Assert\Regex(array('pattern' => '(0|[1-9][0-9]*)')))
	                        ));
	    $validation_data = array(
	        'date' => $data['date'],
	        'km' => $data['km'],
	        'average_speed' => $data['average_speed']
	    );
	    $errors = $this->app['validator']->validateValue($validation_data, $constraint);
		
		return $errors;
	}

	public function validateStravaForm($data)
	{
		$constraint = new Assert\Collection(array(
	        'strava_ride_id' => array(new Assert\NotBlank(), new Assert\Length(array('min' => 8)))));
	    $validation_data = array(
	        'strava_ride_id' => $data['strava_ride_id']
	        );
	    //Get any errors
	    $errors = $this->app['validator']->validateValue($validation_data, $constraint);
		return $errors;
	}

	public function getLoggedInUser()
	{
		$token = $this->app['security']->getToken();
	    if (null !== $token) {
	        $user = $token->getUser();
	    }
	    return $user;
	}
	public function addRidePage(Request $request)
	{
  		if ($this->app['request']->getMethod() === 'POST') {
            $return_data = array('return_form_data' => $this->app['request']->get('return_form_data'),
                                 'errors' => $this->app['request']->get('errors'),
                                 'strava_errors' => $this->app['request']->get('strava_errors')
                                 );
        }
        else{
            $return_data = null;
        }

        $form = $this->createRideForm($return_data['return_form_data']);

        $form_strava = $this->createStravaRideForm($return_data['return_form_data']);


        return $this->app['twig']->render('add2.html.twig', array(
            'form' => $form->createView(), 
            'form_strava' => $form_strava->createView(), 
            'errors' => $return_data['errors'],
            'strava_errors' => $return_data['strava_errors'],
            'return_form_data' => $return_data['return_form_data']
        ));
	}
	
	public function addRideStrava(Request $request)
	{
	    //Get Logged in user_id
	    $user_id = $this->getLoggedInUser()->getUserId();

	    //Fetch form data to be submitted.
	    $data = $request->get('form');

	    //Validation
	    $errors = $this->validateStravaForm($data);
	    
	    if (count($errors) > 0) {
	        //If there are errors, send user back to the add form with errors.
	        
	        //Array to be returned back to /add form
	        $return_data = array(
	            'return_form_data' => $data,
	            'strava_errors' => $errors,
	            'errors' => null
	        );
	        $subRequest = Request::create('/add', 'POST', $return_data);
	        return $this->app->handle($subRequest, HttpKernelInterface::SUB_REQUEST);  
	    }
	    else {
	        //If no errors, prepare strava data for submission
	        $browser = new Browser(new Curl());
	        $client = new StravaClient($browser);
	        $ride_details = $client->getRideDetails($data['strava_ride_id']);

	        //Check if a strava activity could be fetched
	        if(isset($ride_details['ride']['id'])){
	            //This should probably not be in the controller.

	            //Convert date to database compatible date
	            $date = \DateTime::createFromFormat('Y-m-d\TH:i:s\Z', $ride_details['ride']['start_date_local']);
	            //prepare an array of data to be submitted
	            $prepared_data = array('user_id' => $user_id,
	                                   'km' => round($ride_details['ride']['distance'] / 1000, 1),
	                                   'url' => 'http://app.strava.com/activities/'. (string) $ride_details['id'],
	                                   'date' => $date->format('Y-m-d'),
	                                   'average_speed' => $ride_details['ride']['average_speed'] * 3.6, // Convert from m/s to km/h
	                                   'strava_ride_id' => $ride_details['id'],
	                                    );
	            //Insert array to db.
	            $this->app['rides']->insert($prepared_data);
	            //Send user to success page
	            return $this->app['twig']->render('success.html.twig', array('message' => 'Your ride was added successfully'));
	        }
	        else{
	            throw new \InvalidArgumentException('The ride ID is invalid');
	        }
	    }
	}
	
	public function addRideManual(Request $request)
	{
		
	    $user_id = $this->getLoggedInUser()->getUserId();

	    $data = $request->get('form');
	    //validate
	    $errors = $this->validateManualForm($data);
	   
	    if (count($errors) > 0) {
	        $return_data = array(
	            'return_form_data' => $data,
	            'errors' => $errors,
	            'strava_errors' => null
	        );
	        $subRequest = Request::create('/add', 'POST', $return_data);
	        return $this->app->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
	    }
	    else {
	    	if(!$this->getLoggedInUser()->isMetric()){
	    		$distance = $data['km'] * 1.609344;
	    		$ave =  $data['average_speed'] * 1.609344;
	    	}
	    	else{
	    		$distance = $data['km'];
	    		$ave = $data['average_speed'];
	    	}
	        $prepared_data = array(
	                'user_id' => $user_id,
	                'km' => $distance,
	                'url' => $data['url'],
	                'date' => $data['date'],
	                'average_speed' => $ave,
	                'strava_ride_id' => null
	            );
	        $this->app['rides']->insert($prepared_data);
	        return $this->app['twig']->render('success.html.twig', array('message' => 'Your ride was added successfully'));
	    }
	}
	public function editRide(Request $request)
	{
	 	$ride_id = $this->app->escape($request->get('id'));
	   
	    $ride = $this->app['rides']->getRideById($this->app->escape($ride_id));
	    
	    //Check if ride exists.
	    if(!$ride){
	         $this->app->abort(404, "Ride does not exist");
	    }

	    //Check user is allowed to view this.
	    //Get logged in user
	    $logged_in_user_id = $this->getLoggedInUser()->getUserId();
	    $ride_user_id = $ride->getUserId();

	    if($ride_user_id !== $logged_in_user_id){
	        $this->app->abort(401, "You can't edit other user's rides!");
	    }

	    if ($this->app['request']->getMethod() === 'GET') {

	    	//Until I can figure out data transformers
	    	//Convert to miles.
	    	if(!$this->getLoggedInUser()->isMetric()){
	    		$distance = round($ride->getDistance() * 0.621371192, 1);
	    		$ave =  round($ride->getAverageSpeed() * 0.621371192, 1);
	    	}
	    	else{
	    		$distance = $ride->getDistance();
	    		$ave = $ride->getAverageSpeed();
	    	}


	    	$ride_data = array(
	    		'ride_id' =>$ride->getRideId(),
	            'date' => $ride->getDate()->format('Y-m-d'),
	            'km' => $distance,
	            'average_speed' => $ave,
	            'url' => $ride->getUrl(),
	            'details' => $ride->getDetails()
	        );
	    	$intial_data['return_form_data'] = $ride_data;

	    	$form = $this->createRideForm($intial_data['return_form_data']);

	    	return $this->app['twig']->render('edit.html.twig', array(
                'form' => $form->createView(), 
                'errors' => null,
            ));
	    }
	    elseif($this->app['request']->getMethod() === 'POST'){
	    	
	    	$form_data = $request->get('form');

	    	//Until I can figure out data transformers
	    	//Convert to km.
	    	if(!$this->getLoggedInUser()->isMetric()){
	    		$distance = $form_data['km'] * 1.609344;
	    		$ave =  $form_data['average_speed'] * 1.609344;
	    	}
	    	else{
	    		$distance = $form_data['km'];
	    		$ave = $form_data['average_speed'];
	    	}

	    	$ride_data = array(
	    		'ride_id' => $request->get('id'),
	            'date' => $form_data['date'],
	            'km' => $distance,
	            'average_speed' => $ave,
	            'url' => $form_data['url'],
	            'details' => $form_data['details']
	        );

	    	$errors = $this->validateManualForm($ride_data);

	    	$form = $this->createRideForm($ride_data);


	    	if (count($errors) > 0) {
		        return $this->app['twig']->render('edit.html.twig', array(
	                'form' => $form->createView(), 
	                'errors' => $errors,
            	));
		    }
		    else {

		    	$this->app['rides']->update($ride_data, array('ride_id' => $request->get('id')));

		    	return $this->app['twig']->render('success.html.twig', array('message' => 'Your ride was edited successfully'));
			}
	    }

	}
	public function deleteRide(Request $request)
	{
		$ride_id = $this->app->escape($request->get('id'));
	   
	    $ride = $this->app['rides']->getRideById($this->app->escape($ride_id));
	    
	    //Check if ride exists.
	    if(!$ride){
	         $this->app->abort(404, "Ride does not exist");
	    }

	    //Check user is allowed to view this.
	    //Get logged in user
	    $logged_in_user_id = $this->getLoggedInUser()->getUserId();
	    $ride_user_id = $ride->getUserId();

	    //check user ids match
	    if($ride_user_id !== $logged_in_user_id){
	        $this->app->abort(401, "You can't delete other user's rides!");
	    }

	    if($this->app['request']->getMethod() === 'GET'){
		    return $this->app['twig']->render('delete.html.twig', array('referer' => $request->headers->get('referer')));
		}
	    elseif($this->app['request']->getMethod() === 'POST'){
	    	$this->app['rides']->delete(array('ride_id' => $ride_id));
	    	return $this->app['twig']->render('success.html.twig', array('message' => 'Your ride was deleted successfully'));
	    }


	}
}
