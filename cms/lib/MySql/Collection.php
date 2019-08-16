<?php

namespace MySql;

class Collection
{
  public $database;
  public $name;

  // Database name
  private $db;
  // Collection type
  private $type;

  private $structuredTypes = [
    'collections'
  ];

  private $cache = [
    'structure' => [
      'name' => null,
      'timestamp' => 0,
      'data' => []
    ]
  ];

  public function __construct($name, $database, $db, $type)
  {
    $this->name = $name;
    $this->database = $database;
    $this->db = $db;
    $this->type = $type;
  }

  public function drop()
  {
    $query = "DROP TABLE IF EXISTS <" . $this->name . ">";
    $this->database->query($query);
  }

  public function insert(&$document)
  {
    if (isset($document[0])) {
      $this->database->action(function ($database) use ($document) {
        foreach ($document as &$doc) {
          if (!is_array($doc)) continue;

          $res = $this->_insert($doc, $database);
          if (!$res) {
            return false;
          }
        }

        return count($document);
      });
    } else {
      return $this->_insert($document);
    }
  }

  protected function _insert(&$document, $database = null)
  {
    if ($database == null) {
      $database = $this->database;
    }

    $table = $this->name;
    $structure = $this->getCollectionStructure();

    if (!in_array($this->name, $this->getCollectionNames())) {
      // Create table if not exist
      $query = "CREATE TABLE IF NOT EXISTS <" . $this->name . "> (<_id> VARCHAR(30) PRIMARY KEY)";
      $database->query($query);
    }

    $query = "DESCRIBE <" . $table . ">";
    $columns = $database->query($query)->fetchAll();

    $alterQuery = "ALTER TABLE <" . $table . ">";
    $changed = false;

    foreach ($document as $key => &$value) {
      $dataType = 'TEXT';

      // Convert array to JSON string
      if (is_array($value)) {
        $value = json_encode($value);
      }

      if (isset($structure[$key])) {
        if ($structure[$key]['type'] == 'text' && (isset($structure[$key]['options']['is_number']) && $structure[$key]['options']['is_number'] == 'true')) {
          // Change column type to INT
          $dataType = 'INT';
          // If value is not int -> default to 0
          $document[$key] = is_numeric($value) ? $value : 0;
        }
      }

      $outCol = [];
      if (!$this->checkIfColumnExist($columns, $key, $outCol)) {
        // Create column if not exist
        $alterQuery .= ($changed ? "," : "") . " ADD COLUMN <" . $key . "> " . $dataType . " NULL DEFAULT NULL";
        $changed = true;
      } else {
        if (isset($structure[$key]) && strtolower($dataType) != strtolower($outCol['1'])) {
          // Change column type
          $alterQuery .= ($changed ? "," : "") . " CHANGE COLUMN <" . $key . "> <" . $key . "> " . $dataType . " NULL DEFAULT NULL";
          $changed = true;
        }
      }
    }
    unset($value);

    if ($changed) {
      $database->query($alterQuery);
    }

    $document["_id"] = uniqid() . 'doc' . rand();

    $result = false;
    $ins = $database->insert($table, $document);

    if ($ins->rowCount() > 0) {
      $result = true;
    } else {
      $error = $database->error();
    }

    return $result;
  }

  public function save(&$document)
  {
    return isset($document["_id"]) ? $this->update(array("_id" => $document["_id"]), $document) : $this->insert($document);
  }

  public function update($criteria, $document)
  {
    $table = $this->name;
    $structure = $this->getCollectionStructure();

    $query = "DESCRIBE <" . $table . ">";
    $columns = $this->database->query($query)->fetchAll();

    $alterQuery = "ALTER TABLE <" . $table . ">";
    $changed = false;

    foreach ($document as $key => &$value) {
      $dataType = 'TEXT';

      if (is_array($value)) {
        $value = json_encode($value);
      }

      if (isset($structure[$key])) {
        if ($structure[$key]['type'] == 'text' && (isset($structure[$key]['options']['is_number']) && $structure[$key]['options']['is_number'] == 'true')) {
          $dataType = 'INT';
          $value = is_numeric($value) ? intval($value) : 0;
        }
      }

      $outCol = [];
      if (!$this->checkIfColumnExist($columns, $key, $outCol)) {
        // Create column if not exist
        $alterQuery .= ($changed ? "," : "") . " ADD COLUMN <" . $key . "> " . $dataType . " NULL DEFAULT NULL ";
        $changed = true;
      } else {
        if (isset($structure[$key]) && strtolower($dataType) != strtolower($outCol['1'])) {
          // Change column type
          $alterQuery .= ($changed ? "," : "") . " CHANGE COLUMN <" . $key . "> <" . $key . "> " . $dataType . " NULL DEFAULT NULL";
          $changed = true;
        }
      }
    }
    unset($value);

    if ($changed) {
      $this->database->query($alterQuery);
    }

    $result = false;
    $sql = $this->database->update($table, $document, UtilArrayQuery::buildCondition($criteria, $structure));

    if ($sql->rowCount() > 0) {
      $result = true;
    } else {
      $error = $this->database->error();
    }

    return $result;
  }

  public function remove($criteria)
  {
    return $this->database->delete($this->name, UtilArrayQuery::buildCondition($criteria, $this->getCollectionStructure()));
  }

  public function count($criteria = null)
  {
    return $this->database->count($this->name, UtilArrayQuery::buildCondition($criteria, $this->getCollectionStructure()));
  }

  public function find($criteria = null, $projection = null, $limit = null, $sort = null, $skip = null)
  {
    if ($projection == null) {
      $projection = '*';
    }

    $criteria = UtilArrayQuery::buildCondition($criteria, $this->getCollectionStructure());

    if ($limit != null) {
      $criteria['LIMIT'] = [$skip == null ? 0 : $skip, $limit];
    }

    if ($sort != null && is_array($sort)) {
      foreach ($sort as &$s) {
        if ($s == -1)
          $s = 'DESC';
        if ($s == 1)
          $s = 'ASC';
      }
      $criteria["ORDER"] = $sort;
    }

    $results = $this->database->select($this->name, $projection, $criteria);

    foreach ($results as &$data) {
      foreach ($data as $key => $value) {
        if ($this->isJSON($value))
          $data[$key] = json_decode($value, true);
      }
    }
    unset($data);

    return new \MongoHybrid\ResultSet($this->database, $results);
  }

  public function findOne($criteria = null, $projection = null)
  {
    if ($projection == null) {
      $projection = '*';
    }
    $data = $this->database->get($this->name, $projection, UtilArrayQuery::buildCondition($criteria, $this->getCollectionStructure()));
    if (is_array($data)) {
      foreach ($data as $key => $value) {
        if ($this->isJSON($value)) {
          $data[$key] = json_decode($value, true);
        }
      }
    }
    return $data;
  }

  /**
   * Get all collections name from database
   */
  public function getCollectionNames()
  {
    $query = "SELECT <table_name> FROM <information_schema.tables> WHERE <table_schema> = :db";
    $names = $this->database->query($query, [
      ":db" => $this->db
    ])->fetchAll();
    return $names;
  }

  public function renameCollection($newname)
  {
    if (!in_array($newname, $this->getCollectionNames())) {
      $query = "ALTER TABLE <" . $this->name . "> RENAME TO <" . $newname . ">";
      $this->database->query($query);
      $this->name = $newname;
      return true;
    }

    return false;
  }

  /**
   * Get this collection structure
   */
  private function getCollectionStructure()
  {
    $structure = [];

    // Return early if this collection type does not need to get structure
    if (!in_array($this->type, $this->structuredTypes)) {
      return $structure;
    }

    $cacheStructure = $this->cache['structure'];
    $fieldData = [];
    if (empty($cacheStructure['name'])) {
      // If we did not have the collection file name 
      // then we will need to loop through all files of this collection type to get the fields data
      foreach (glob(COCKPIT_STORAGE_FOLDER . "/" . $this->type . "/*.php") as $filePath) {
        $data = include($filePath);
        if ($data['_id'] == $this->name) {
          $fieldData = $data['fields'];
          // Set the collection file name
          $cacheStructure['name'] = basename($filePath, '.php');
          // Set the file timestamp to check if we need to fetch new data
          $cacheStructure['timestamp'] = filemtime($filePath);
          break;
        }
      }
    } else {
      $filePath = COCKPIT_STORAGE_FOLDER . "/" . $this->type . "/" . $cacheStructure['name'] . ".php";
      $fileTimestamp = filemtime($filePath);
      if ($fileTimestamp > $cacheStructure['timestamp']) {
        // If file was modified then we will fetch new data from it
        $data = include($filePath);
        $fieldData = $data['fields'];
        $cacheStructure['data'] = $fieldData;
        $cacheStructure['timestamp'] = $fileTimestamp;
      } else {
        // Get data from cache
        $fieldData = $cacheStructure['data'];
      }
    }

    foreach ($fieldData as $field) {
      $structure[$field['name']] = $field;
    }

    return $structure;
  }

  private function isJSON($string)
  {
    return is_string($string) && is_array(json_decode($string, true)) ? true : false;
  }

  /**
   * Check if this column exist in database
   */
  private function checkIfColumnExist($columns, $name, &$outCol)
  {
    foreach ($columns as $col) {
      if ($col[0] == $name) {
        $outCol = $col;
        return true;
      }
    }
    return false;
  }
}

class UtilArrayQuery
{
  /**
   * Convert cockpit filter condition to medoo where condition
   */
  public static function buildCondition($criteria, $structure = [])
  {
    $fn = [];

    if ($criteria == null) {
      return $fn;
    }

    foreach ($criteria as $key => $value) {
      switch ($key) {
        case '$and':
          $_fn = array();
          foreach ($value as $v) {
            $_fn[] = self::buildCondition($v);
          }
          $fn['AND'] = $_fn;
          break;
        case '$or':
          $_fn = array();
          foreach ($value as $v) {
            $_fn[] = self::buildCondition($v);
          }
          $fn['OR'] = $_fn;
          break;
        default:
          if (is_array($value)) {
            foreach ($value as $func => $v) {
              switch ($func) {
                case '$eq':
                  $fn[$key] = $v;
                  break;
                case '$not':
                  $fn[$key . '[!]'] = $v;
                  break;
                case '$gte':
                  $fn[$key . '[>=]'] = $v;
                  break;
                case '$gt':
                  $fn[$key . '[>]'] = $v;
                  break;
                case '$lte':
                  $fn[$key . '[<=]'] = $v;
                  break;
                case '$lt':
                  $fn[$key . '[<]'] = $v;
                  break;
                case '$in':
                  $fn[$key] = $v;
                  break;
                case '$nin':
                  $fn[$key . '[!]'] = $v;
                  break;
                case '$btw':
                  $fn[$key . '[<>]'] = $v;
                  break;
                case '$like':
                  $fn[$key . '[~]'] = $v;
                  break;
              }
            }
          } else {
            if (isset($structure[$key]) && $structure[$key]['type'] == 'collectionlink') {
              // If this column key is a collection link then we use LIKE condition to find a match with this value
              $fn[$key . '[~]'] = '%"_id":"' . $value . '"%';
            } else {
              $fn[$key] = $value;
            }
          }
      }
    }

    return $fn;
  }
}