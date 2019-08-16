<?php 

foreach (glob("controllers/*.php") as $filename)
{
	$filename = basename($filename, ".php");
	$app->bindClass(ucfirst($filename));
}

$app->bind('/', function () {
  return $this->invoke('Home', 'index');
});
