<?php

/**
 *
 * 作者: 范圣帅(fanshengshuai@gmail.com)
 * 时间: 2012-07-02 01:22:18
 *
 * vim: set expandtab sw=4 ts=4 sts=4
 * $Id: DB.php 273 2012-08-22 10:37:34Z fanshengshuai $
 */
class FDB {

    private static $_connects = array();

    private $_dbh;

    /**
     * FDB 构造函数
     *
     * @param string $dsn
     * @param string $user
     * @param string $password
     * @param string $charset
     * @param string $failOver
     * @param boolean $persistent
     * @param integer $timeout
     *
     * @throws FDB_Exception
     */
    private function __construct($dsn, $user, $password, $charset, $failOver = '', $persistent = false, $timeout = 0) {

        $attr = array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_PERSISTENT => $persistent);
        if (0 < $timeout) {
            $attr[PDO::ATTR_TIMEOUT] = $timeout;
        }

        try {
            $this->_dbh = new PDO($dsn, $user, $password, $attr);
            $this->_dbh->exec("SET NAMES '" . $charset . "'");
        } catch (PDOException $e) {

            if ($failOver) {
                try {
                    $this->_dbh = new PDO($failOver, $user, $password, $attr);
                    $this->_dbh->exec("SET NAMES '" . $charset . "'");
                } catch (PDOException $e) {
                    throw new FDB_Exception("can't connect to the server because:" . $e->getMessage());
                }
            } else {

                throw new FDB_Exception("连接数据库失败：" . $e->getMessage());
            }
        }
    }


    public function table($t) {
        return $t;
    }


    /**
     * 获取数据库连接类
     *
     * @throws Exception
     * @internal param string $dsn
     * @internal param string $user
     * @internal param string $password
     * @internal param string $charset
     * @internal param string $failOver
     * @internal param bool $persistent
     * @internal param int $timeout
     * @return FDB 实例
     */
    public static function connect() {
        global $_F;

        if (!$_F['db']['config']) {
            $db = 'default';
            $_F['db']['config'] = FConfig::get('db.' . $db);
        } else {
            // check db.php
            if (!include(APP_ROOT . "config/db.php")) {
                throw new Exception ('NO DB CONFIG EXIST ! PLEASE CHECK config/db.php');
            }
        }

        $dsn = $_F['db']['config']['dsn'];

        if (array_key_exists($dsn, self::$_connects)) {
            self::$_connects[$dsn];
        }

        self::$_connects[$dsn] = new FDB(
            $_F['db']['config']['dsn'],
            $_F['db']['config']['user'],
            $_F['db']['config']['password'],
            $_F['db']['config']['charset'],
            $_F['db']['config']['failOver'],
            $_F['db']['config']['persistent'],
            $_F['db']['config']['timeout']
        );

        return self::$_connects[$dsn];
    }

    /**
     * 开启事务
     */
    public function begin() {

        $this->_dbh->beginTransaction();
    }

    /**
     * 提交事务
     */
    public function commit() {

        $this->_dbh->commit();
    }

    /**
     * 回滚事务
     */
    public function rollBack() {

        $this->_dbh->rollBack();
    }

    /**
     * 取得记录的第一行
     *
     * @param sql string $query
     * @param array $params
     *
     * @return mixed
     */
    public function fetchRow($query, $params = array()) {
        global $_F;

        if ($_F['debug']) {
            $_F['debug_info']['sql'][] = $query;
        }

        $stmt = $this->_dbh->prepare($query);
        $stmt->execute($params);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row;

    }

    /**
     * 取得所有的记录
     *
     * @param sql string $query
     * @param bool $from_cache
     *
     * @internal param array $params
     *
     * @return array
     */
    public function fetchAll($query, $from_cache = false) {
        global $_F;

        if ($_F['debug']) {
            $_F['debug_info']['sql'][] = $query;
        }

        $stmt = $this->_dbh->prepare($query);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $rows;
    }

    /**
     * 获取记录的第一行第一列
     *
     * @param string sql $query
     * @param array $params
     *
     * @return string
     */
    public function fetchOne($query, $params = array()) {
        global $_F;

        if ($_F['debug']) {
            $_F['debug_info']['sql'][] = $query;
        }

        $stmt = $this->_dbh->prepare($query);
        $result = $stmt->execute($params);
        if ($result) {
            $row = $stmt->fetchColumn();
        }
        return $row;
    }


    /**
     * 执行sql 语句
     *
     * @param $query
     * @param array $params
     *
     * @return bool
     */
    public function exec($query, $params = array()) {
        global $_F;

        if ($_F['debug']) {
            $_F['debug_info']['sql'][] = $query;
        }

        $stmt = $this->_dbh->prepare($query);
        return $stmt->execute($params);
    }

    /**
     * 获取最后一条记录的id
     */
    public function lastInsertId() {

        return $this->_dbh->lastInsertId();
    }

    /**
     * 关闭数据库连接
     *
     * @param string $dsn
     */
    public function close($dsn = null) {

        if ($dsn) {
            self::$_connects[$dsn] = NULL;
        } else {
            $this->_dbh = NULL;
        }
    }


    public static function query($sql) {
        global $_F;

        $_dbh = FDB::connect();
        return $_dbh->exec($sql);
    }

    public static function fetch($sql) {
        global $_F;

        $_dbh = FDB::connect();

        return $_dbh->fetchAll($sql, $from_cache = false);
    }

    public static function fetchCached($sql, $cache_time = 3600) {
        $cache_key = "sql-fetch_{$sql}";
        $cache_content = C::get($cache_key);
        if ($cache_content) {
            return $cache_content;
        }

        $cache_content = self::fetch($sql);
        C::set($cache_key, $cache_content, $cache_time);
        return $cache_content;
    }

    public static function fetchFirst($sql, $from_cache = false) {
        global $_F;

        $_dbh = FDB::connect();

        return $_dbh->fetchRow($sql);
    }

    public static function fetchFirstCached($sql, $cache_time = 3600) {
        $cache_key = "sql-fetchFirst_{$sql}";
        $cache_content = C::get($cache_key);
        if ($cache_content) {
            return $cache_content;
        }

        $cache_content = self::fetchFirst($sql);
        C::set($cache_key, $cache_content, $cache_time);
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
    public static function insert($table, $data) {

        if (!$data['create_time']) {
            $data['create_time'] = date('Y-m-d H:i:s');
        }

        if (!$data['status']) {
            $data['status'] = 1;
        }

        $table = new FTable($table);
        return $table->insert($data);
    }

    /**
     * 更新记录
     *
     * @param $table
     * @param $data
     * @param $condition
     *
     * @throws Exception
     * @return bool
     */
    public static function update($table, $data, $condition) {
        global $_F;

        if (!$condition) {
            throw new Exception("FDB update need condition.");
        }

        if (!$data['update_time']) {
            $data['update_time'] = date('Y-m-d H:i:s');
        }

        $c = '';
        if (is_array($condition)) {
            foreach ($condition as $_k => $_v) {
                $c .= " and {$_k}='{$_v}'";
            }

            $condition = ltrim($c, ' and');
        }

        $table = new FTable($table);
        return $table->update($data, $condition);
    }

    /**
     * 删除数据
     *
     * @param      $table string 表名
     * @param      $condition string 条件
     * @param bool $is_real_delete true 真删除，false 假删除
     *
     * @throws Exception
     * @return bool
     */
    public static function remove($table, $condition, $is_real_delete = false) {

        if (!$condition) {
            throw new Exception("FDB remove need condition. Remove is a very dangerous operation.");
        }

        $table = new FTable($table);

        if ($is_real_delete) {
            $table->remove($condition);
        } else {
            $data = array(
                'status' => 2,
                'remove_time' => date('Y-m-d H:i:s'),
            );
            $table->update($data, $condition);
        }

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
    public static function incr($table, $field, $conditions = null, $unit = 1) {
        $table = new FTable($table);
        $table->increase($field, $conditions, array(), $unit);
    }

    /**
     * 字段数据 -1
     *
     * @param $table
     * @param $field
     * @param null $conditions
     * @param array $params
     * @param int $unit
     */
    public static function decr($table, $field, $conditions = null, $params = array(), $unit = 1) {
        $table = new FTable($table);
        $table->decrease($field, $conditions, array(), $unit);
    }

    /**
     * 统计符合条目的数目
     *
     * @param $table
     * @param null $conditions
     *
     * @return int
     */
    public static function count($table, $conditions = null) {
        $table = new FTable($table);
        return $table->count($conditions);
    }
}
