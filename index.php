<?php
// Branch test

require 'vendor/autoload.php';
date_default_timezone_set('America/New_York');

//$log = new Monolog\Logger('name');
//$log->pushHandler(new Monolog\Handler\StreamHandler('app.txt', Monolog\Logger::WARNING));
//$log->addWarning('Oh Noes.');

$app = new \Slim\Slim( array(
  'view' => new \Slim\Views\Twig()
));

$view = $app->view();
$view->parserOptions = array(
    'debug' => true
);

$view->parserExtensions = array(
    new \Slim\Views\TwigExtension(),
);

$app->get('/', function() use($app){
    $app->render('about.twig');
})->name('home');

$app->get('/contact', function() use($app){
    $app->render('contact.twig');
})->name('contact');

$app->post('/contact', function() use($app){
  $name = $app->request->post('name');
  $email = $app->request->post('email');
  $msg = $app->request->post('msg');

  if( !empty($name) AND !empty($email) AND !empty($msg) ){
    $cleanName = filter_var($name, FILTER_SANITIZE_STRING);
    $cleanEmail = filter_var($email, FILTER_SANITIZE_EMAIL);
    $cleanMsg = filter_var($msg, FILTER_SANITIZE_STRING);
  } else {
    // Error message
    $app->redirect('/contact');
  }


  $transport = Swift_SendmailTransport::newInstance('/usr/sbin/sendmail -bs');
  $mailer = \Swift_Mailer::newInstance($transport);

  $message = \Swift_Message::newInstance();
  $message->setSubject('Email from our website');
  $message->setFrom(array(
    $cleanEmail => $cleanName
  ));
  $message->setTo(array('szana.szabolcs@gmail.com'));
  $message->setBody($cleanMsg);

  $result = $mailer->send($message);

  if( $result > 0 ) {
    //send message to the user: succes
    $app->redirect('/');
  } else {
    // send message to the user: failed
    // log that error
    $app->redirect('/contact');
  }

});

$app->run();
