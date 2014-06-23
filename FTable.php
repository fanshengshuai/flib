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
class FTable {
    private $_connects = array();
    private $_dbh;
    // 字段信息
    protected $fields = array();
    // 数据信息
    protected $data = array();
    protected $limit = 0;
    // 查询表达式参数
    protected $options = array();
    // 查询表达式
    protected $selectSql = 'SELECT%DISTINCT% %FIELD% FROM %TABLE%%JOIN%%WHERE%%GROUP%%HAVING%%ORDER%%LIMIT% %UNION%%COMMENT%';


    /**
     * 构建数据操作实例
     *
     * @param string $table
     *
     * @internal param array
     */
    public function __construct($table) {
        global $_F;

        $this->_dbh = $this->connect();
        $this->_table = $_F['db']['config']['table_pre'] . $table;
    }

    public function fields($fields) {
        $this->options['fields'] = $fields;
        return $this;
    }

    /**
     * where 查询条件
     *
     * @param null $conditions
     *
     * @return $this
     */
    public function where($conditions = null) {
        $params = array();
        if (is_string($conditions)) {
            $this->options['where'] = $conditions;
        } elseif (is_array($conditions)) {
            $where = '';
            foreach ($conditions as $_k => $_v) {

                if (is_array($_v)) {

                    if (strtolower($_v[0]) == 'in') {
                        $where[] .= "{$_k} {$_v[0]} ( " . join(',', $_v[1]) . " )";
                    }

                    continue;
                }


                if (strpos($_k, ':')) {
                    $_k = substr($_k, 0, strpos($_k, ':'));
                }

                $__v = strtolower($_v);
                if (strpos($__v, 'like ') !== false) {
                    $where[] .= "{$_k} like ?";
                    $params[] = "%" . trim(str_replace(array('like', 'LIKE'), '', $_v)) . "%";
                } elseif (
                    strpos($_v, 'gt ') !== false || strpos($_v, 'lt ') !== false ||
                    strpos($_v, 'gte ') !== false || strpos($_v, 'lte ') !== false
                ) {
                    $opt = substr($_v, 0, strpos($_v, ' '));
                    $param = trim(substr($_v, strpos($_v, ' ')));
                    $opt = str_replace(array('gte', 'lte', 'gt', 'lt'), array('>= ', '<= ', '> ', '< '), $opt);

                    $where[] .= "{$_k} $opt ?";
                    $params[] = $param;

                } else {
                    $where[] .= "{$_k} = ?";
                    $params[] = $_v;
                }
            }


            $this->options['where'] = join(' and ', $where);
            $this->options['params'] = $params;
        }

        return $this;
    }

    /**
     * 排序
     *
     * @param $order
     *
     * @return $this
     */
    public function order($order) {
        if (is_array($order)) {
            $orderClause = '';
            foreach ($order as $field => $orderBy) {
                $orderClause .= $field . ' ' . $orderBy . ',';
            }
            $this->options['order_by'] = rtrim($orderClause, ',');
        } elseif ($order && is_string($order)) {
            $this->options['order_by'] = $order;
        }

        return $this;
    }

    /**
     * 获取一条记录
     *
     * @throws Exception
     * @internal param string $conditions
     * @internal param array $columns
     *
     * @internal param array $params
     *
     * @internal param array $param
     * @internal param \columns $array 列
     *
     * @return array || null
     */
    public function find() {
        global $_F;

        $this->limit(1);
        $sql = $this->buildSql();

        try {
            $stmt = $this->_dbh->prepare($sql);
            $stmt->execute($this->options['params']);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception($e);
        }

        return $row;
    }

    /**
     * 查询操作
     *
     * @return mixed
     * @throws Exception
     */
    public function select() {

        $sql = $this->buildSql();

//var_dump($this->options);
        try {
            $stmt = $this->_dbh->prepare($sql);
            $stmt->execute($this->options['params']);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception($e);
        }

        return $rows;
    }

    /**
     * 统计记录数目
     *
     * @return mixed
     */
    public function count() {
        $this->options['fields'] = array("COUNT(*) as count");
        $result = $this->find();

        return $result['count'];
    }

    private function buildSql() {
        global $_F;

        $columns = null;
        if ($this->options['fields']) {
            $columns = implode(',', $this->options['fields']);
        }

//        var_dump($this->options['fields'], $columns);

        if (!$columns) {
            $columns = '*';
        }

        $sql = "SELECT $columns FROM $this->_table";

        if ($this->options['where']) {
            $sql .= ' WHERE ' . $this->options['where'];
        }

        if ($this->options['order_by']) {
            $sql .= ' ORDER BY ' . $this->options['order_by'];
        }

        if ($this->options['page'] > 0) {

            if (!$this->options['limit']) {
                throw new Exception('limit cannot be 0 when use page function, please use limit function to set it.');
            }

            $sql .= ' limit ' . (($this->options['page'] - 1) * $this->options['limit']) . ',' . $this->options['limit'];

        } elseif ($this->options['limit'] > 0) {
            $sql .= ' limit ' . $this->options['limit'];
        }

//        echo $sql;

        if ($_F['debug']) {
            $_F['debug_info']['sql'][] = array('sql' => $sql, 'params' => $this->options['params']);
        }

        return $sql;
    }

    public function connect() {
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

        if (array_key_exists($dsn, $this->_connects)) {
            $this->_connects[$dsn];
        }

        $attr = array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_PERSISTENT => false);
        $attr[PDO::ATTR_TIMEOUT] = 5;

        try {
            $this->_dbh = new PDO($_F['db']['config']['dsn'], $_F['db']['config']['user'], $_F['db']['config']['password'], $attr);
            $this->_dbh->exec("SET NAMES '" . $_F['db']['config']['charset'] . "'");
        } catch (PDOException $e) {
            throw new Exception("连接数据库失败：" . $e->getMessage());
        }

        $this->_connects[$dsn] = $this->_dbh;

        return $this->_connects[$dsn];
    }

    /**
     * 分页：页数
     *
     * @param $page
     *
     * @return $this
     */
    public function page($page) {
        $this->options['page'] = $page;
        return $this;
    }

    /**
     * 要取出条数
     *
     * @param $limit
     *
     * @return $this
     */
    public function limit($limit) {
        $this->options['limit'] = $limit;
        return $this;
    }

    /**
     * 插入一条记录 或更新表记录
     *
     * @param array $data
     * @param string $conditions
     * @param array $params
     *
     * @throws Exception
     * @internal param array $param
     *
     * @return bool || int
     */
    public function save($data, $conditions = null, $params = array()) {
        global $_F;

        $tempParams = array();
        $set = array();
        foreach ($data as $k => $v) {
            array_push($set, '`' . $k . '`' . '= ?');
            array_push($tempParams, $v);
        }

        if ($conditions) {
            // 更新
            $sql = "UPDATE `{$this->_table}` SET " . join(', ', $set) . " WHERE $conditions";
            $params = array_merge($tempParams, $params);
        } else {
            // 插入
            $sql = "INSERT INTO `{$this->_table}` SET " . join(', ', $set);
            $params = $tempParams;
        }

        if ($_F['debug']) {
            $_F['debug_info']['sql'][] = array('sql' => $sql, 'params' => $this->options['params']);
        }

        // 捕获PDOException后 抛出Exception
        try {

            $stmt = $this->_dbh->prepare($sql);
            $stmt->execute($params);

            return $this->_dbh->lastInsertId();
        } catch (PDOException $e) {
            throw new Exception($e);
        }
    }

    /**
     * 获取刚刚写入记录的ID
     *
     * @throws Exception
     * @return int
     */
    public function lastInsertId() {

        try {
            return $this->_dbh->lastInsertId();
        } catch (PDOException $e) {
            throw new Exception($e);
        }
    }

    /**
     * 自增
     *
     * @param $field
     * @param $conditions
     * @param array $params
     * @param int $unit
     *
     * @throws Exception
     * @throws Exception
     * @return mixed
     */
    public function increase($field, $conditions, $params = array(), $unit = 1) {

        if (!$conditions) {
            throw new Exception('FTable incr function need condition');
        }

        $sql = 'UPDATE ' . $this->_table . " SET `$field` = `$field` + $unit";
        $sql .= ' WHERE ' . $conditions;

        try {
            $stmt = $this->_dbh->prepare($sql);
            $result = $stmt->execute($params);
        } catch (PDOException $e) {
            throw new Exception($e);
        }

        return $result;
    }

    /**
     * 自减
     *
     * @param $field
     * @param $conditions
     * @param array $params
     * @param int $unit
     *
     * @return mixed
     * @throws Exception
     * @throws Exception
     */
    public function decrease($field, $conditions, $params = array(), $unit = 1) {

        if (!$conditions) {
            throw new Exception('FTable decr function need condition');
        }

        $sql = 'UPDATE ' . $this->_table . " SET $field = IF($field > $unit,  $field - $unit, 0)";
        $sql .= ' WHERE ' . $conditions;

        try {
            $stmt = $this->_dbh->prepare($sql);
            $result = $stmt->execute($params);
        } catch (PDOException $e) {
            throw new Exception($e);
        }

        return $result;
    }

    /**
     * @return bool
     * @throws Exception
     */
    public function truncate() {
        global $_F;

        if (!$_F['truncate_confirm']) {
            return false;
        }

        $sql = "TRUNCATE  $this->_table  ";
        //echo "$sql \n";
        $params = array();
        try {
            $stmt = $this->_dbh->prepare($sql);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            throw new Exception($e);
        }
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
}