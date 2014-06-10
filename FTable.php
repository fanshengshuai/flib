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
    private static $_connects = array();
    private $_dbh;
    // 字段信息
    protected $fields = array();
    // 数据信息
    protected $data = array();
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

    public function where($conditions = null) {
        $params = array();
        if (is_string($conditions)) {
            $this->options['where'] = $conditions;
        } elseif (is_array($conditions)) {
            $where = '';
            foreach ($conditions as $_k => $_v) {

                if (is_array($_v)) {

                    if ($_v[0] == 'in') {
                        $where[] .= "{$_v[0]} in ( ? )";
                        $params[] = $_v[1];
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

    public function order($order) {
        if (is_array($order)) {
            $orderClause = '';
            foreach ($order as $field => $orderBy) {
                $orderClause .= $field . ' ' . $orderBy . ',';
            }
            $this->options['order_by'] = ' ORDER BY ' . rtrim($orderClause, ',');
        } else if ($order && is_string($order)) {
            $this->options['order_by'] = ' ORDER BY ' . $order;
        }

        return $this;
    }

    /**
     * 获取一条记录
     *
     * @throws FDB_Exception
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

        $sql = $this->buildSql();

        try {
            $stmt = $this->_dbh->prepare($sql);
            $stmt->execute($this->options['params']);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new FDB_Exception($e);
        }

        return $row;
    }

    public function select() {

        $sql = $this->buildSql();

//var_dump($this->options);
        try {
            $stmt = $this->_dbh->prepare($sql);
            $stmt->execute($this->options['params']);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new FDB_Exception($e);
        }

        return $rows;
    }

    public function count() {
        $this->options['fields'] = array("COUNT(*) as count");
        $result = $this->find();

        return $result['count'];
    }

    private function buildSql() {
        global $_F;

        $columns = implode(',', $this->options['fields']);

//        var_dump($this->options['fields'], $columns);

        if (!$columns) {
            $columns = '*';
        }

        $sql = "SELECT $columns FROM $this->_table";

        if ($this->options['where']) {
            $sql .= ' WHERE ' . $this->options['where'];
        }

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
            throw new FDB_Exception("连接数据库失败：" . $e->getMessage());
        }

        $this->_connects[$dsn] = $this->_dbh;

        return $this->_connects[$dsn];
    }
}