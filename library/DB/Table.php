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
class DB_Table {

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
    private $_table;

    /**
     * 构建数据操作实例
     *
     * - 本程序负责分表时：
     *   $db = new DB_Table('table_name');
     *
     * @param string $table
     * @param integer $shardKey 分库标志
     *  + key 数据库字段
     *  + value 与字段对应的值
     */
    public function __construct($table, $shardKey = array()) {

        $conf = self::shardTable($table, $shardKey);

        require APP_ROOT . "config/db.php";

        if (strpos($table, '.')) {
            $db = substr($table, 0, strpos($table, '.'));
            $table = substr($table, strpos($table, '.') + 1);
        } else {
            $db = 'db';
        }
        //var_dump($conf[$db]);

        $this->_table = $_config[$db]['table_pre'] . $table;

        $this->_dbh = DB::connect(
            $_config[$db]['dsn'],
            $_config[$db]['user'],
            $_config[$db]['password'],
            $_config[$db]['charset'],
            $_config[$db]['failover'],
            $_config[$db]['persistent'],
            $_config[$db]['timeout']
        );

        //var_dump($this->_dbh);
        //exit;
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
     * @param array $shardKey   切分关键字
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
     * @param array $param
     * @param array columns 列
     * @return array || null
     */
    public function find($conditions = null, $params = array(), $columns = array('*')) {

        $columns = implode(',', $columns);
        $sql = "SELECT $columns FROM $this->_table";

        if ($conditions) {
            $sql .= ' WHERE ' . $conditions;
        }

        try {
            $data = $this->_dbh->fetchRow($sql, $params);
        } catch (PDOException $e) {
            throw new DB_Exception($e);
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
     * @param string $order
     *   fieldName1 => [ASC|DESC]
     *   fieldName2 => [ASC|DESC]
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
        //return $sql;

        try {
            $rows = $this->_dbh->fetchAll($sql, $params);
        } catch (PDOException $e){
            throw new DB_Exception($e);
        }
        return $rows;
    }

    /**
     * findBySql
     *
     * @param  string $sql
     * @param  array $params
     * @return array
     */
    public function findBySql($sql, $params = array()) {

        try {
            $sql = $this->_rewriteSql($sql);
            $data = $this->_dbh->fetchAll($sql, $params);
        } catch (PDOException $e){
            throw new DB_Exception($e);
        }
        return $data;
    }

    /**
     * 删除记录
     *
     * @param string $conditions
     * @param array $param
     * @return int affect rows
     */
    public function remove($conditions, $params = array()) {

        $sql = "DELETE FROM $this->_table WHERE $conditions ";
        try {
            return $this->_dbh->exec($sql, $params);
        } catch (PDOException $e){
            throw new DB_Exception($e);
        }
    }

    /**
     * 插入一条记录 或更新表记录
     *
     * @param array $data
     * @param string $conditions
     * @param array $param
     * @return bool || int
     */
    public function save($data, $conditions = NULL, $params = array()) {

        $tempParams = array();
        $set = array();
        foreach ($data as $k => $v) {
            array_push($set, $k . '= ?');
            array_push($tempParams, $v);
        }

        if ($conditions) {
            // 更新
            $sql = "UPDATE $this->_table SET " . join(',',$set) . " WHERE $conditions";
            $params = array_merge($tempParams,$params);
        } else {
            // 插入
            $sql = "INSERT INTO  $this->_table SET ". join(',', $set);
            $params = $tempParams;
        }

        // 捕获PDOException后 抛出DB_Exception
        try{
            return $this->_dbh->exec($sql, $params);
        } catch (PDOException $e){
            throw new DB_Exception($e);
        }
    }

    /**
     * replace
     * 根据主键替换或保存
     *
     * @param array $data
     * @return mixed
     */
    public function replace($data) {

        $tempParams = array();
        $set = array();
        foreach ($data as $k => $v) {
            array_push($set, $k . '= ?');
            array_push($tempParams, $v);
        }

        $sql = "REPLACE INTO $this->_table SET ". join(',', $set);

        try{
            return $this->_dbh->exec($sql, $tempParams);
        } catch (PDOException $e){
            throw new DB_Exception($e);
        }
    }

    /**
     * 获取刚刚写入记录的ID
     *
     * @return int
     */
    public function lastInsertId() {

        try{
            return $this->_dbh->lastInsertId();
        } catch (PDOException $e){
            throw new DB_Exception($e);
        }
    }

    /**
     * 一次插入多条记录
     *
     * @param  $data
     */
    public function multiInsert($data) {

        $count = count($data);
        $getKeys = array_keys($data[0]);
        $countKeys = count($getKeys);
        $columns = implode(',', $getKeys);

        // 构造问号表达式
        $tmpArr = array();
        for($i=0;$i< $countKeys;$i++) {
            $tmpArr[] = '?';
        }
        $tmpStr = implode(',', $tmpArr);
        $tmpStr = '(' . $tmpStr. ')';
        $tmpArr2 = array();
        $mergeArr = array();
        for ($i=0;$i < $count; $i++){
            $tmpArr2[] = $tmpStr;
            $mergeArr = array_merge($mergeArr, array_values($data[$i]));
        }

        $tmpStr2 = implode(',', $tmpArr2);
        $conditions = "INSERT INTO $this->_table ($columns) VALUES $tmpStr2";
        try {
            return  $this->_dbh->exec($conditions,$mergeArr);
        } catch (PDOException  $e) {
            throw new DB_Exception($e);
        }
    }

    /**
     * 数据表列名到缓存列名的转换
     *
     * @param array $array
     */
    public function keyMap($array){

        $outArray = array();
        foreach ($array as $key => $value) {
            $keyArr = explode('_',$key);
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
     * @return integer
     */
    public function count($conditions = NULL, $params = array()) {

        $sql = 'SELECT COUNT(*) FROM ' . $this->_table ;
        try {
            if ($conditions) {
                $sql .= ' WHERE ' . $conditions;
            }
            return  $this->_dbh->fetchOne($sql, $params);

        } catch (PDOException  $e) {
            throw new DB_Exception($e);

        }
    }

    /**
     * exec
     * 执行sql语句
     *
     * @param  string $sql
     * @param  string $params
     * @return void
     */
    public function exec($sql, $params = array()) {

        global $_G;

        try {
            $sql = $this->_rewriteSql($sql);

            if ($_G['debug']) {
                $_G['debug_info']['sql'][] = $sql;
            }

            $result = $this->_dbh->exec($sql, $params);
        } catch (PDOException $e) {
            throw new DB_Exception($e);
        }

        return $result;
    }

    public function incr($field, $conditions = null, $params = array(),  $unit = 1) {

        $sql = 'UPDATE ' . $this->_table . " SET `$field` = `$field` + $unit";
        if ($conditions) {
            $sql .= ' WHERE ' . $conditions;
        }
        try {
            $result = $this->_dbh->exec($sql, $params);
        } catch (PDOException $e) {
            throw new DB_Exception($e->getMessage, $e->getCode());
        }
        return $result;
    }

    public function decr($field , $conditions = null, $params = array(), $unit = 1) {

        $sql = 'UPDATE ' . $this->_table . " SET $field = IF($field > $unit,  $field - $unit, 0)";
        if ($conditions) {
            $sql .= ' WHERE ' . $conditions;
        }

        try {
            $result = $this->_dbh->exec($sql, $params);
        } catch (PDOException $e) {
            throw new DB_Exception($e);
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
            throw new DB_Exception($e);
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

        $pattern = '/((?:select.*?from|insert into|delete from|update|replace into|truncate table|describe|alter table)\s+)`?(\w+)`?/i';
        return preg_replace($pattern, '\1' . $this->_table, $sql);
    }

}
