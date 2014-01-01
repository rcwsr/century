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
        $year = (int) date('Y');
	    $rides = $this->app['rides']->getAllRides(null, null, $year);
	    $users = $this->app['users']->getAllUsers(true, false, true);




	    $months = array();
	    
	    $count_qualified_users = count($rides);
	    foreach (range(1, (int) date('n')) as $month) {
	        
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
	    );
	}
	public function leaderboard()
	{
	    return $this->app['twig']->render('leaderboard.html.twig', $this->getLeaderboardData() );
	}
}
