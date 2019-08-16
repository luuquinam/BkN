<?php

class API extends \LimeExtra\Controller
{
  private $paginationLimit = 10;
  protected function before()
  {
    $this->app->response->mime = 'json';
  }

  public function gallery_category(){
    header("Access-Control-Allow-Origin: *");
    
    $page = $_GET['page'];
    // $limit = $_GET['limit'];
    $limit = $this->paginationLimit;

    if (empty($page) || empty($limit)) {
      $page = 1;
      $limit = 10;
    }
    $filter = [
      'sort' => [
        '_created' => -1
      ],
      'limit' => $limit,
      'skip' => ($page - 1) * $limit
    ];

    $criteria = [];
    $filter['filter'] = $criteria;

    $total = cockpit('collections:count', 'galerry_category', $criteria);
    $results = cockpit('collections:find', 'galerry_category', $filter);
    $last = ceil($total / $this->paginationLimit);

    foreach ($results as $key => &$value_cate) {
      if ($value_cate && $value_cate['image'] && $value_cate['image']['path']) {
        $value_cate['image']['path'] = $this->getImage($value_cate['image']['path']);
      }

      
    }    

    return [
      'items' => $results,
      'last' => $last,
      'total' => $total,
      'page' => $page,
    ];    
  }

  public function gallery()
  {
    header("Access-Control-Allow-Origin: *");
    // $data = json_decode(file_get_contents('php://input'), true);

    // $page = $data['page'];
    // $limit = $data['limit'];
    
    $page = $_GET['page'];
    $category = $_GET['category'];
    // $limit = $_GET['limit'];
    $limit = $this->paginationLimit;

    // var_dump($_GET['page']);

    // var_dump($data);
    // var_dump($page);
    // var_dump($limit);
    if (empty($page) || empty($limit)) {
      return [];
    }
    $filter = [
      'sort' => [
        '_created' => -1
      ],
      'limit' => $limit,
      'skip' => ($page - 1) * $limit
    ];

    $criteria = ['category' => $category];
    $filter['filter'] = $criteria;

    $total = cockpit('collections:count', 'gallery', $criteria);
    $results = cockpit('collections:find', 'gallery', $filter);
    $last = ceil($total / $this->paginationLimit);

    foreach ($results as $key => &$value_rs) {
      if ($value_rs && $value_rs['image'] && $value_rs['image']['path']) {
        $value_rs['image']['path'] = $this->getImage($value_rs['image']['path']);
      }

      
    }
    // var_dump($results);
    // return $results;
    $pages = [$page];
    $count_page = 3;
    for ($i=1; $i <5 ; $i++) {
      if ((($page - $i) > 0) && $count_page > 0) {
        array_unshift($pages , $page - $i);
        $count_page--;
      }
      if ((($page + $i) <= $last) && $count_page > 0) {
        $pages[] = $page + $i;
        $count_page--;
      }      
    }

    if ($pages[0] > 1) {
      array_unshift($pages , -1);
      array_unshift($pages , 1);
    }

    if ($pages[count($pages) - 1] < $last) {
      $pages[] = -1;
      $pages[] = $total;
    }
    $data = [
      'items' => $results,
      'last' => $last,
      'total' => $total,
      'page' => $page,
      'pages' => $pages,
      'limit' => $limit,
    ];    
    // var_dump($data);
    return json_encode($data);
  }

  public function gallery_category_ios(){
    header("Access-Control-Allow-Origin: *");
    
    $page = $_GET['page'];
    // $limit = $_GET['limit'];
    $limit = $this->paginationLimit;

    if (empty($page) || empty($limit)) {
      $page = 1;
      $limit = 10;
    }
    $filter = [
      'sort' => [
        '_created' => -1
      ],
      'limit' => $limit,
      'skip' => ($page - 1) * $limit
    ];

    $criteria = [];
    $filter['filter'] = $criteria;

    $total = cockpit('collections:count', 'galerry_category_ios', $criteria);
    $results = cockpit('collections:find', 'galerry_category_ios', $filter);
    $last = ceil($total / $this->paginationLimit);

    foreach ($results as $key => &$value_cate) {
      if ($value_cate && $value_cate['image'] && $value_cate['image']['path']) {
        $value_cate['image']['path'] = $this->getImage($value_cate['image']['path']);
      }

      
    }    

    return [
      'items' => $results,
      'last' => $last,
      'total' => $total,
      'page' => $page,
    ];    
  }

  public function gallery_ios()
  {
    header("Access-Control-Allow-Origin: *");
    // $data = json_decode(file_get_contents('php://input'), true);

    // $page = $data['page'];
    // $limit = $data['limit'];
    
    $page = $_GET['page'];
    $category = $_GET['category'];
    // $limit = $_GET['limit'];
    $limit = $this->paginationLimit;

    // var_dump($_GET['page']);

    // var_dump($data);
    // var_dump($page);
    // var_dump($limit);
    if (empty($page) || empty($limit)) {
      return [];
    }
    $filter = [
      'sort' => [
        '_created' => -1
      ],
      'limit' => $limit,
      'skip' => ($page - 1) * $limit
    ];

    $criteria = ['category' => $category];
    $filter['filter'] = $criteria;

    $total = cockpit('collections:count', 'gallery_ios', $criteria);
    $results = cockpit('collections:find', 'gallery_ios', $filter);
    $last = ceil($total / $this->paginationLimit);

    foreach ($results as $key => &$value_rs) {
      if ($value_rs && $value_rs['image'] && $value_rs['image']['path']) {
        $value_rs['image']['path'] = $this->getImage($value_rs['image']['path']);
      }

      
    }
    // var_dump($results);
    // return $results;
    $pages = [$page];
    $count_page = 3;
    for ($i=1; $i <5 ; $i++) {
      if ((($page - $i) > 0) && $count_page > 0) {
        array_unshift($pages , $page - $i);
        $count_page--;
      }
      if ((($page + $i) <= $last) && $count_page > 0) {
        $pages[] = $page + $i;
        $count_page--;
      }      
    }

    if ($pages[0] > 1) {
      array_unshift($pages , -1);
      array_unshift($pages , 1);
    }

    if ($pages[count($pages) - 1] < $last) {
      $pages[] = -1;
      $pages[] = $total;
    }
    $data = [
      'items' => $results,
      'last' => $last,
      'total' => $total,
      'page' => $page,
      'pages' => $pages,
      'limit' => $limit,
    ];    
    // var_dump($data);
    return json_encode($data);
  }  

  function getImage($path){
    // $mypath = str_replace("//","/",$this->base_url .'/'. $path);
    // $mypath = str_replace("\\","/",$mypath);
    $mypath = str_replace("\\","/",$path);
    // return $path;
    return $mypath;
  }  

  function getYearImage()
  {
    header("Access-Control-Allow-Origin: *");
    $data = json_decode(file_get_contents('php://input'), true);
    $response = [
      'success' => false
    ];

    if(isset($data['device']) && !empty($data['device']) ){

      if(isset($data['year']) && !empty($data['year'])){
        if (!isset($data['version'])) {
          $data['version'] = '';
        }

          $backgroundImage = [cockpit('collections:findOne', 'background', [
            'year' => $data['year'],
            'version[!]' => [
              '$not' => $data['version']
            ]
          ])];
      }
      else{

          $filter = [
            // 'year[!]' => 'default_ios',
            // 'sort' => [
            //   '_o' => 1
            // ],
          ];

          if ($data['device'] == 'android') {
            $backgroundImage = cockpit('collections:find', 'background', $filter);
          }
          else{
             $backgroundImage = cockpit('collections:find', 'Background_ios', $filter);
          }
          

       
      }
      if(!empty($backgroundImage)){
        $response['success'] = true;
        // if ($data['device'] == 'ios') {

        //   foreach ($backgroundImage as $key => $item) {
        //     if ($item['year'] == 'default_ios') {
        //       $t = $item;
        //       break;
        //     }
        //   } 

        //   foreach ($backgroundImage as $key => &$itemd) {
        //     if ($itemd['year'] == 'default') {
        //       if ($t) {
        //         $itemd = $t;
        //         $itemd['year'] = 'default';
        //       }
        //       break;
        //     }
        //   }

        //   $t['year'] = 'default';
        //   $backgroundImage = [$t];

        // }        
        $response['result'] = $backgroundImage;
      }
    }
    return json_encode($response);
  }

  function isEnableCensoredDefault()
  {
    header("Access-Control-Allow-Origin: *");
    $response['success'] = true;
    $data = cockpit('singletons:getData', 'censored_default');
    if(isset($data['enable']) && $data['enable']){
      $response['result'] = true;
    } else{
      $response['result'] = false;
    }

    return json_encode($response);
  }

  public function day_info(){
    header("Access-Control-Allow-Origin: *");
    // $data = json_decode(file_get_contents('php://input'), true);
    $response = [
      'success' => false
    ];
    $year = $_GET['year'];
    $month = $_GET['month'];
    $device = $_GET['device'];

    // return json_encode($year);

    if( isset($year) && !empty($year) && isset($month) && !empty($month) ){
      $month = $month -1;

      if ($device == 'android') {
        $backgroundImage = [cockpit('collections:findOne', 'background', [
          'year' => $year,
        ])];
      }
      else{
        $backgroundImage = [cockpit('collections:findOne', 'Background_ios', [
          'year' => $year,
        ])];
      }      
      
      // return json_encode($backgroundImage);
      if ($backgroundImage && $backgroundImage[0] && $backgroundImage[0]['gallery'] && $backgroundImage[0]['gallery'][$month]) {
        $response['success'] = true;
        $response['result'] = $backgroundImage[0]['gallery'][$month];
      }
    }   

    return json_encode($response); 
  }  
}