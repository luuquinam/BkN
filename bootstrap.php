<?php
date_default_timezone_set('Asia/Ho_Chi_Minh');
require_once('cms/bootstrap.php');
require_once('config/constant.php');

spl_autoload_register(function ($class) {
  $class_path = DIR_CONTROLLER . str_replace('\\', '/', strtolower($class)) . '.php';
  if (file_exists($class_path)) {
    include_once($class_path);
    return;
  }

  $class_path = DIR_HELPER . str_replace('\\', '/', strtolower($class)) . '.php';
  if (file_exists($class_path)) {
    include_once($class_path);
    return;
  }
});

// Load custom config
$appConfig = [];

if (file_exists(DIR_CONFIG . 'app_config.yaml')) {
  $appConfig = Spyc::YAMLLoad(DIR_CONFIG . 'app_config.yaml');

}
$uri_parts = str_replace("/index.php","",explode('?', $_SERVER['REQUEST_URI'], 2));
// $appConfig['route'] = $uri_parts[0];
// Initialize web app
$app = new LimeExtra\App($appConfig);

// Setup path alias
$app->path('view', DIR_VIEW);
$app->path('lang', DIR_LANGUAGE);
$app->path('image', DIR_IMAGE);

// Helper
$app->helpers['util'] = 'Util';

// Frontend configuration
$config = include(DIR_CONFIG . 'frontend_config.php');
$app['config.frontend'] = $config;

$app['cache_version'] = $config['cache_version'];
