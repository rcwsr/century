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
use Symfony\Component\Security\Core\Exception\AuthenticationException;

$app = new Silex\Application();

$app->register(new ConfigServiceProvider(__DIR__ . '/../config/config.yml'));

$app->register(new Silex\Provider\SessionServiceProvider());
$app->register(new Silex\Provider\UrlGeneratorServiceProvider());
$app->register(new FormServiceProvider());
$app->register(new TranslationServiceProvider(), array(
    'locale_fallback' => 'en',
));
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
));




$app->get('/', function () use ($app) {
    //Show leaderboard and latest rides
    $rides = $app['rides']->getAllRides();
    $users = $app['users']->getAllUsers();

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
        'year' => $year
    ));
});

$app->get('/rides', function () use ($app) {
 return 'he';
});

$app->get('/rides/{username}', function ($username) use ($app) {
    //Show Rides for specific user
   
    $user = $app['users']->getUserByUsername($username);
    
    if($user == null){
        throw new \InvalidArgumentException('User does not exist');
    }

    $months = array();
    $year = (int) date('Y');
    foreach (range((int) date('n'), 1) as $month) {
        $months[$month] = array(
            'date' => date('F', mktime(0, 0, 0, $month)),
           
            'rides' => $user->getRides($month, $year)
        );

    }

    return $app['twig']->render('rides.html.twig', array(
        'user' => $user,
        'months' => $months
    ));
});


$app->match('/add', function () use ($app) {

    $token = $app['security']->getToken();
    if (null !== $token) {
        $user = $token->getUser();

    }   
    
    $user_id = $user->getUserId();

    $data = array(
       //'date' => new \DateTime()
    );

    $form = $app['form.factory']->createBuilder('form', $data)
        ->add('date', 'text', array(
            'label' => 'Date of ride',
            'required' => true
        ))
        ->add('km', 'text', array(
            'label' => 'Distance',
            'required' => true
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

    if ($app['request']->getMethod() === 'POST') {
        $form->bind($app['request']);
        if ($form->isValid()) {
            $data = $form->getData();
            

            $app['rides']->insert(array(
                'user_id' =>$user_id,
                'km'       => $data['km'],
                'url' => $data['url'],
                'date'       => $data['date'],
                'details'       => $data['details']
            ));


            //$rideHelper = new RideHelper();
            //$rideHelper->addRide($ride);

            return $app->redirect('/');
        }
    }

    return $app['twig']->render('add.html.twig', array('form' => $form->createView(), 'user' => $user));

});


$app->get('/ride/{$id}', function () use ($app) {
    //Show a single ride by its ID
});

$app->match('/register', function () use ($app) {
    //User registration

     $form = $app['form.factory']->createBuilder('form')
        ->add('username', 'text', array(
            'label' => 'Username',
            'required' => true
        ))
        ->add('name', 'text', array(
            'label' => 'Full name',
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
            'required' => true
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
                'username' => strtolower($data['username']),
                'password'       => $password,
                'roles' => 'ROLE_USER',
                'email' => $data['email'],
                'name'       => $data['name'],
                'forum_name'       => $data['forum_name'],
                'strava'       => $data['strava']
            ));
            

            return $app->redirect('/');
        }
    }

    return $app['twig']->render('register.html.twig', array('form' => $form->createView()));
});

$app->match('/login', function(Request $request) use ($app) {
    return $app['twig']->render('login.html.twig', array(
        'error'         => $app['security.last_error']($request),
        'last_username' => $app['session']->get('_security.last_username'),
    ));
});

$app->get('/admin/hi', function () use ($app) {
    return "test";
});

$app->error(function (\Exception $e, $code) {
    throw $e;
});

$app->run();
