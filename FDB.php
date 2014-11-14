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


    public static function getConfig() {
        return FConfig::get('db');
    }

    /**
     * @param string $rw_type
     *
     * @throws Exception
     * @return PDO
     */
    public static function connect($rw_type = 'rw') {
        global $_F;

        $gConfig = self::getConfig();

        $curConfig = null;
        if ($rw_type == 'w') {
            $curConfig = $gConfig['server'][array_rand($gConfig['server'])];
        } else {
            if ($gConfig['server_read'] && count($gConfig['server_read']) > 0) {
                $curConfig = $gConfig['server_read'][array_rand($gConfig['server_read'])];
            } else {
                $curConfig = $gConfig['server'][array_rand($gConfig['server'])];
            }
        }

        $dsn = $curConfig['dsn'];

        FLogger::write($rw_type, $dsn);

        if (isset(self::$_connects[$dsn])) {
            return self::$_connects[$dsn];
        }

        $attr = array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_PERSISTENT => false);
        $attr[PDO::ATTR_TIMEOUT] = 5;

        try {
            $dbh = new PDO($curConfig['dsn'], $curConfig['user'], $curConfig['password'], $attr);
            $dbh->exec("SET NAMES '" . $gConfig['charset'] . "'");
        } catch (PDOException $e) {
            throw new Exception("连接数据库失败：" . $e->getMessage());
        }

        self::$_connects[$dsn] = $dbh;

        return $dbh;
    }

    public function table($t) {
        return $t;
    }

    /**
     * 开启事务
     */
    public static function begin() {

        self::connect()->beginTransaction();
    }

    /**
     * 提交事务
     */
    public static function commit() {

        self::connect()->commit();
    }

    /**
     * 回滚事务
     */
    public static function rollBack() {

        self::connect()->rollBack();
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

        $_dbh = self::connect('w');
        return $_dbh->exec($sql);
    }

    public static function fetch($sql) {
        global $_F;

        $_dbh = self::connect('r');

        if ($_F['debug']) {
            $_F['debug_info']['sql'][] = $sql;
        }

        $stmt = $_dbh->prepare($sql);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $rows;
    }

    public static function fetchCached($sql, $cache_time = 3600) {
        $cache_key = "sql-fetch_{$sql}";
        $cache_content = FCache::get($cache_key);
        if ($cache_content) {
            return $cache_content;
        }

        $cache_content = self::fetch($sql);
        FCache::set($cache_key, $cache_content, $cache_time);
        return $cache_content;
    }

    public static function fetchFirst($sql, $params = null) {
        global $_F;

        if ($_F['debug']) {
            $_F['debug_info']['sql'][] = $sql;
        }

        $dbh = self::connect('r');

        $stmt = $dbh->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row;
    }

    public static function fetchFirstCached($sql, $cache_time = 3600) {
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
