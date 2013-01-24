<?php

namespace Century\Controller;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;


class RideDisplayController
{
	protected $app;

	public function __construct(Application $app)
	{
		$this->app = $app;
	}

	public function allRides()
	{
	 	$rides = $this->app['rides']->getAllRides();

	        $months = array();
	        $year = (int) date('Y');
	        foreach (range((int) date('n'), 1) as $month) {
	        $months[$month] = array(
	            'date' => date('F', mktime(0, 0, 0, $month)),          
	            'rides' => $this->app['rides']->getAllRides(null, $month, $year)
	        );
	    }
	    return $this->app['twig']->render('rides.html.twig', array(
	        'months' => $months,
	        'userRepo' => $this->app['users']
	    ));
	}
	public function singleRide(Request $request)
	{
	    $ride_id = $this->app->escape($request->get('ride_id'));

	    $ride = $this->app['rides']->getRideById($ride_id);

	    if(!$ride){
	         $this->app->abort(404, "Ride #$ride_id does not exist");
	    }

	    $user = $this->app['users']->getUserById($ride->getUserId());


	    $page_data = array(
	        'user' => $user,
	        'ride' => $ride
	        );
	    return $this->app['twig']->render('ride_single.html.twig', $page_data);
	}
}