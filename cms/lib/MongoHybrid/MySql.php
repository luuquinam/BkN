<?php

use Medoo\Medoo;

namespace MongoHybrid;

class MySql
{

  protected $client;

  public function __construct($server, $options = [])
  {
    $this->client = new \MySql\Client($server, $options);
  }

  public function getCollection($name, $type = null)
  {
    if (strpos($name, '/') !== false) {
      list($type, $name) = explode('/', $name, 2);
    }

    $name = str_replace('/', '_', $name);

    return $this->client->selectCollection($name, $type);
  }

  public function findOne($collection, $filter = [], $projection = null)
  {
    return $this->getCollection($collection)->findOne($filter, $projection);
  }

  public function findOneById($collection, $id)
  {
    return $this->getCollection($collection)->findOne(["_id" => $id]);
  }

  public function find($collection, $options = [])
  {
    $filter = isset($options["filter"]) ? $options["filter"] : null;
    $fields = isset($options["fields"]) && $options["fields"] ? $options["fields"] : null;
    $limit = isset($options["limit"]) ? $options["limit"] : null;
    $sort = isset($options["sort"]) ? $options["sort"] : null;
    $skip = isset($options["skip"]) ? $options["skip"] : null;

    $resultSet = $this->getCollection($collection)->find($filter, $fields, $limit, $sort, $skip);

    return $resultSet;
  }

  public function insert($collection, &$doc)
  {
    return $this->getCollection($collection)->insert($doc);
  }

  public function save($collection, &$data)
  {
    return $this->getCollection($collection)->save($data);
  }

  public function update($collection, $criteria, $data)
  {
    return $this->getCollection($collection)->update($criteria, $data);
  }

  public function remove($collection, $filter = [])
  {
    return $this->getCollection($collection)->remove($filter);
  }

  public function count($collection, $filter = [])
  {
    return $this->getCollection($collection)->count($filter);
  }
}
