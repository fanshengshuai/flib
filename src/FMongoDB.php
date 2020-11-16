<?php

/**
 * User: fanshengshuai
 * Date: 15-1-15
 * Time: 上午11:36
 * Mongodb类** examples:
 * $mongo = new FMongodb("127.0.0.1:11223");
 * $mongo->selectDb("test_db");
 * 创建索引
 * $mongo->ensureIndex("test_table", array("id"=>1), array('unique'=>true));
 * 获取表的记录
 * $mongo->count("test_table");
 * 插入记录
 * $mongo->insert("test_table", array("id"=>2, "title"=>"asdqw"));
 * 更新记录
 * $mongo->update('user_message', $dataMsg, array('$set' => $newData));
 * $mongo->update("test_table", array("id"=>1),array("id"=>1,"title"=>"bbb"));
 * 更新记录-存在时更新，不存在时添加-相当于set
 * $mongo->update("test_table", array("id"=>1),array("id"=>1,"title"=>"bbb"),array("upsert"=>1));
 * 查找记录
 * $mongo->find("c", array("title"=>"asdqw"), array("start"=>2,"limit"=>2,"sort"=>array("id"=>1)))
 * or 查询
 * $mongo->find("user_message", array('$or' => array(array('from_id' => 10001), array('to_id' => 10001))), array('limit' => 10, 'sort' => array('auto_id' => -1)));
 * 查找一条记录
 * $mongo->findOne("msg", array("id"=>1));
 * 删除记录
 * $mongo->remove("ttt", array("title"=>"bbb"));
 * 仅删除一条记录
 * $mongo->remove("ttt", array("title"=>"bbb"), array("justOne"=>1));
 * 获取Mongo操作的错误信息
 * $mongo->getError();
 */
class FMongoDBNull
{
  public static function findOne()
  {
  }

  public static function find()
  {
  }

  public static function update()
  {
  }

  public static function count()
  {
  }
}

class FMongoDB
{

  private $mongo; //Mongodb连接
  private $curr_db_name;
  private $error;

  /**
   * @param string $db_conf
   * @return MongoDB
   */
  public static function getInstance($db_conf = 'default')
  {
    global $_F;

    if (!isset($_F['mongo_dbs'][$db_conf])) {
      try {
        $connect = new MongoClient(FConfig::get('db.mongoDB.' . $db_conf . '.dsn'));
        $_F['mongo'] = $connect->selectDb(FConfig::get('db.mongoDB.' . $db_conf . '.db'));
      } catch(MongoConnectionException $e) {
        FException::getInstance()->traceError($e);
      }
    }

    $_F['mongo_dbs'][$db_conf] = $_F['mongo'];
    return $_F['mongo'];
  }

  /**
   * 构造函数
   * 支持传入多个mongo_server(1.一个出问题时连接其它的server 2.自动将查询均匀分发到不同server)
   *
   * 参数：
   * @param string $mongo_server 数组或字符串<br/>-array("127.0.0.1:1111", "127.0.0.1:2222")-"127.0.0.1:1111"
   * @param bool $connect 初始化mongo对象时是否连接，默认连接
   * @param bool $auto_balance 是否自动做负载均衡，默认是
   *
   * 返回值：
   * 成功：mongo object
   * 失败：false
   */
  public function __construct($mongo_server, $connect = true, $auto_balance = true)
  {
    if (is_array($mongo_server)) {
      $mongo_server_num = count($mongo_server);
      if ($mongo_server_num > 1 && $auto_balance) {
        $prior_server_num = rand(1, $mongo_server_num);
        $rand_keys = array_rand($mongo_server, $mongo_server_num);
        $mongo_server_str = $mongo_server[$prior_server_num - 1];
        foreach ($rand_keys as $key) {
          if ($key != $prior_server_num - 1) {
            $mongo_server_str .= ',' . $mongo_server[$key];
          }
        }
      } else {
        $mongo_server_str = implode(',', $mongo_server);
      }
    } else {
      $mongo_server_str = $mongo_server;
    }
    try {
      $this->mongo = new MongoClient($mongo_server_str, array('connect' => $connect));
    } catch (Exception $e) {
      FException::getInstance()->traceError($e);
    }
  }

  /**
   * 连接mongodb server
   *
   * 参数：无
   *
   * 返回值：
   * 成功：true
   * 失败：false
   */
  public function connect()
  {
    try {
      $this->mongo->connect();
      return true;
    } catch (MongoConnectionException $e) {
      $this->error = $e->getMessage();
      return false;
    }
  }

  /**
   * select db
   *
   * 参数：$dbname
   *
   * 返回值：无
   */
  public function selectDb($dbname)
  {
    $this->curr_db_name = $dbname;
  }

  /**
   * 创建索引：如索引已存在，则返回。
   *
   * 参数：
   * @param string $table_name 表名
   * @param array $index 索引-array("id"=>1)-在id字段建立升序索引
   * @param array $index_param 其它条件-是否唯一索引等
   * @return bool
   *
   * 返回值：
   * 成功：true
   * 失败：false
   */
  public function ensureIndex($table_name, $index, $index_param = array())
  {
    $dbname = $this->curr_db_name;
    $index_param['safe'] = 1;
    try {
      $this->mongo->$dbname->$table_name->ensureIndex($index, $index_param);
      return true;
    } catch (MongoCursorException $e) {
      $this->error = $e->getMessage();
      return false;
    }
  }

  /**
   * 插入记录
   * @param string $table_name  表名
   * @param array $record  记录
   *
   * 返回值：
   * 成功：true
   * 失败：false
   */
  public static function insert($table_name, $record)
  {
    global $_F;
    if (!$_F['mongo']) {
      self::getInstance();
    }

    try {
      if ($record['id']) {
        $record['_id'] = intval($record['id']);
        unset($record['id']);
      }

      if (!$record['_id']) {
        $record = array_merge(['_id' => self::auto_id($table_name)], $record);
      }
      $ret = $_F['mongo']->$table_name->insert($record, array('w' => true));
    } catch (Exception $e) {
      // throw $e;
      FException::getInstance()->traceError($e);
    }
  }

  /**
   * 获取数据，返回数组
   *
   * @param String 表名
   * @param array $query 查询条件
   * @param array $result_condition 其他条件，如排序(sort)，分页(start, limit)
   * @param array $fields 返回的字段
   * @return array
   */
  public static function fetch($table_name, $query = [], $result_condition = array(), $fields = array())
  {
    global $_F;
    if (!$_F['mongo']) {
      self::getInstance();
    }

    try {
      $cursor = $_F['mongo']->$table_name->find($query, $fields);
      if (!empty($result_condition['start'])) {
        $cursor->skip($result_condition['start']);
      }
      if (!empty($result_condition['limit'])) {
        $cursor->limit($result_condition['limit']);
      }
      if (!empty($result_condition['sort'])) {
        $cursor->sort($result_condition['sort']);
      }
      $result = array();
      while ($cursor->hasNext()) {
        $tmp = $cursor->getNext();
        if ($tmp['_id']->{'$id'}) {
          $tmp['_id'] = $tmp['_id']->{'$id'};
        }
        $tmp['id'] = $tmp['_id'];
        $result[] = $tmp;
      }

      return $result;
    } catch (Exception $e) {
      // throw $e;
      FException::getInstance()->traceError($e);
    }

    return null;
  }

  public static function fetchFirst($table_name, $query = [], $result_condition = array('sort' => ['id' => -1]), $fields = array())
  {
    // 更换id为_id
    if ($query['id']) {
      $query['_id'] = $query['id'];
      unset($query['id']);
    }

    $ret = self::fetch($table_name, $query, $result_condition, $fields);
    if ($ret) {
      return $ret[0];
    }

    return null;
  }

  public static function inc($table, $field, $query, $step = 1)
  {
    global $_F;
    if (!$_F['mongo']) {
      self::getInstance();
    }

    $command = array(
      'findandmodify' => $table, 'update' => ['$inc' => array($field => $step)],
      'query' => $query, 'new' => true, 'upsert' => true,
    );
    $id = $_F['mongo']->command($command);
    // var_dump($id);exit;
    return $id['value']['id'];
  }

  public static function auto_id($name)
  {
    global $_F;
    if (!$_F['mongo']) {
      self::getInstance();
    }

    $update = array('$inc' => array("id" => 1));
    $query = array('name' => $name);
    $command = array(
      'findandmodify' => '_auto_ids', 'update' => $update,
      'query' => $query, 'new' => true, 'upsert' => true,
    );
    $id = $_F['mongo']->command($command);
    return $id['value']['id'];
  }

  /**
   * 删除记录
   *
   * 参数：
   * $table_name:表名
   * $condition:删除条件
   * $options:删除选择-justOne
   *
   * 返回值：
   * 成功：true
   * 失败：false
   */
  public static function delete($table_name, $condition, $options = array())
  {
    global $_F;
    if (!$_F['mongo']) {
      self::getInstance();
    }

    try {
      $_F['mongo']->$table_name->remove($condition, $options);
      return true;
    } catch (MongoCursorException $e) {
      //   $this->error = $e->getMessage();
      return false;
    }
  }

  /**
   * 查询表的记录数
   *
   * 参数：
   * @param string $table_name 表名
   * @param null $condition
   *
   * 返回值：表的记录数
   * @return int
   */
  public static function count($table_name, $condition = null)
  {
    global $_F;
    if (!$_F['mongo']) {
      self::getInstance();
    }
    return $_F['mongo']->$table_name->count($condition);
  }

  /**
   * Undocumented function
   *
   * @param String $table_name 表名
   * @param array $newData 新的数据记录
   * @param array $condition 更新条件
   * @param array $options 更新选择-upsert/multiple/w/setOnInsert
   * @return boolean 成功：true 失败：false
   */
  public static function update($table_name, $newData, $condition, $options = array('multiple' => true))
  {
    global $_F;
    if (!$_F['mongo']) {
      self::getInstance();
    }

    // 更换id为_id
    if ($condition['id']) {
      $condition['_id'] = intval($condition['id']);
      unset($condition['id']);
    }

    if ($newData['id']) {
      // $newData['_id'] = $newData['id'];
      unset($newData['id']);
    }

    $data = array("\$set" => $newData);

    if (!isset($options['w'])) {
      $options['w'] = 1;
    }

    if (!isset($options['multiple'])) {
      $options['multiple'] = true;
    }

    if (!isset($options['upsert'])) {
      $options['upsert'] = false; // 没找到不新增记录
    }

    if ($options['setOnInsert']) {
      //   $options['setOnInsert']['_id'] = self::auto_id($table_name);
      $data["\$setOnInsert"] = $options['setOnInsert'];
    }

    try {
      $_F['mongo']->$table_name->update($condition, $data, $options);
      return true;
    } catch (MongoCursorException $e) {
      FException::getInstance()->traceError($e);
      // $this->error = $e->getMessage();
      return false;
    }
  }

  /**
   * 删除记录
   *
   * 参数：
   * $table_name:表名
   * $condition:删除条件
   * $options:删除选择-justOne
   *
   * 返回值：
   * 成功：true
   * 失败：false
   * @param string $table_name
   * @param array $condition
   * @param array $options
   * @return bool
   */
  public function remove($table_name, $condition, $options = array())
  {
    $dbname = $this->curr_db_name;
    // 更换id为_id
    if ($condition['id']) {
      $condition['_id'] = $condition['id'];
      unset($condition['id']);
    }

    //        $options['safe'] = 1;
    try {
      $this->mongo->$dbname->$table_name->remove($condition, $options);
      return true;
    } catch (MongoCursorException $e) {
      FException::getInstance()->traceError($e);
      // $this->error = $e->getMessage();
      return false;
    }
  }

  /**
   * 查找记录
   *
   * 参数：
   * $table_name:表名
   * $query_condition:字段查找条件
   * $result_condition:查询结果限制条件-limit/sort等
   * $fields:获取字段
   *
   * 返回值：
   * 成功：记录集
   * 失败：false
   */
  public function find($table_name, $query_condition, $result_condition = array(), $fields = array())
  {
    $dbname = $this->curr_db_name;
    $cursor = $this->mongo->$dbname->$table_name->find($query_condition, $fields);
    //        $cursor->timeout(-1);
    if (!empty($result_condition['start'])) {
      $cursor->skip($result_condition['start']);
    }
    if (!empty($result_condition['limit'])) {
      $cursor->limit($result_condition['limit']);
    }
    if (!empty($result_condition['sort'])) {
      $cursor->sort($result_condition['sort']);
    }
    $result = array();
    try {
      while ($cursor->hasNext()) {
        $result[] = $cursor->getNext();
      }
    } catch (MongoConnectionException $e) {
      $this->error = $e->getMessage();
      return false;
    } catch (MongoCursorTimeoutException $e) {
      $this->error = $e->getMessage();
      return false;
    }
    return $result;
  }

  /**
   * 查找一条记录
   *
   * 参数：
   * @param string $table_name 表名
   * @param array $condition 查找条件
   * @param array $fields 获取字段
   * @return array|null
   *
   * 返回值：
   * 成功：一条记录
   * 失败：false
   */
  public function findOne($table_name, $condition, $fields = array())
  {
    $dbname = $this->curr_db_name;
    return $this->mongo->$dbname->$table_name->findOne($condition, $fields);
  }

  public function runCommand($command)
  {
    $dbname = $this->curr_db_name;
    return $this->mongo->$dbname->command($command);
  }

  /**
   * 分组
   * @param string $table_name
   * @param array $keys
   * @param array $initial
   * @param MongoCode $reduce
   * @param array $condition
   * @return array
   */
  public function group($table_name, $keys, array $initial, MongoCode $reduce, array $condition = array())
  {
    $dbname = $this->curr_db_name;
    return $this->mongo->$dbname->$table_name->group($keys, $initial, $reduce, $condition);
  }

  /**
   * 获取当前错误信息
   *
   * 参数：无
   *
   * 返回值：当前错误信息
   */
  public function getError()
  {
    return $this->error;
  }
}