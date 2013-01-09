<?php

require_once __DIR__.'/../vendor/autoload.php';

use Igorw\Silex\ConfigServiceProvider;
use Silex\Provider\DoctrineServiceProvider;
use Silex\Provider\FormServiceProvider;
use Silex\Provider\SecurityServiceProvider;
use Silex\Provider\TranslationServiceProvider;
use Silex\Provider\TwigServiceProvider;

require_once('../classes/RideHelper.php');
require_once('../classes/UserHelper.php');
require_once('../classes/Registration.php');

$app = new Silex\Application();

$app->register(new ConfigServiceProvider(__DIR__ . '/../config/config.yml'));

$app->register(new DoctrineServiceProvider(), array(
    'db.options' => array(
        'driver' => 'pdo_mysql',
        'host' => $app['db.host'],
        'dbname' => $app['db.name'],
        'username' => $app['db.username'],
        'password' => $app['db.password']
    )
));

$app->register(new FormServiceProvider());

$app->register(new SecurityServiceProvider());
$app['security.firewalls'] = array(
    'register' => array(
        'pattern' => '^/register$',
    ),
    'secured' => array(
        'pattern' => '^.*$',
        'http' => true,
        'users' => $app->share(function() use ($app) {
            return new Century\User\UserProvider($app['db']);
        })
    )
);

$app->register(new TranslationServiceProvider(), array(
    'locale_fallback' => 'en',
));

$app->register(new TwigServiceProvider(), array(
    'twig.path' => __DIR__ . '/../views'
));

$app->get('/', function () use ($app) {
    $userHelper = new UserHelper();
    $users = $userHelper->getUsers();

    $rideHelper = new RideHelper();
    $rides = $rideHelper->getRides(1);

    $months = array();
    foreach (range(1, (int) date('n')) as $month) {
        $months[$month] = date('F', mktime(0, 0, 0, $month));
    }

    return $app['twig']->render('index.html.twig', array(
        'users' => $users,
        'rides' => $rides,
        'months' => $months,
        'year' => (int) date('Y')
    ));
});

$app->get('/rides', function () use ($app) {
    if (!$app['request']->query->has('username')) {
        throw new \InvalidArgumentException('Missing username parameter');
    }

    $userHelper = new UserHelper();
    $rideHelper = new RideHelper();
    $user = $userHelper->getUser($app['request']->query->get('username'));

    $days = array();
    $year = (int) date('Y');
    for ($day = (int) date('j'); $day >= 1; $day--) {
        $days[$day] = array(
            'date' => date('F', mktime(0, 0, 0, $day)),
            'rides' => $rideHelper->getRides($user->getUser_id(), $day, $year)
        );
    }

    return $app['twig']->render('rides.html.twig', array(
        'user' => $user,
        'days' => $days
    ));
});

$app->match('/ride/add', function () use ($app) {
    $userHelper = new UserHelper();
    $user = $userHelper->getUser($app['security']->getToken()->getUser()->getUsername());

    $data = array(
        'date' => new \DateTime()
    );

    $form = $app['form.factory']->createBuilder('form', $data)
        ->add('date', 'date', array(
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
            
            $ride = new Ride();
            $ride->newRide($user->getUser_id(), $data['km'], $data['url'], $data['date'], $data['details']);

            $rideHelper = new RideHelper();
            $rideHelper->addRide($ride);

            return $app->redirect('/');
        }
    }

    return $app['twig']->render('add.html.twig', array('form' => $form->createView()));
});

$app->match('/register', function () use ($app) {
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
            $data = $form->getData();

            $user = new User(null, 
                $data['username'],
                $data['email'],
                $data['password'], // TODO: Encode password so it works
                null,
                null,
                $data['forum_name'],
                $data['strava'],
                $data['name']
            );

            $register = new Registration();
            $register->addUser($user);

            return $app->redirect('/');
        }
    }

    return $app['twig']->render('register.html.twig', array('form' => $form->createView()));
});

$app->get('/activiate', function () use ($app) {
    if ($app['request']->query->has('k')) {
        $register = new Registration();
        $register->activateUser($app['request']->query->get('k'));

        return $app->redirect('/');
    }

    echo 'Invalid key';
    exit;
});

$app->error(function (\Exception $e, $code) {
    throw $e;
});

$app->run();
