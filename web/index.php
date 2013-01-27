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
use Century\Controller\UserProfileController;
use Century\Controller\RideDisplayController;
use Century\Form\DataTransformer\MilesToKmDataTransformer;
use Century\Form\DataTransformer\KmToMilesDataTransformer;

$app = new Silex\Application();


/**
 Register Services
**/
$app->register(new ConfigServiceProvider(__DIR__ . '/../config/config.yml'));

$app->register(new Silex\Provider\SessionServiceProvider());
$app->register(new Silex\Provider\UrlGeneratorServiceProvider());
$app->register(new Silex\Provider\ServiceControllerServiceProvider());
$app->register(new Silex\Provider\SwiftmailerServiceProvider());
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
                return new Century\Provider\UserProvider($app['db']);
            })
        ),

    ),
    'security.access_rules' => array(
        // You can rename ROLE_USER as you wish
        array('^/add', 'ROLE_USER'),
        array('^/add/0|[1-9][0-9]*/edit', 'ROLE_USER'),
        array('^/add/0|[1-9][0-9]*/delete', 'ROLE_USER'),
        array('^/profile/^.*$/edit', 'ROLE_USER')
    )
));

//Twig Options/Extensions
$app['twig'] = $app->share($app->extend('twig', function($twig, $app) {
    $twig->addFilter('miles', new \Twig_Filter_Function('miles'));
    $twig->addFilter('km', new \Twig_Filter_Function('km'));
    function miles($km)
    {
        return round((int) $km * 0.621371192, 1);
    }
    function km($km)
    {
        return $km;
    }
    return $twig;
}));
 
/*
$app['miles_to_km'] = $app->share(function() use ($app) {
    return new MilesToKmDataTransformer($app);
});
$app['km_to_miles'] = $app->share(function() use ($app) {
    return new KmToMilesDataTransformer($app);
});
*/

/**
 Register Controllers: 
**/

$app['ride_modification.controller'] = $app->share(function() use ($app) {
    return new RideModificationController($app);
});
$app['user_registration.controller'] = $app->share(function() use ($app) {
    return new UserRegistrationController($app);
});
$app['user_profile.controller'] = $app->share(function() use ($app) {
    return new UserProfileController($app);
});
$app['ride_display.controller'] = $app->share(function() use ($app) {
    return new RideDisplayController($app);
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

//Ride Display:
$app->get('/rides', "ride_display.controller:allRides");
$app->get('/ride/{ride_id}', "ride_display.controller:singleRide");

//User Registration:
$app->match('/login', "user_registration.controller:login");
$app->match('/register', "user_registration.controller:register");
$app->match('/resetpassword', "user_registration.controller:resetPassword");

//Profile
$app->get('/profile/{username}', "user_profile.controller:displayProfile");
$app->match('/profile/{username}/edit', "user_profile.controller:editProfile");
$app->match('/profile/{username}/changepassword', "user_profile.controller:changePassword");
//Index
$app->get('/', function () use ($app) {
    //Show leaderboard and latest rides
    $rides = $app['rides']->getAllRides();
    $users = $app['users']->getAllUsers(true, true);

    $year = (int) date('Y');
    $months = array();
    
    $count_qualified_users = count($rides);
    foreach (range(1, (int) date('n')) as $month) {
        
         $months[$month] = array(
            'date' => date('F', mktime(0, 0, 0, $month)),
           
            'rides' => $app['rides']->getAllRides(null, $month, $year)
        );
    }

    $disqualified_users = $app['users']-> getDisqualifiedUsers();



    return $app['twig']->render('index.html.twig', array(
        'disqualified_users' => $disqualified_users,
        'count_qualified_users' => $count_qualified_users,
        'users' => $users,
        'rides' => $rides,
        'months' => $months,
        'year' => $year,
        'userRepo' => $app['users']
    ));
});

$app->run();