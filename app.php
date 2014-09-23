<?php

require_once __DIR__.'/vendor/autoload.php';
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
use Century\Controller\LeaderboardController;
use StravaDL\StravaDownloader;

$app = new Silex\Application();


/**
Register Services
 **/

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
    'twig.path'       => __DIR__ . '/views',
));
$app->register(new DoctrineServiceProvider(), array(
    'db.options' => array(
        'driver' => 'pdo_mysql',
        'host' => getenv('DB_HOST'),
        'dbname' => getenv('DB_NAME'),
        'user' => getenv('DB_USER'),
        'password' => getenv('DB_PASS'),
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

$client_secret = getenv('STRAVA_SECRET');
$client_id = getenv('STRAVA_ID');

$app['stravaDL'] = $app->share(function () use ($client_secret, $client_id){
    return new StravaDownloader($client_secret, $client_id);
});


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

$app['ride.modification.controller'] = $app->share(function() use ($app) {
    return new RideModificationController($app);
});
$app['user.registration.controller'] = $app->share(function() use ($app) {
    return new UserRegistrationController($app);
});
$app['user.profile.controller'] = $app->share(function() use ($app) {
    return new UserProfileController($app);
});
$app['ride.display.controller'] = $app->share(function() use ($app) {
    return new RideDisplayController($app);
});
$app['leaderboard.controller'] = $app->share(function() use ($app) {
    return new LeaderboardController($app);
});

$app['debug'] = true;

/**
Controllers:
 **/

//Ride Modification:
$app->match('/add', "ride.modification.controller:addRidePage");
$app->post('/add/manual', "ride.modification.controller:addRideManual");
$app->post('/add/strava', "ride.modification.controller:addRideStrava");
$app->match('/ride/{id}/edit', "ride.modification.controller:editRide");
$app->match('/ride/{id}/delete', "ride.modification.controller:deleteRide");

//Ride Display:
//$app->get('/rides', "ride_display.controller:allRides");
//$app->get('/ride/{ride_id}', "ride_display.controller:singleRide");

//User Registration:
$app->match('/login', "user.registration.controller:login");
$app->match('/register', "user.registration.controller:register");
$app->match('/resetpassword', "user.registration.controller:resetPassword");

//Profile
$app->get('/profile/{username}', "user.profile.controller:displayProfile");
$app->match('/profile/{username}/edit', "user.profile.controller:editProfile");
$app->match('/profile/{username}/changepassword', "user.profile.controller:changePassword");

//Leaderboard
$app->get('/leaderboard', "leaderboard.controller:leaderboard");
$app->get('/leaderboard/{year}', "leaderboard.controller:leaderboardByYear")->assert('year', '\d{4}');
//Index
$app->get('/', function () use ($app) {
    return $app['twig']->render('index.html.twig', $app['leaderboard.controller']->getLeaderboardData());
});

$app->run();