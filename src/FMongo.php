<?php
/**
 * @date 2020/9/1 15:24
 * @desciption:mongodb 操作类
 */
declare(strict_types=1);

class FMongo
{
  public static $_instance = null;
  public $conf = null;
  /**
   * @var MongoDB\Driver\Manager
   */
  public $handle = null;
  public $_and = [];
  public $_or = [];
  public $collection = "";
  public $limit = 0;
  public $skip = 0;
  public $_field = [];
  public $_sort = [];

  public static function getInstance($conf = 'default')
  {
    if (self::$_instance === null) {
      self::$_instance = new self($conf);
    }

    return self::$_instance;
  }

  private function __construct($conf = 'default')
  {
    $this->conf = FConfig::get('db.mongoDB.' . $conf);
    $this->connect();
  }

  public function connect()
  {
    $uri = "mongodb://{$this->conf['dsn']}/{$this->conf["db"]}";
    try {
      $this->handle = new MongoDB\Driver\Manager($uri);
    } catch(Exception $exception) {
      throw new Exception($exception->getMessage());
    }
  }

  public function collection(string $collection)
  {
    $this->collection = $this->conf["db"] . "." . $collection;
    return $this;
  }

  public function where(array $where)
  {
    if (!$where) {
      return $this;
    }

    if ($where['id']) {
      $where['_id'] = intval($where['id']);
      unset($where['id']);
    }

    if ($where['_id'] && is_string($where['_id'])) {
      $where['_id'] = new MongoDB\BSON\ObjectID($where['_id']);
    }

    $this->_and = array_merge($this->_and, $where);
    return $this;
  }

  public function orWhere(array $where)
  {
    if (empty($where)) {
      $this->echoError(new Exception("param where is empty"));
    }
    $this->_or = array_merge($this->_or, $where);
    return $this;
  }

  public function limit(int $limit)
  {
    $this->limit = $limit;
    return $this;
  }

  public function skip(int $skip)
  {
    $this->skip = $skip;
    return $this;
  }

  /**
   * Description:分页数据
   * @param int $page
   * @param int $limit
   * @return string
   *
   */
  public function page(int $page, int $limit)
  {
    $this->skip(($page - 1) * $limit);
    $this->limit($limit);
    return $this;
  }


  public function field(string $field, bool $_id = true)
  {
    if (!empty($field)) {
      $fieldArr = explode(",", $field);
      if (is_array($fieldArr)) {
        foreach ($fieldArr as $val) {
          $this->_field[$val] = 1;
        }
      }
    }
    if (!$_id) {
      $this->_field["_id"] = 0;
    }
    return $this;
  }

  public function sort(array $sort)
  {
    $this->_sort = $sort;
    // $this->_sort["field"] = $field;
    // $this->_sort["rule"] = $sort;
    return $this;
  }

  public function select()
  {
    return $this->query();
  }

  public function fetch()
  {
    return $this->query();
  }

  public function findRow()
  {
    $this->limit = 1;
    $this->skip = 0;
    $query = $this->query();

    if ($query) {
      return $query[0];
    } else {
      return null;
    }
  }

  public function count()
  {
    //查询条件
    $filter = $this->getWhere();
    if (!$filter) {
      $filter = json_decode("{}");
    }

    //var_dump($filter);exit;

    $command = new \MongoDB\Driver\Command(['count' => str_replace($this->conf["db"] . ".", "", $this->collection), 'query' => $filter]);
    $result = $this->command($this->conf["db"], $command);
    $res = $result->toArray();
    $cnt = 0;
    if ($res) {
      $cnt = $res[0]->n;
    }

    return $cnt;
  }

  public function sum(string $field)
  {
    $filter = $this->getWhere();
    $aggregate = [
      "aggregate" => str_replace($this->conf["db"] . ".", "", $this->collection),
      "pipeline" => [
        ['$match' => $filter],
        [
          '$group' => [
            '_id' => '',
            'total' => ['$sum' => '$' . $field],
          ]
        ],
      ],
      "cursor" => (object)array()
    ];
    $command = new \MongoDB\Driver\Command($aggregate);
    $result = $this->command($this->conf["db"], $command);
    return $result ? $result->toArray()[0]->total : false;
  }

  public function getWhere()
  {
    $filter = [];
    if (!empty($this->_and)) {
      $filter = array_merge($filter, $this->_and);
    }
    if (!empty($this->_or)) {
      foreach ($this->_or as $key => $val) {
        $filter['$or'][][$key] = $val;
      }
    }
    return $filter;
  }

  public function getQuery()
  {
    $filter = $this->getWhere();
    if (!empty($this->_field)) {
      $queryOptions["projection"] = $this->_field;
    }
    if (!empty($this->_sort)) {
      $queryOptions["sort"] = $this->_sort;
    }
    if ($this->limit > 0) {
      $queryOptions["limit"] = $this->limit;
    }
    if ($this->skip > 0) {
      $queryOptions["skip"] = $this->skip;
    }
    $query = new MongoDB\Driver\Query($filter, $queryOptions);
    return $query;
  }

  /**
   * @return array
   * @throws Exception
   */
  public function query()
  {
    $query = $this->getQuery();
    try {
      $cursor = $this->handle->executeQuery($this->collection, $query);
      $result = array();

      foreach ($cursor as $tmp) {

        $id = $tmp->_id;
        if (is_object($id)) {
          $id = (array)$id;
          $tmp->_id = $id = $id["oid"];
        }

        $tmp = (array)$tmp;
        $tmp['id'] = $id;

        $result[] = $tmp;
      }
    } catch(\Exception $exception) {
      $this->echoError($exception);
      $result = false;
    } catch(\MongoDB\Driver\Exception\Exception $e) {
      $this->echoError($e);
      $result = false;
    }
    $this->init();
    return $result ?: [];
  }

  /**
   * 插入数据
   *
   * @param array $data 数据
   * @param bool $batch 是否批量
   * @return int
   */
  public function insert(array $data, bool $batch = false)
  {
    $write = new MongoDB\Driver\BulkWrite();
    if ($batch) {
      foreach ($data as $val) {
        if ($data['id']) {
          $data['_id'] = intval($data['id']);
          unset($data['id']);
        }

        if (!$data['_id']) {
          $data['_id'] = self::auto_id($this->collection);
          $data['create_time'] = date('Y-m-d H:i:s');
        }
        $write->insert($val);
      }
    } else {
      if ($data['id']) {
        $data['_id'] = intval($data['id']);
        unset($data['id']);
      }

      if (!$data['_id']) {
        $data['_id'] = self::auto_id($this->collection);
        $data['create_time'] = date('Y-m-d H:i:s');
      }

      $write->insert($data);
    }

    $result = $this->execute($this->collection, $write);
    return $result ? $data['_id'] : 0;
  }

  /**
   * 更新
   * @param array $update
   * @param array $where
   * @param bool $multi true 匹配所有文档 false 匹配一个
   * @param bool $upsert true 匹配不到将插入
   * @return bool
   * @throws Exception
   */
  public function update(array $update, array $where = null, bool $multi = false, bool $upsert = true)
  {
    if ($where) {
      $this->where($where);
    }

    if (empty($this->_and)) {
      $this->echoError(new Exception("update where is empty"));
    }
    $write = new MongoDB\Driver\BulkWrite();
    $write->update(
      $this->_and,
      ['$set' => $update],
      ['multi' => $multi, 'upsert' => $upsert]
    );
    $result = $this->execute($this->collection, $write);
    return $result ? $result->getUpsertedCount() + $result->getMatchedCount() : false;
  }

  public function delete(bool $all = false)
  {
    if (empty($this->_and)) {
      $this->echoError(new Exception("delete where is empty"));
    }

    $write = new MongoDB\Driver\BulkWrite();
    $write->delete($this->_and, ['limit' => $all]);
    $result = $this->execute($this->collection, $write);
    return $result ? $result->getDeletedCount() : false;
  }

  /**
   * @param array $pipeline
   * $pipeline 参数说明：
   * [
   *    [
   *        '$match' => [
   *            'time' => ['$lt'=>1598864580]
   *         ],
   *    ],
   *    [
   *         '$group' => [
   *             "_id"=>'$time', "total" => ['$sum' => 1]
   *         ],
   *     ],
   *     [
   *         '$limit' => 3
   *     ],
   *     [
   *         '$sort'  => ['total' => -1]
   *     ]
   * ]
   * @return bool
   */
  public function aggregate(array $pipeline)
  {
    $aggregate = [
      "aggregate" => str_replace($this->conf["db"] . ".", "", $this->collection),
      "pipeline" => $pipeline,
      "cursor" => (object)array()
    ];
    $command = new \MongoDB\Driver\Command($aggregate);
    $result = $this->command($this->conf["db"], $command);
    return $result ? $result->toArray() : false;
  }

  public function execute($namespace, $object)
  {
    try {
      $result = $this->handle->executeBulkWrite($namespace, $object);
    } catch(\Exception $exception) {
      $this->echoError($exception);
    }
    $this->init();
    return $result;
  }

  public function command($db, $command)
  {
    try {
      $result = $this->handle->executeCommand($db, $command);
    } catch(\Exception $exception) {
      $this->echoError($exception);
    }
    $this->init();
    return $result;
  }

  public function echoError(Exception $exception)
  {
    throw new Exception($exception->getMessage());
  }

  public function init()
  {
    $this->_and = [];
    $this->_or = [];
    $this->collection = "";
    $this->limit = 0;
    $this->skip = 0;
    $this->_field = [];
    $this->_sort = [];
  }

  /**
   * 同mysql中的distinct功能
   *
   * @param $field
   * @param string $key 要进行distinct的字段名
   * @return array
   * Array
   * (
   * [0] => 1.0
   * [1] => 1.1
   * )
   */
  function distinct($field)
  {
    $query = $this->getQuery();
    $command = new \MongoDB\Driver\Command([
      // 'distinct' => $this->collection, // 集合名称
      'distinct' => str_replace($this->conf["db"] . ".", "", $this->collection), // 集合名称
      'key' => $field, // 需要显示的字段
      'query' => $query // 条件
    ]);

    $arr = $this->command($this->conf["db"], $command)->toArray(); // 数据库名称和命令

    $result = [];
    if (!empty($arr)) {
      $result = $arr[0]->values;
    }
    return $result;
  }


  /**
   * 自动ID
   * @param $table
   * @return int|mixed
   */
  public function auto_id($table)
  {
    $table = str_replace($this->conf["db"] . '.', '', $table);

    $write = new MongoDB\Driver\BulkWrite();
    $query = new MongoDB\Driver\Query(['table' => $table], ["sort" => ['_id' => -1], "limit" => 1,]);

    $auto_id = 1;

    try {
      $result = $this->handle->executeQuery($this->conf["db"] . '._auto_ids', $query)->toArray();

      if ($result) {
        $result = (array)$result[0];
        $auto_id = $result['auto_id'] + 1;

        $write->update(
          ['table' => $table],
          ['$set' => ['auto_id' => $auto_id]],
          ['multi' => false, 'upsert' => true]
        );
      } else {
        $write->insert(['table' => $table, 'auto_id' => $auto_id]);
      }

      $this->handle->executeBulkWrite($this->conf["db"] . '._auto_ids', $write);

    } catch(\MongoDB\Driver\Exception\Exception $e) {
      FException::getInstance()->traceError($e);
    }

    return $auto_id;
  }

  /**
   * @param $table String 表名
   * @param $where array 查询条件
   * @return int
   */
  public static function countRecord(string $table, array $where)
  {
    $mongo = FMongo::getInstance();
    return $mongo->collection($table)->where($where)->count();
  }
}

//$db = MongoDB::getInstance([
//    "type" => "mongodb",
//    "host" => "127.0.0.1",
//    "port" => "27017",
//    "db" => "db",
//    "user" => "",
//    "password" => ""
//]);
//查询
//$result = $db->collection("message")->Where(["time"=>['$lte'=>1598864449]])->sort("time",-1)->find();
//$result = $db->collection("message")->Where(["time"=>['$lte'=>1598864449]])->count();
//$result = $db->collection("message")->Where(["time"=>['$lte'=>1598864449]])->sum("time");
//写入
//$result = $db->collection("message")->insert([
//    "from" => "a",
//    "type" => "write",
//    "content" => "哈哈",
//    "time" => time(),
//]);
//更新
//$result = $db->collection("message")->where(["from"=>"a"])->update(["type"=>"ssd"]);
//删除
//$result = $db->collection("message")->where(["from"=>"a"])->delete();
//aggregate 聚合
//$result = $db->collection("message")->aggregate([
//    ['$match'=>['time'=>['$gte'=>1598955498]]],
//    ['$group' => ["_id"=>'$time', "total" => ['$sum' => 1]]]
//]);
//var_dump($result);
