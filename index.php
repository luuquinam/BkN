<?php
require_once('bootstrap.php');

// $app = new Lime\App([
// 	// 'route' => $_SERVER["REQUEST_URI"],
// ]);

require_once(DIR_CONFIG . 'routing.php');

$app->on('before', function () use ($app) {
});

$app->on('app.layout.header', function() use ($app){
});

$app->on('app.layout.contentbefore', function() use ($app){
});

// Error handler
$app->on('after', function () {
  switch ($this->response->status) {
    case '404':
      $this->response->body = $this->render('view:404.php');
      break;
    case '500':
      $this->response->body = $this->render('view:500.php');
      break;
  }
});

$app->run();
?>