<?php

/**
 *
 * 作者: 范圣帅(fanshengshuai@gmail.com)
 *
 * 创建: 2012-08-08 10:57:22
 * vim: set expandtab sw=4 ts=4 sts=4 *
 *
 * $Id$
 */
class FDB_Table {

    /**
     * _dbh
     * DB对象的实例
     *
     * @var DB
     */
    private $_dbh;

    /**
     * _table
     * 当前操作的物理表名
     *
     * @var string
     */
    protected $_table;

    /**
     * 构建数据操作实例
     *
     * @param string $table
     *
     * @internal param array
     */
    public function __construct($table) {
        global $_F;

        $this->_dbh = FDB::connect();
        $this->_table = $_F['db']['config']['table_pre'] . $table;
    }

    /**
     * getDbh
     * 获取DB对象
     *
     * @return object
     */
    public function getDbh() {

        return $this->_dbh;
    }

    /**
     * 根据表名和key分库(分表)
     *
     * 目前shardKey只支持一个字段
     *
     * 根据表名前缀(第一个_前面的部分)区分是哪个模块，进而读取相应模块的数据库配置文件，
     * 如果需要根据模块和$key分数据库，则返回相应的配置；否则使用默认的配置
     * 在配置文件conf/db.php中，指定$conf['MODULE.siteTables.TABLE.num']的值，可实现所有数据库上TABLE的分表
     *
     * @param string $table 表名
     * @param array $shardKey 切分关键字
     *
     * @return array
     *           + db 数据库相关配置
     *           + table 物理表名
     */
    public static function shardTable($table, $shardKey = array()) {

        $result = array();

        // 不分库的情况
        if (!$shardKey || !array_key_exists($table, $tables)) {
            $result['db'] = 'onsby_com';
            $result['table'] = $table;

            return $result;
        }
    }

    /**
     * 获取一条记录
     *
     * @param string $conditions
     * @param array $columns
     * @param array $params
     *
     * @throws FDB_Exception
     * @internal param array $param
     * @internal param \columns $array 列
     *
     * @return array || null
     */
    public function find($conditions = null, $columns = array('*'), $params = array()) {

        $columns = implode(',', $columns);
        $sql = "SELECT $columns FROM $this->_table";

        if ($conditions) {
            $sql .= ' WHERE ' . $conditions;
        }

        try {
            $data = $this->_dbh->fetchRow($sql, $params);
        } catch (PDOException $e) {
            throw new FDB_Exception($e);
        }

        return $data;
    }

    /**
     * 取得所有的记录
     *
     * @param array $conditions
     * @param array $params
     * @param array $columns
     * @param integer $start
     * @param integer $limit
     * @param array|string $order
     *   fieldName1 => [ASC|DESC]
     *   fieldName2 => [ASC|DESC]
     *
     * @throws FDB_Exception
     * @return array
     */
    public function findAll($conditions = null, $params = array(), $columns = array('*'),
                            $start = 0, $limit = 0, $order = array()) {

        $columns = implode(',', $columns);
        $sql = "SELECT $columns FROM $this->_table ";

        if ($conditions) {
            $sql .= ' WHERE ' . $conditions;
        }

        if ($order && is_array($order)) {
            $orderClause = '';
            foreach ($order as $field => $orderBy) {
                $orderClause .= $field . ' ' . $orderBy . ',';
            }
            $sql .= ' ORDER BY ' . rtrim($orderClause, ',');
        } else if ($order && is_string($order)) {
            $sql .= ' ORDER BY ' . $order;
        }

        if ($limit) {
            $sql .= " LIMIT $start, $limit";
        }

        try {
            $rows = $this->_dbh->fetchAll($sql, $params);
        } catch (PDOException $e) {
            throw new FDB_Exception($e);
        }

        return $rows;
    }

    /**
     * findBySql
     *
     * @param  string $sql
     * @param  array $params
     *
     * @throws FDB_Exception
     * @return array
     */
    public function findBySql($sql, $params = array()) {

        try {
            $sql = $this->_rewriteSql($sql);
            $data = $this->_dbh->fetchAll($sql, $params);
        } catch (PDOException $e) {
            throw new FDB_Exception($e);
        }

        return $data;
    }

    /**
     * 删除记录
     *
     * @param string $conditions
     * @param array $params
     *
     * @throws FDB_Exception
     * @internal param array $param
     *
     * @return int affect rows
     */
    public function remove($conditions, $params = array()) {

        $sql = "DELETE FROM $this->_table WHERE $conditions ";
        try {
            return $this->_dbh->exec($sql, $params);
        } catch (PDOException $e) {
            throw new FDB_Exception($e);
        }
    }

    /**
     * 插入一条记录 或更新表记录
     *
     * @param array $data
     * @param string $conditions
     * @param array $params
     *
     * @throws FDB_Exception
     * @internal param array $param
     *
     * @return bool || int
     */
    public function save($data, $conditions = null, $params = array()) {

        $tempParams = array();
        $set = array();
        foreach ($data as $k => $v) {
            array_push($set, $k . '= ?');
            array_push($tempParams, $v);
        }

        if ($conditions) {
            // 更新
            $sql = "UPDATE $this->_table SET " . join(',', $set) . " WHERE $conditions";
            $params = array_merge($tempParams, $params);
        } else {
            // 插入
            $sql = "INSERT INTO  $this->_table SET " . join(',', $set);
            $params = $tempParams;
        }

        // 捕获PDOException后 抛出FDB_Exception
        try {
            $this->_dbh->exec($sql, $params);

            return $this->lastInsertId();
        } catch (PDOException $e) {
            throw new FDB_Exception($e);
        }
    }

    /**
     * replace
     * 根据主键替换或保存
     *
     * @param array $data
     *
     * @throws FDB_Exception
     * @return mixed
     */
    public function replace($data) {

        $tempParams = array();
        $set = array();
        foreach ($data as $k => $v) {
            array_push($set, $k . '= ?');
            array_push($tempParams, $v);
        }

        $sql = "REPLACE INTO $this->_table SET " . join(',', $set);

        try {
            return $this->_dbh->exec($sql, $tempParams);
        } catch (PDOException $e) {
            throw new FDB_Exception($e);
        }
    }

    /**
     * 获取刚刚写入记录的ID
     *
     * @throws FDB_Exception
     * @return int
     */
    public function lastInsertId() {

        try {
            return $this->_dbh->lastInsertId();
        } catch (PDOException $e) {
            throw new FDB_Exception($e);
        }
    }

    /**
     * 一次插入多条记录
     *
     * @param  $data
     *
     * @throws FDB_Exception
     * @return bool
     */
    public function multiInsert($data) {

        $count = count($data);
        $getKeys = array_keys($data[0]);
        $countKeys = count($getKeys);
        $columns = implode(',', $getKeys);

        // 构造问号表达式
        $tmpArr = array();
        for ($i = 0; $i < $countKeys; $i++) {
            $tmpArr[] = '?';
        }
        $tmpStr = implode(',', $tmpArr);
        $tmpStr = '(' . $tmpStr . ')';
        $tmpArr2 = array();
        $mergeArr = array();
        for ($i = 0; $i < $count; $i++) {
            $tmpArr2[] = $tmpStr;
            $mergeArr = array_merge($mergeArr, array_values($data[$i]));
        }

        $tmpStr2 = implode(',', $tmpArr2);
        $conditions = "INSERT INTO $this->_table ($columns) VALUES $tmpStr2";
        try {
            return $this->_dbh->exec($conditions, $mergeArr);
        } catch (PDOException  $e) {
            throw new FDB_Exception($e);
        }
    }

    /**
     * 数据表列名到缓存列名的转换
     *
     * @param array $array
     *
     * @return array
     */
    public function keyMap($array) {

        $outArray = array();
        foreach ($array as $key => $value) {
            $keyArr = explode('_', $key);
            $outKey = ucfirst($keyArr[1]) . ucfirst(@$keyArr[2]) . ucfirst(@$keyArr[3]);
            $outArray[$outKey] = $value;
        }

        return $outArray;
    }

    /**
     * count
     * 计算行数
     *
     * @param  string $conditions
     * @param  array $params
     *
     * @throws FDB_Exception
     * @return integer
     */
    public function count($conditions = null, $params = array()) {

        $sql = 'SELECT COUNT(*) FROM ' . $this->_table;
        try {
            if ($conditions) {
                $sql .= ' WHERE ' . $conditions;
            }

            return $this->_dbh->fetchOne($sql, $params);

        } catch (PDOException  $e) {
            throw new FDB_Exception($e);
        }
    }

    /**
     * exec
     * 执行sql语句
     *
     * @param  string $sql
     * @param array|string $params
     *
     * @throws FDB_Exception
     * @return void
     */
    public function exec($sql, $params = array()) {

        global $_F;

        try {
            $sql = $this->_rewriteSql($sql);

            if ($_F['debug']) {
                $_F['debug_info']['sql'][] = $sql;
            }

            $result = $this->_dbh->exec($sql, $params);
        } catch (PDOException $e) {
            throw new FDB_Exception($e);
        }

        return $result;
    }

    public function incr($field, $conditions = null, $params = array(), $unit = 1) {

        $sql = 'UPDATE ' . $this->_table . " SET `$field` = `$field` + $unit";
        if ($conditions) {
            $sql .= ' WHERE ' . $conditions;
        }
        try {
            $result = $this->_dbh->exec($sql, $params);
        } catch (PDOException $e) {
            throw new FDB_Exception($e->getMessage, $e->getCode());
        }

        return $result;
    }

    public function decr($field, $conditions = null, $params = array(), $unit = 1) {

        $sql = 'UPDATE ' . $this->_table . " SET $field = IF($field > $unit,  $field - $unit, 0)";
        if ($conditions) {
            $sql .= ' WHERE ' . $conditions;
        }

        try {
            $result = $this->_dbh->exec($sql, $params);
        } catch (PDOException $e) {
            throw new FDB_Exception($e);
        }

        return $result;
    }

    public function truncate() {

        $sql = "TRUNCATE  $this->_table  ";
        //echo "$sql \n";
        $params = array();
        try {
            return $this->_dbh->exec($sql, $params);
        } catch (PDOException $e) {
            throw new FDB_Exception($e);
        }
    }

    public function beginTransaction() {

        return $this->_dbh->begin();
    }

    public function commitTransaction() {

        return $this->_dbh->commit();
    }

    public function rollBackTransaction() {

        return $this->_dbh->rollBack();
    }

    protected function _rewriteSql($sql) {

        if (strpos(strtolower($sql), ' join ')) {
            return $sql;
        } else {
            $pattern = '/((?:select.*?from|insert into|delete from|update|replace into|truncate table|describe|alter table)\s+)`?(\w+)`?/i';

            return preg_replace($pattern, '\1' . $this->_table, $sql);
        }
    }
}
