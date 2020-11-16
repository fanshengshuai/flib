<?php

/**
 *
 * 作者: 范圣帅(fanshengshuai@gmail.com)
 * 时间: 2012-07-02 01:22:18
 *
 * vim: set expandtab sw=4 ts=4 sts=4
 * $Id: FDB.php 764 2015-04-14 15:09:06Z fanshengshuai $
 */
class FDB
{

  private static $__connects = array();
  public static function getConfig()
  {
    return FConfig::get('db');
  }

  /**
   * @param string $rw_type
   * @return PDO
   */
  public static function connect($rw_type = 'rw')
  {
    global $_F;

    $gConfig = self::getConfig();

    $curConfig = null;
    if ($rw_type == 'w') {
      $curConfig = $gConfig['server'][array_rand($gConfig['server'])];
    } elseif ($rw_type == 'r' || $rw_type == 'rw') {
      if (isset($gConfig['server_read']) && count($gConfig['server_read']) > 0) {
        $curConfig = $gConfig['server_read'][array_rand($gConfig['server_read'])];
      } else {
        $curConfig = $gConfig['server'][array_rand($gConfig['server'])];
      }
    } else {
      if ($gConfig['server_others'][$rw_type]) {
        $curConfig = $gConfig['server_others'][$rw_type];
      } elseif ($gConfig['server'][$rw_type]) {
        $curConfig = $gConfig['server'][$rw_type];
      } elseif ($gConfig['server_read'][$rw_type]) {
        $curConfig = $gConfig['server_read'][$rw_type];
      } else {
        FException::getInstance()->traceError(
          new Exception("DB Connect Config [{$rw_type}] not found!"));
      }
    }

    $dsn = $curConfig['dsn'];

    if (isset(self::$__connects[$dsn])) {
      return self::$__connects[$dsn];
    }

    $attr = array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_PERSISTENT => false);
    $attr[PDO::ATTR_TIMEOUT] = 5;

    try {
      $dbh = new PDO($curConfig['dsn'], $curConfig['user'], $curConfig['password'], $attr);
      $dbh->exec("SET NAMES '" . $gConfig['charset'] . "'");
    } catch (PDOException $e) {
      FException::getInstance()->traceError(
        new Exception("连接数据库[{$rw_type}]失败：" . $e->getMessage()));
    }

    self::$__connects[$dsn] = $dbh;

    return $dbh;
  }

  public static function table($t, $as = null)
  {
    return new FTable($t, $as);
  }

  public static function getMongo($mongo) {

  }

  /**
   * 开启事务
   */
  public static function begin()
  {

    self::connect()->beginTransaction();
  }

  /**
   * 提交事务
   */
  public static function commit()
  {

    self::connect()->commit();
  }

  /**
   * 回滚事务
   */
  public static function rollBack()
  {

    self::connect()->rollBack();
  }

  /**
   * 关闭数据库连接
   *
   * @param string $dsn
   */
  public function close($dsn = null)
  {

    if ($dsn) {
      self::$__connects[$dsn] = null;
    } else {
      $this->_dbh = null;
    }
  }

  public static function query($sql, $db_conf = 'w')
  {
    global $_F;

    $_dbh = self::connect($db_conf);
    $ret = 0;

    if ($_F['debug']) {
      $_F['debug_info']['sql'][] = $sql;
      FDebug::logSql($sql);
    }

    try {
      $ret = $_dbh->exec($sql);
    } catch (Exception $e) {
      $_F['current_sql'] = $sql;
      FException::getInstance()->traceError($e);
    }

    return $ret;
  }

  /**
   * 返回结果集
   *
   * @param string $sql 查询语句
   * @param array $params 参数
   * @return array
   */
  public static function fetch($sql, $params = null, $db_conf = 'r')
  {
    global $_F;

    $_dbh = self::connect($db_conf);

    if ($_F['debug']) {
      $_sql = $sql;
      foreach ($params as $k => $item) {
        $_sql = str_replace($k, "'$item'", $_sql);
      }
      $_F['debug_info']['sql'][] = $_sql;
    }

    try {
      $stmt = $_dbh->prepare($sql);
      $stmt->execute($params);
      $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
      $_F['current_sql'] = $sql;
      FException::getInstance()->traceError($e);
    }

    return $rows;
  }

  public static function fetchCached($sql, $cache_time = 3600)
  {
    $cache_key = "sql-fetch_{$sql}";
    $cache_content = FCache::get($cache_key);
    if ($cache_content) {
      return $cache_content;
    }

    $cache_content = self::fetch($sql);
    FCache::set($cache_key, $cache_content, $cache_time);
    return $cache_content;
  }

  /**
   * 查一条数据
   *
   * @param $sql
   * @param string $db_conf
   * @return array
   */
  public static function fetchRow($sql, $params = null, $db_conf = 'r')
  {
    return self::fetchFirst($sql, $params, $db_conf);
  }

  /**
   * @param $sql
   * @param string $db_conf
   * @return mixed
   */
  public static function fetchFirst($sql, $params = null, $db_conf = 'r')
  {
    global $_F;

    if ($_F['debug']) {
      $_F['debug_info']['sql'][] = $sql;
    }

    $dbh = self::connect($db_conf);

    $stmt = $dbh->prepare($sql);
    $stmt->execute($params);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    // $stmt->debugDumpParams();
    return $row;
  }

  public static function fetchFirstCached($sql, $cache_time = 3600)
  {
    $cache_key = "sql-fetchFirst_{$sql}";
    $cache_content = FCache::get($cache_key);
    if ($cache_content) {
      return $cache_content;
    }

    $cache_content = self::fetchFirst($sql);
    FCache::set($cache_key, $cache_content, $cache_time);
    return $cache_content;
  }

  /**
   * 插入数据
   *
   * @param $table
   * @param $data
   *
   * @return bool
   */
  public static function insert($table, $data, $db_conf = '')
  {

    if ($db_conf) {
      $table = new FTable($table, '', $db_conf);
    } else {
      $table = new FTable($table);
    }
    return $table->insert($data);
  }

  /**
   * 更新记录
   *
   * @param $table
   * @param $data
   * @param $condition
   *
   * @return bool
   */
  public static function update($table, $data, $condition, $db_conf = '')
  {
    global $_F;

    if (!$condition) {
      FException::getInstance()->traceError(
        new Exception("FDB update need condition."));
    }

    if ($db_conf) {
      $table = new FTable($table, '', $db_conf);
    } else {
      $table = new FTable($table);
    }
    return $table->update($data, $condition);
  }

  /**
   * 删除数据
   *
   * @param      $table string 表名
   * @param      $condition string|array 条件
   * @param bool $is_real_delete true 真删除，false 假删除
   *
   * @return bool
   */
  public static function remove($table, $condition, $is_real_delete = false, $db_conf = '')
  {
    if ($db_conf) {
      $table = new FTable($table, '', $db_conf);
    } else {
      $table = new FTable($table);
    }
    $table->where($condition)->remove($is_real_delete);

    return true;
  }

  /**
   * 字段数据 +1
   *
   * @param $table
   * @param $field
   * @param null $conditions
   * @param int $unit
   */
  public static function incr($table, $field, $conditions = null, $unit = 1, $db_conf = '')
  {
    if ($db_conf) {
      $table = new FTable($table, '', $db_conf);
    } else {
      $table = new FTable($table);
    }
    $table->where($conditions)->increase($field, $unit);
  }

  /**
   * 字段数据 -1
   *
   * @param $table
   * @param $field
   * @param null $conditions
   * @param int $unit
   * @param string $db_conf
   */
  public static function decr($table, $field, $conditions = null, $unit = 1, $db_conf = '')
  {
    if ($db_conf) {
      $table = new FTable($table, '', $db_conf);
    } else {
      $table = new FTable($table);
    }
    $table->where($conditions)->decrease($field, $unit);
  }

  /**
   * 统计符合条目的数目
   *
   * @param $table
   * @param array $conditions
   *
   * @return int
   */
  public static function count($table, $conditions = null, $db_conf = '')
  {
    if ($db_conf) {
      $table = new FTable($table, '', $db_conf);
    } else {
      $table = new FTable($table);
    }
    return $table->where($conditions)->count();
  }
}