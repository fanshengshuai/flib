<?php

/**
 *
 * 作者: 范圣帅(fanshengshuai@gmail.com)
 * 时间: 2012-07-02 01:22:18
 *
 * vim: set expandtab sw=4 ts=4 sts=4
 * $Id: DB.php 273 2012-08-22 10:37:34Z fanshengshuai $
 */

class DB {

    private static $_conns = array();

    private $_dbh;

    /**
     * db 构造函数
     *
     * @param string $dsn
     * @param string $user
     * @param string $password
     * @param string $charset
     * @param string $failover
     * @param boolean $persistent
     * @param integer $timeout
     */
    private function __construct($dsn, $user, $password, $charset, $failover = '', $persistent = false, $timeout = 0) {

        $attr = array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                      PDO::ATTR_PERSISTENT => $persistent);
        if (0 < $timeout) {
            $attr[PDO::ATTR_TIMEOUT] = $timeout;
        }

        try {
            $this->_dbh = new PDO($dsn, $user, $password, $attr);
            $this->_dbh->exec("SET NAMES '" . $charset . "'");
        } catch (PDOException $e){

            if ($failover) {
                try {
                    $this->_dbh = new PDO($failover, $user, $password, $attr);
                    $this->_dbh->exec("SET NAMES '" . $charset . "'");
                } catch (PDOException $e){
                    throw new DB_Exception("can't connect to the server because:" . $e->getMessage());
                }
            } else {

                throw new DB_Exception("can't connect to the server because:" . $e->getMessage());
            }
        }
    }

public function table($t) {
return $t;
}

    /**
     * 获取数据库连接类
     *
     * @param string $dsn
     * @param string $user
     * @param string $password
     * @param string $charset
     * @param string $failover
     * @param boolean $persistent
     * @param integer $timeout
     * @return DB 实例
     */
    public static function connect($dsn, $user, $password, $charset, $failover = '', $persistent = false, $timeout = 0) {

        if (!array_key_exists($dsn, self::$_conns)){
            self::$_conns[$dsn] = new DB($dsn, $user, $password, $charset, $failover, $persistent, $timeout);
        }

        return self::$_conns[$dsn];
    }

    /**
     * 开启事物
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
     */
    public function fetchRow($query, $params = array()) {

        $stmt = $this->_dbh->prepare($query);
        $stmt->execute($params);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row;

    }

    /**
     * 取得所有的记录
     *
     * @param sql string $query
     * @param array $params
     * @return array
     */
    public function fetchAll($query, $params = array()) {
        global $_G;

        if ($_G['debug']) {
            $_G['debug_info']['sql'][] = $query;
        }

        $stmt = $this->_dbh->prepare($query);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $rows;
    }

    /**
     * 获取记录的第一行第一列
     *
     * @param string sql $query
     * @param array $params
     */
    public function fetchOne($query, $params = array()) {

        $stmt = $this->_dbh->prepare($query);
        $result = $stmt->execute($params);
        if ($result) {
            $row = $stmt->fetchColumn();
        }
        return $row ;
    }

    /**
     * 执行sql 语句
     *
     * @param sqlstring $query
     * @param array $params
     * @return 更新的记录的条数
     */

    public function exec($query, $params = array()) {
        global $_G;

        if ($_G['debug']) {
            $_G['debug_info']['sql'][] = $query;
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
     * @param string $dsn
     */
    public function close($dsn = null) {

        if ($dsn) {
            self::$_conns[$dsn] = NULL;
        } else {
            $this->_dbh = NULL;
        }
    }


    public static function query($sql) {
        $db_config = Config::get('db.default');

        $db = new DB($db_config['dsn'], $db_config['user'], $db_config['password'], $db_config['charset']);
        return $db->exec($sql);
    }

    public static function fetch($sql) {
        $db_config = Config::get('db.default');

        $db = new DB($db_config['dsn'], $db_config['user'], $db_config['password'], $db_config['charset']);
        return $db->fetchAll($sql);
    }
}
