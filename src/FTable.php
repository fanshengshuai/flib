<?php

/**
 *
 * 作者: 范圣帅(fanshengshuai@gmail.com)
 *
 * 创建: 2012-08-08 10:57:22
 * vim: set expandtab sw=4 ts=4 sts=4 *
 *
 * $Id: FTable.php 764 2015-04-14 15:09:06Z fanshengshuai $
 */
class FTable {

    /**
     * @var string
     */
    private $_table = null;

    /**
     * @param string $table
     */
    public function setTable($table) {
        $this->table_raw = $table;
        $this->_table = $table;
    }

    /**
     * @return string
     */
    public function getTable() {
        return $this->table_raw;
    }

    /**
     * @param array $pagerOptions
     */
    public function setPagerOptions($pagerOptions) {
        $this->pagerOptions = $pagerOptions;
    }

    /**
     * @return array
     */
    public function getPagerOptions() {
        return $this->pagerOptions;
    }

    /**
     * @param array $options
     */
    public function setOptions($options) {
        $this->options = $options;
    }

    /**
     * @return array
     */
    public function getOptions() {
        return $this->options;
    }

    /**
     * @var PDO
     */
    private $_dbh;

    /**
     * @var array
     */
    protected $options = array();

    /**
     * 分页配置项
     * @var array
     */
    protected $pagerOptions = array();

    /**
     * @var string
     */
    protected $table_info = null;

    // 查询表达式
    /**
     * @var string
     */
    protected $selectSql = 'SELECT%DISTINCT% %FIELD% FROM %TABLE%%JOIN%%WHERE%%GROUP%%HAVING%%ORDER%%LIMIT% %UNION%%COMMENT%';

    /**
     * 构建数据操作实例
     *
     * @param string $table
     * @param null $as
     * @param null $db_conf
     */
    public function __construct($table, $as = null, $db_conf = null) {
        $this->table_raw = $table;
        $this->config = FDB::getConfig();
        $this->table = $this->_table = "`{$this->config['table_pre']}{$table}`";

        if ($as) {
            $this->_table = $this->_table . " as {$as}";
        }

        if ($db_conf) {
            $this->db_conf = $db_conf;
        }
    }

    public function connect($rw_type = 'w') {
        if ($this->db_conf) {
            $rw_type = $this->db_conf;
        }
        $this->_dbh = FDB::connect($rw_type);
        return $this->_dbh;
    }

    /**
     * 设置使用缓存
     *
     * @param int $cacheTime
     *
     * @return $this
     * @internal param bool $useCache
     */
    public function cache($cacheTime = 3600) {
        $this->options['useCache'] = true;
        $this->options['cacheTime'] = $cacheTime;

        return $this;
    }

    /**
     * @param $fields
     *
     * @return $this
     */
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
     *
     * @example:
     *
     * $where = "uid > 10";
     * or
     * $where = array(
     *      'uid' => array('in' => '1, 2, 4'),
     *      'uid:1' => '1',
     *      'uid:2' => 'like \'%xxx%\'',  // like
     *      'uid:3' => 'gt 10',           // 大于 10
     *      'uid:4' => 'gte 10',          // 大于等于 10
     *      'uid:5' => 'lt 10',           // 小于 10
     *      'uid:6' => 'lte 10',          // 小于等于 10
     * );
     */
    public function where($conditions = null) {
        $params = array();
        if (is_string($conditions)) {
            $this->options['where'] = $conditions;
            $this->options['params'] = array();

        } elseif (is_array($conditions)) {
            $where = array();
            foreach ($conditions as $_k => $_v) {

                $tableFiled = $_k;


                // IMPORTANT 一定要全等于！！！
                if ($_v === "__NO_VALUE__") {
                    $where[] = "$_k";
                    continue;
                }

                if (is_null($_v)) {
                    $where[] = "$_k IS NULL";
                    continue;
                } elseif (is_array($_v)) {

                    foreach ($_v as $where_item_sub_key => $where_item_sub_value) {


//                        if (strtoupper($where_item_sub_key) == 'BETWEEN') {
//                            $where[] .= "{$tableFiled} = ?";
//                            $params[] = "BETWEEN $where_item_sub_value";
//                            continue;
//                        }

                        // array("in", '(1, 2, 3)'); 内容不是数组的跳过
                        // array(array('in' => '(1, 2)'))；只解析这样的
                        if (is_numeric($where_item_sub_key)) {
                            continue;
                        }

                        $where_item_sub_key = str_replace(array('gte', 'lte', 'gt', 'lt', 'neq', 'eq'),
                            array('>= ', '<= ', '> ', '< ', '<>', '='), $where_item_sub_key);

                        if (strpos(strtolower($where_item_sub_key), 'in') !== false) {
                            if (is_array($where_item_sub_value)) {
                                $where_item_sub_value = join(',', $where_item_sub_value);
                            }
                            $where[] .= "$tableFiled {$where_item_sub_key} ( " . $where_item_sub_value . " )";
                        } elseif (strpos(strtolower($where_item_sub_key), 'like') !== false) {
                            $where[] .= "$tableFiled {$where_item_sub_key} ?";

                            if (strpos($where_item_sub_value, '%') === false) {
                                $params[] = "%" . trim(str_replace(array('like', 'LIKE'), '', $where_item_sub_value)) . "%";
                            } else {
                                $params[] = trim(str_replace(array('like', 'LIKE'), '', $where_item_sub_value));
                            }

                        } elseif (strpos(strtolower($where_item_sub_key), 'between') !== false) {
                            if (is_array($where_item_sub_value)) {
                                $where_item_sub_value = join(',', $where_item_sub_value);
                            }
                            $where[] .= "$tableFiled {$where_item_sub_key}  " . $where_item_sub_value . " ";
                        } else {
                            $where[] .= "$tableFiled {$where_item_sub_key} ?";
                            $params[] = $where_item_sub_value;
                        }

                        // 解析完后删除条件，剩下的条件由下面代码解析
                        unset($conditions[$_k][$where_item_sub_key]);
                    }

                    continue;
                }

                $__v = strtolower($_v);
                if (strpos($__v, 'like ') !== false) {
                    $where[] .= "{$_k} like ?";
                    $params[] = "%" . trim(str_replace(array('like', 'LIKE'), '', $_v)) . "%";
                } elseif (
                    strpos($_v, 'gt ') !== false || strpos($_v, 'lt ') !== false ||
                    strpos($_v, 'gte ') !== false || strpos($_v, 'lte ') !== false || strpos($_v, 'ni ') !== false
                ) {
                    $opt = substr($_v, 0, strpos($_v, ' '));
                    $param = trim(substr($_v, strpos($_v, ' ')));
                    $opt = str_replace(array('gte', 'lte', 'gt', 'lt', 'ni'), array('>= ', '<= ', '> ', '< ', 'not in '), $opt);

                    $where[] .= "{$tableFiled} $opt ?";
                    $params[] = $param;

                } else {
                    $where[] .= "{$tableFiled} = ?";
                    $params[] = $_v;
                }
            }

            $this->options['where'] = join(' AND ', $where);
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
     * 分组
     *
     * @param $group
     *
     * @return $this
     */
    public function group($group) {
        $this->options['group_by'] = $group;
        return $this;
    }

    /**
     * 获取一条记录
     *
     * @param null $priValue mix 主键数值
     *
     * @throws Exception
     *
     * @return array || null
     */
    public function find($priValue = null) {
        global $_F;

        $retData = null;

        // 按主键查询
        if ($priValue) {
            $this->table_info = $this->getTableInfo();
            if (!$this->table_info['pri']) throw new Exception("该表没有设置主键，无法通过 find 参数查询。");
            $this->where($this->table_info['pri'] . '=\'' . $priValue . '\'');
        }


        if ($this->options['group_by']) {
            $sql = 'select count(*) as count from (' . $this->buildSql() . ')A';
        } else {
            $this->limit(1);
            $sql = $this->buildSql();
        }


//         echo($sql . '<br>');

        // 缓存处理
        $cacheKey = "SQL-RESULT-{$this->_table}-{$sql}-" . ($this->options['params'] ? join('-', $this->options['params']) : '');
        if ($this->options['useCache']) {

            $cacheValue = FCache::get($cacheKey);
            if ($cacheValue) {
                $this->reset();
                return $cacheValue;
            }
        }

        if ($_F['debug'])
            FDebug::logSql($sql, $this->options['params']);

        try {
            $this->connect('r');
            $stmt = $this->_dbh->prepare($sql);
            $stmt->execute($this->options['params']);
            $retData = $stmt->fetch(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            $_F['current_sql'] = $sql;
            throw $e;
        }

        // 缓存处理
        if ($this->options['useCache'])
            FCache::set($cacheKey, $retData, $this->options['cacheTime']);

        $this->reset();
        return $retData;
    }

    /**
     * 查询操作
     *
     * @return mixed
     * @throws Exception
     */
    public function select() {
        global $_F;

        $sql = $this->buildSql();

        // 处理分页参数，放在 缓存处理 之前
        if ($this->options['page']) {
            $this->setPagerOptions(array('where' => $this->options['where'],
                'params' => $this->options['params'],
                'page' => $this->options['page'],
                'limit' => $this->options['limit'],
                'fields' => $this->options['fields'],
                'group_by' => $this->options['group_by']));
        }

        // 缓存处理
        $cacheKey = "SQL-RESULT-{$this->_table}-{$sql}-" . ($this->options['params'] ? join('-', $this->options['params']) : '');
        if ($this->options['useCache']) {

            $cacheValue = FCache::get($cacheKey);

            if ($cacheValue) {
                // 放在 return 之前，不要忘记 cache 模式也要重置
                $this->reset();
                return $cacheValue;
            }
        }

        if ($_F['debug'])
            FDebug::logSql($sql, $this->options['params']);

        try {
            $this->connect('r');
            $stmt = $this->_dbh->prepare($sql);
            $stmt->execute($this->options['params']);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            throw new Exception($e);
        }

        // 缓存处理
        if ($this->options['useCache'])
            FCache::set($cacheKey, $rows, $this->options['cacheTime']);

        // 放在 return 之前，不要忘记 cache 模式也要重置
        $this->reset();
        return $rows;
    }

    /**
     * 统计记录数目
     *
     * @return mixed
     */
    public function count() {
        $this->options['page'] = 1;
        $this->options['limit'] = 0;

        if (!$this->options['group_by']) {
            if ($this->options['fields']) {
                $this->options['fields'] = array("COUNT(*) as count"); //修改leftJoin u.*报错 edit:wyr
            } else {
                $this->options['fields'] = array("COUNT(*) as count");
            }

        }

        $result = $this->find();


        return $result['count'];
    }

    /**
     * 汇总某个字段
     * @param $field
     * @return int
     * @throws Exception
     */
    public function sum($field) {
        $this->fields("sum({$field}) as sum");
        $result = $this->find();

        return intval($result['sum']);
    }

    /**
     * @return string
     * @throws Exception
     */
    private function buildSql() {

        $columns = null;
        if ($this->options['fields'] && is_array($this->options['fields'])) {
            $columns = implode(',', $this->options['fields']);
        } elseif (is_string($this->options['fields'])) {
            $columns = $this->options['fields'];
        } else {
            $columns = '*';
        }

        $sql = "SELECT $columns FROM {$this->_table}";

        if ($this->options['where']) {
            $sql .= ' WHERE ' . $this->options['where'];
        }

        if ($this->options['group_by'] && is_array($this->options['group_by'])) {
            $sql .= ' GROUP BY ' . implode(',', $this->options['group_by']);
        }

        if ($this->options['order_by']) {
            $sql .= ' ORDER BY ' . $this->options['order_by'];
        }

        if ($this->options['limit']) {
            if ($this->options['page'] > 0) {

//            if (!$this->options['group_by']) {
                if (!$this->options['limit']) {
                    throw new Exception('limit cannot be 0 when use page function, please use limit function to set it.');
                }

                $sql .= ' limit ' . (($this->options['page'] - 1) * $this->options['limit']) . ',' . $this->options['limit'];
//            }

            } elseif ($this->options['limit'] > 0) {
                $sql .= ' limit ' . $this->options['limit'];
            }
        }

        return $sql;
    }


    /**
     * 分页：页数
     *
     * @param $page
     *
     * @return $this
     */
    public function page($page) {
        $this->options['page'] = max(1, $page);
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
     *
     * @throws Exception
     * @internal     param string $conditions
     * @internal     param array $params
     *
     * @internal     param array $param
     *
     * @return bool || int
     */
    public function save($data) {
        global $_F;

        $tempParams = array();
        $set = array();
        foreach ($data as $k => $v) {
            array_push($set, '`' . $k . '`' . '= ?');
            array_push($tempParams, $v);
        }

        if ($this->options['where']) {
            // 更新
            $sql = "UPDATE {$this->config['table_pre']}{$this->table_raw} SET " . join(', ', $set) . " WHERE {$this->options['where']}";
            $params = array_merge($tempParams, $this->options['params']);
        } else {
            // 插入
            $sql = "INSERT INTO {$this->config['table_pre']}{$this->table_raw} SET " . join(', ', $set);
            $params = $tempParams;
        }

        if ($_F['debug']) {
            FDebug::logSql($sql, $params);
        }

        // 捕获PDOException后 抛出Exception
        try {
            $this->connect('w');

            $stmt = $this->_dbh->prepare($sql);
            $stmt->execute($params);

            $this->reset();

            return $stmt->rowCount();
        } catch (PDOException $e) {
            $_F['current_sql'] = $sql;
            throw $e;
        }
    }

    /**
     * 增加一条数据
     *
     * @param $data
     *
     * @return bool
     */
    public function insert($data) {
        $this->reset();

        $tableInfo = $this->getTableInfo();

        if (isset($tableInfo['fields']['create_time']) && !$data['create_time']) {
            $data['create_time'] = date('Y-m-d H:i:s');
        }

        if (isset($tableInfo['fields']['status']) && !isset($data['status'])) {
            $data['status'] = 1;
        }

        $this->save($data);

        return $this->_dbh->lastInsertId();
    }

    /**
     * 更新一条数据
     *
     * @param $data
     * @param $where
     *
     * @throws Exception
     * @return bool
     */
    public function update($data, $where = null) {

        if ($where) {
            $this->reset();
            $this->where($where);
        }

        if (!$this->options['where']) {
            throw new Exception("FDB update need condition.");
        }

        $tableInfo = $this->getTableInfo();

        if (isset($tableInfo['fields']['update_time']) && !$data['update_time']) {
            $data['update_time'] = date('Y-m-d H:i:s');
        }

        return $this->save($data);
    }


    /**
     * 更新一条数据 不包含update_time字段的更新 edit:wyr
     *
     * @param $data
     * @param $where
     *
     * @throws Exception
     * @return bool
     */
    public function update1($data, $where = null) {

        if ($where) {
            $this->reset();
            $this->where($where);
        }

        if (!$this->options['where']) {
            throw new Exception("FDB update need condition.");
        }

        return $this->save($data);
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
     * 删除一条数据，需要通过where方法设置条件，参数为true为真删除
     *
     * @param bool $reallyMode true 为真删除，false 为假删除，默认为假删除
     *
     * @return bool
     * @throws Exception
     */
    public function remove($reallyMode = false) {
        global $_F;

        // 检查条件
        if (!$this->options['where'])
            throw new Exception("FTable REMOVE function need where params. 我认为没有条件的删除是很危险的。");

        // 假删除
        if (!$reallyMode) {
            return $this->save(array('status' => 2, 'remove_time' => date('Y-m-d H:i:s')));
        }

        $sql = "DELETE from $this->_table WHERE {$this->options['where']}";

        if ($_F['debug'])
            FDebug::logSql($sql, $this->options['params']);

        try {
            $this->connect('w');

            $stmt = $this->_dbh->prepare($sql);
            $result = $stmt->execute($this->options['params']);

            $this->reset();
        } catch (PDOException $e) {
            throw new Exception($e);
        }

        return $result;
    }

    /**
     * 自增
     *
     * @param     $field
     * @param int $unit
     *
     * @throws Exception
     * @internal param $conditions
     * @internal param array $params
     * @return mixed
     */
    public function increase($field, $unit = 1) {
        global $_F;

        if (!$this->options['where']) {
            throw new Exception('FTable increase function need condition');
        }

        $sql = "UPDATE {$this->_table} SET `$field` = `$field` + $unit";
        $sql .= ' WHERE ' . $this->options['where'];

        if ($_F['debug']) {
            FDebug::logSql($sql, $this->options['params']);
        }

        try {
            $this->connect('w');

            $stmt = $this->_dbh->prepare($sql);
            $result = $stmt->execute($this->options['params']);
        } catch (PDOException $e) {
            throw $e;
        }

        return $result;
    }

    /**
     * 自减
     *
     * @param     $field
     * @param int $unit
     *
     * @throws Exception
     * @internal param $conditions
     * @internal param array $params
     * @return mixed
     */
    public function decrease($field, $unit = 1) {

        if (!$this->options['where']) {
            throw new Exception('FTable decrease function need condition');
        }

        $sql = 'UPDATE ' . $this->_table . " SET $field = IF($field > $unit,  $field - $unit, 0)";
        $sql .= ' WHERE ' . $this->options['where'];

        try {
            $this->connect('w');

            $stmt = $this->_dbh->prepare($sql);
            $result = $stmt->execute($this->options['params']);
        } catch (PDOException $e) {
            throw $e;
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
            $this->connect('w');

            $stmt = $this->_dbh->prepare($sql);
            $this->reset();
            return $stmt->execute($params);
        } catch (PDOException $e) {
            throw $e;
        }
    }

    /**
     * 重置 FTable
     */
    public function reset() {
        $this->options = null;
    }

    /**
     * 获得表结构
     * @return array|null
     * @throws Exception
     */
    public function getTableInfo() {
        $tableInfo = FCache::get($this->_table);
        if ($tableInfo) {
            return $tableInfo;
        }

        $sql = "desc {$this->config['table_pre']}$this->table_raw";

        try {
            $this->connect('r');

            $stmt = $this->_dbh->prepare($sql);
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $tableInfo = array('pri' => null, 'fields' => null);
            foreach ($rows as $row) {
                if ($row['Key'] == 'PRI') {
                    $tableInfo['pri'] = $row['Field'];
                }

                $tableInfo['fields'][$row['Field']] = $row;
            }

            FCache::set($this->_table, $tableInfo, 8640000);

        } catch (PDOException $e) {
            FLogger::write("获取表信息失败: " . $this->_table . "\t{$sql}\t" . $e->getMessage(), 'error');
            throw new Exception("获取表信息失败。");
        }

        return $tableInfo;
    }

    /**
     * 获得分页信息
     *
     * @throws Exception
     * @return array
     */
    public function getPagerInfo() {
        global $_F;

        $this->options = $this->getPagerOptions();
        $count = $this->count();
        $pagerOptions = $this->getPagerOptions();

        if (!isset($pagerOptions['page'])) {
            if ($_F['debug']) {
                throw new Exception('使用 getPagerInfo 的时候，必须在查询方法上使用 page 参数。如：$table->page(1)->limit(20)->select();');
            } else {
                return false;
            }
        }

        return FPager::getPagerInfo($count, $pagerOptions['page'], $pagerOptions['limit']);
    }

    public function leftJoin($table, $as, $on = null) {
        $this->_table = "{$this->_table} left join `{$table}` as {$as}";

        if ($on) $this->on($on);

        return $this;
    }

    public function on($condition) {
        $this->_table = "{$this->_table} on {$condition}";
        return $this;
    }
}
