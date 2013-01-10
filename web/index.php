<?php

require_once __DIR__.'/../vendor/autoload.php';

use Igorw\Silex\ConfigServiceProvider;
use Silex\Provider\DoctrineServiceProvider;
use Silex\Provider\FormServiceProvider;
use Silex\Provider\SecurityServiceProvider;
use Silex\Provider\TranslationServiceProvider;
use Silex\Provider\TwigServiceProvider;
use Knp\Provider\RepositoryServiceProvider;

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

$app->register(new RepositoryServiceProvider(), array('repository.repositories' => array(
    'rides'      => 'Century\\Repository\\RideRepo',
)));

$app->register(new FormServiceProvider());

$app->register(new TranslationServiceProvider(), array(
    'locale_fallback' => 'en',
));

$app->register(new TwigServiceProvider(), array(
    'twig.path' => __DIR__ . '/../views'
));

$app->get('/', function () use ($app) {
    //Show leaderboard and latest rides
    $rides = $app['rides']->getAllRides();

    $months = array();
    foreach (range(1, (int) date('n')) as $month) {
        $months[$month] = date('F', mktime(0, 0, 0, $month));
    }

    return $app['twig']->render('index.html.twig', array(
        //'users' => $users,
        'rides' => $rides,
        'months' => $months,
        'year' => (int) date('Y')
    ));
});

$app->get('/rides/{$username}', function () use ($app) {
    //Show Rides
});


$app->match('/add', function () use ($app) {
    //Add ride, user must be logged in.
    //otherwise go to login form

    $user_id = rand(1,5);

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

    return $app['twig']->render('add.html.twig', array('form' => $form->createView()));

});


$app->get('/ride/{$id}', function () use ($app) {
    //Show a single ride by its ID
});

$app->match('/register', function () use ($app) {
    //User registration
});

$app->match('/login', function () use ($app) {
    //login page
});

$app->error(function (\Exception $e, $code) {
    throw $e;
});

$app->run();
