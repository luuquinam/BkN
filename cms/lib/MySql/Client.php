<?php

namespace MySql;

class Client
{
  protected $database;
  protected $server;
  protected $options;
  protected $type;

  // Cache collection objects
  protected $collections = [];

  public function __construct($server, $options = [])
  {
    $this->server = str_replace('mysql://', '', $server);
    $this->options = $options;
    $this->selectDB();
  }

  public function selectCollection($collection, $type)
  {
    if(!isset($this->collections[$type . '/' . $collection])){
      $this->collections[$type . '/' . $collection] = new Collection($collection, $this->database, $this->options['db'], $type);
    }
    return $this->collections[$type . '/' . $collection];
  }

  public function selectDB()
  {
    // Return a singleton medoo connection
    if (!isset($this->database)) {
      $this->database = new Medoo([
        'database_type' => 'mysql',
        'database_name' => $this->options['db'],
        'server' => $this->server,
        'username' => $this->options['username'],
        'password' => $this->options['password'],
      ]);
    }

    return $this->database;
  }
}
