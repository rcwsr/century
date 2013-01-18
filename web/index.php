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
use Endurance\Strava\StravaClient;
use Symfony\Component\Validator\Constraints as Assert;


// Do I need these here:
use Buzz\Browser;
use Buzz\Message\Form\FormRequest;
use Buzz\Message\Response;
use Buzz\Util\Url;
use Buzz\Client\Curl;

$app = new Silex\Application();

$app->register(new ConfigServiceProvider(__DIR__ . '/../config/config.yml'));

$app->register(new Silex\Provider\SessionServiceProvider());
$app->register(new Silex\Provider\UrlGeneratorServiceProvider());
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
/*$app->register(new Silex\Provider\SecurityServiceProvider(), array(
    'security.firewalls' => array( 
        'add' => array(
            'pattern' => '^/add',
            'form' => array('login_path' => '/login', 'check_path' => '/add/login_check'),
            'logout' => array('logout_path' => '/add/logout'),
            'users' => $app->share(function() use ($app) {
                // raw password is foo
                return new Century\Provider\UserProvider($app['db']);
            })
            
        ),
    ),
));*/

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
    )
));


$app['debug'] = true;

$app->match('/login', function(Request $request) use ($app) {
    return $app['twig']->render('login.html.twig', array(
        'error'         => $app['security.last_error']($request),
        'last_username' => $app['session']->get('_security.last_username'),
    ));
});

$app->get('/', function () use ($app) {
    //Show leaderboard and latest rides
    $rides = $app['rides']->getAllRides();
    $users = $app['users']->getAllUsers(false);

    $year = (int) date('Y');
    $months = array();
    foreach (range(1, (int) date('n')) as $month) {
         $months[$month] = array(
            'date' => date('F', mktime(0, 0, 0, $month)),
           
            'rides' => $app['rides']->getAllRides(null, $month, $year)
        );
    }

    return $app['twig']->render('index.html.twig', array(
        'users' => $users,
        'rides' => $rides,
        'months' => $months,
        'year' => $year,
        'userRepo' => $app['users']
    ));
});

$app->get('/rides', function () use ($app) {
    //All Rides
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
            'rides' => $user->getRides($month, $year)
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

$app->match('/add', function (Request $request) use ($app) {
        if ($app['request']->getMethod() === 'POST') {
            $return_data = array('return_form_data' => $app['request']->get('return_form_data'),
                                 'errors' => $app['request']->get('errors'),
                                 'strava_errors' => $app['request']->get('strava_errors')
                                 );
        }
        else{
            $return_data = null;
        }

        $form = $app['form.factory']->createBuilder('form', $return_data['return_form_data'])
                ->add('date', 'text', array(
                    'label' => 'Date of ride',
                    'required' => true
                ))
                ->add('km', 'text', array(
                    'label' => 'Distance',
                    'required' => true
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

        $form_strava = $app['form.factory']->createBuilder('form', $return_data['return_form_data'])
                ->add('strava_ride_id', 'text', array(
                    'required' => true
                ))
                ->getForm();

        return $app['twig']->render('add2.html.twig', array(
            'form' => $form->createView(), 
            'form_strava' => $form_strava->createView(), 
            'errors' => $return_data['errors'],
            'strava_errors' => $return_data['strava_errors'],
            'return_form_data' => $return_data['return_form_data']
        ));
    
});

$app->post('/add/manual', function (Request $request) use ($app) {
    $token = $app['security']->getToken();
    if (null !== $token) {
        $user = $token->getUser();
    }
    $user_id = $user->getUserId();

    $data = $request->get('form');
    $constraint = new Assert\Collection(array(
                        'date' => array(new Assert\Date(), new Assert\NotBlank()),
                        'km' => array(new Assert\Regex(array('pattern' => '(0|[1-9][0-9]*)')),new Assert\NotBlank()),
                        'average_speed' => array(new Assert\Regex(array('pattern' => '(0|[1-9][0-9]*)')))
                        ));
    $validation_data = array(
        'date' => $data['date'],
        'km' => $data['km'],
        'average_speed' => $data['average_speed']
    );
    $errors = $app['validator']->validateValue($validation_data, $constraint);
    
   
    if (count($errors) > 0) {
        $return_data = array(
            'return_form_data' => $data,
            'errors' => $errors,
            'strava_errors' => null
        );
        $subRequest = Request::create('/add', 'POST', $return_data);
        return $app->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
    }
    else {
        $prepared_data = array(
                'user_id' => $user_id,
                'km' => $data['km'],
                'url' => $data['url'],
                'date' => $data['date'],
                'average_speed' => $data['average_speed'],
                'strava_ride_id' => null
            );
        $app['rides']->insert($prepared_data);
        return $app['twig']->render('success.html.twig', array('message' => 'Your ride was added successfully'));
    }
   
});

$app->post('/add/strava', function (Request $request) use ($app) {
    
    //Get Logged in user_id
    $token = $app['security']->getToken();
    if (null !== $token) {
        $user = $token->getUser();
    }
    $user_id = $user->getUserId();

    //Fetch form data to be submitted.
    $data = $request->get('form');

    //Validation
    $constraint = new Assert\Collection(array(
        'strava_ride_id' => array(new Assert\NotBlank(), new Assert\MinLength(8))));
    $validation_data = array(
        'strava_ride_id' => $data['strava_ride_id']
        );
    //Get any errors
    $errors = $app['validator']->validateValue($validation_data, $constraint);
    
    if (count($errors) > 0) {
        //If there are errors, send user back to the add form with errors.
        
        //Array to be returned back to /add form
        $return_data = array(
            'return_form_data' => $data,
            'strava_errors' => $errors,
            'errors' => null
        );
        $subRequest = Request::create('/add', 'POST', $return_data);
        return $app->handle($subRequest, HttpKernelInterface::SUB_REQUEST);  
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
                                   'average_speed' => $ride_details['ride']['average_speed'],
                                   'strava_ride_id' => $ride_details['id'],
                                    );
            //Insert array to db.
            $app['rides']->insert($prepared_data);
            //Send user to success page
            return $app['twig']->render('success.html.twig', array('message' => 'Your ride was added successfully'));
        }
        else{
            throw new \InvalidArgumentException('The ride ID is invalid');
        }
    }

});


$app->get('/ride/{$id}/edit', function () use ($app) {
    //Edit a ride. 
    //Ensure logged in user matches the user id of ride (throw 404 if not?)
});

$app->match('/register', function () use ($app) {
    //User registration
    //Needs validation
    //Make sure username does not exist.
    //Validate email maybe
    //password confirmation
     $form = $app['form.factory']->createBuilder('form')
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

    if ($app['request']->getMethod() === 'POST') {
        $form->bind($app['request']);
        if ($form->isValid()) {
            //get form data
            $data = $form->getData();

            //encode password
            $password = $app['security.encoder.digest']->encodePassword($data['password'], strtolower($data['username']));

           
            $app['users']->insert(array(
                'username'  => strtolower($data['username']),
                'password'  => $password,
                'roles'     => 'ROLE_USER',
                'email'     => $data['email'],
                'name'      => $data['name'],
                'forum_name'=> $data['forum_name'],
                'strava'    => $data['strava']
            ));
            

            return $app->redirect('/');
        }
    }

    return $app['twig']->render('register.html.twig', array('form' => $form->createView()));
});






$app->run();
