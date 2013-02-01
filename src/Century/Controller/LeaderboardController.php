<?php

namespace Century\Controller;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;


class LeaderboardController
{
	protected $app;

	public function __construct(Application $app)
	{
		$this->app = $app;
	}

	public function getLeaderboardData(){
		//Show leaderboard and latest rides
	    $rides = $this->app['rides']->getAllRides();
	    $users = $this->app['users']->getAllUsers(true, true);

	    $year = (int) date('Y');
	    $months = array();
	    
	    $count_qualified_users = count($rides);
	    foreach (range((int) date('n'), 1) as $month) {
	        
	         $months[$month] = array(
	            'date' => date('F', mktime(0, 0, 0, $month)),
	           
	            'rides' => $this->app['rides']->getAllRides(null, $month, $year)
	        );
	    }

	    $disqualified_users = $this->app['users']-> getDisqualifiedUsers();

	    return array(
	        'disqualified_users' => $disqualified_users,
	        'count_qualified_users' => $count_qualified_users,
	        'users' => $users,
	        'rides' => $rides,
	        'months' => $months,
	        'year' => $year,
	        'userRepo' => $this->app['users']
	    );
	}
	public function leaderboard()
	{
	    return $this->app['twig']->render('leaderboard.html.twig', $this->getLeaderboardData() );
	}
}