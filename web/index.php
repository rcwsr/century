<?php

require_once __DIR__.'/../vendor/autoload.php';

use Igorw\Silex\ConfigServiceProvider;
use Silex\Provider\DoctrineServiceProvider;
use Silex\Provider\FormServiceProvider;
use Silex\Provider\SecurityServiceProvider;
use Silex\Provider\TranslationServiceProvider;
use Silex\Provider\TwigServiceProvider;
use Silex\Provider\SessionServiceProvider;
use Silex\Provider\UrlGeneratorServiceProvider;
use Knp\Provider\RepositoryServiceProvider;
use Century\Provider\UserProvider;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;
//use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Validator\Constraints as Assert;
use Century\Controller\RideModificationController;
use Century\Controller\UserRegistrationController;




$app = new Silex\Application();

/**
 Register Services
**/
$app->register(new ConfigServiceProvider(__DIR__ . '/../config/config.yml'));

$app->register(new Silex\Provider\SessionServiceProvider());
$app->register(new Silex\Provider\UrlGeneratorServiceProvider());
$app->register(new Silex\Provider\ServiceControllerServiceProvider());
$app->register(new FormServiceProvider());
$app->register(new TranslationServiceProvider(), array(
    'locale_fallback' => 'en',
));
$app->register(new Silex\Provider\ValidatorServiceProvider());
$app->register(new TwigServiceProvider(), array(
      'twig.path'       => __DIR__ . '/../views',
));
$app->register(new DoctrineServiceProvider(), array(
    'db.options' => array(
        'driver' => 'pdo_mysql',
        'host' => $app['db.host'],
        'dbname' => $app['db.name'],
        'username' => $app['db.username'],
        'password' => $app['db.password']
    )
));
$app->register(new RepositoryServiceProvider(), array('repository.repositories' => array(
    'rides'      => 'Century\\Repository\\RideRepo',
    'users'      => 'Century\\Repository\\UserRepo'
)));
$app->register(new Silex\Provider\SecurityServiceProvider(), array(
    'security.firewalls' => array( 
        'default' => array(
            'pattern' => '^.*$',
            'anonymous' => true,
            'form' => array('login_path' => '/login', 'check_path' => '/login_check'),
            'logout' => array('logout_path' => '/logout'),
            'users' => $app->share(function() use ($app) {
                // raw password is foo
                return new Century\Provider\UserProvider($app['db']);
            })
        ),

    ),
    'security.access_rules' => array(
        // You can rename ROLE_USER as you wish
        array('^/add', 'ROLE_USER'),
        array('^/add/0|[1-9][0-9]*/edit', 'ROLE_USER'),
        array('^/add/0|[1-9][0-9]*/delete', 'ROLE_USER')
    )
));

/**
 Register Controllers: 
**/

$app['ride_modification.controller'] = $app->share(function() use ($app) {
    return new RideModificationController($app);
});
$app['user_registration.controller'] = $app->share(function() use ($app) {
    return new UserRegistrationController($app);
});


$app['debug'] = true;

/**
 Controllers: 
**/

//Ride Modification:
$app->match('/add', "ride_modification.controller:addRidePage");
$app->post('/add/manual', "ride_modification.controller:addRideManual");
$app->post('/add/strava', "ride_modification.controller:addRideStrava");
$app->match('/ride/{id}/edit', "ride_modification.controller:editRide");
$app->match('/ride/{id}/delete', "ride_modification.controller:deleteRide");


//User Registration:
$app->match('/login', "user_registration.controller:login");
$app->match('/register', "user_registration.controller:register");


$app->get('/', function () use ($app) {
    //Show leaderboard and latest rides
    $rides = $app['rides']->getAllRides();
    $users = $app['users']->getAllUsers(true, true);

    $year = (int) date('Y');
    $months = array();
    
    foreach (range(1, (int) date('n')) as $month) {
         $months[$month] = array(
            'date' => date('F', mktime(0, 0, 0, $month)),
           
            'rides' => $app['rides']->getAllRides(null, $month, $year)
        );
    }

    $disqualified_users = $app['users']-> getDisqualifiedUsers();



    return $app['twig']->render('index.html.twig', array(
        'disqualified_users' => $disqualified_users,
        'users' => $users,
        'rides' => $rides,
        'months' => $months,
        'year' => $year,
        'userRepo' => $app['users']
    ));
});

$app->get('/rides', function () use ($app) {
        $rides = $app['rides']->getAllRides();

        $months = array();
        $year = (int) date('Y');
        foreach (range((int) date('n'), 1) as $month) {
        $months[$month] = array(
            'date' => date('F', mktime(0, 0, 0, $month)),          
            'rides' => $app['rides']->getAllRides(null, $month, $year)
        );
    }
    return $app['twig']->render('rides.html.twig', array(
        'months' => $months,
        'userRepo' => $app['users']
    ));
});

$app->get('/profile/{username}', function ($username) use ($app) {
    //Show Rides for specific user
    
    $username = $app->escape($username);

    $user = $app['users']->getUserByUsername($username);
    
    
    if(!$user){
         $app->abort(404, "User $username does not exist");
    }

    $months = array();
    $year = (int) date('Y');
    foreach (range((int) date('n'), 1) as $month) {
        $months[$month] = array(
            'date' => date('F', mktime(0, 0, 0, $month)),          
            'rides' => $app['rides']->getAllRides($user->getUserId(), $month, $year)
        );
    }
    $year = (int) date('Y');
    $month =  (int) date('n');
    $page_data = array(
        'total_km_year' => $user->getTotalKm(null, $year),
        'total_km_month' => $user->getTotalKm($month, $year),
        'total_points_year' => $user->getTotalPoints(null, $year),
        'total_points_month' => $user->getTotalPoints($month, $year),
        'centuries_year' => $user->getNoOfCenturies(null, $year),
        'centuries_month' => $user->getNoOfCenturies($month, $year),
        );
    return $app['twig']->render('profile.html.twig', array(
        'page_data' => $page_data,
        'user' => $user,
        'months' => $months,
        'userRepo' => $app['users']
    ));
});

$app->get('/ride/{ride_id}', function ($ride_id) use ($app) {
    $ride_id = $app->escape($ride_id);

    $ride = $app['rides']->getRideById($ride_id);

    if(!$ride){
         $app->abort(404, "Ride #$ride_id does not exist");
    }

    $user = $app['users']->getUserById($ride->getUserId());


    $page_data = array(
        'user' => $user,
        'ride' => $ride
        );
    return $app['twig']->render('ride_single.html.twig', $page_data);
});

$app->run();