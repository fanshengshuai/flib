<?php

/**
 *
 * 作者: 范圣帅(fanshengshuai@gmail.com)
 *
 * 创建: 2012-07-24 22:57:17
 * vim: set expandtab sw=4 ts=4 sts=4 *
 *
 * $Id: DAO.php 196 2012-08-11 10:46:03Z fanshengshuai $
 */
abstract class DAO extends FDB_Table {
    // 数据库表达式
    protected $comparison = array('eq' => '=', 'neq' => '<>', 'gt' => '>', 'egt' => '>=', 'lt' => '<', 'elt' => '<=', 'notlike' => 'NOT LIKE', 'like' => 'LIKE', 'in' => 'IN', 'notin' => 'NOT IN');
    // 查询表达式
    protected $selectSql = 'SELECT%DISTINCT% %FIELD% FROM %TABLE%%JOIN%%WHERE%%GROUP%%HAVING%%ORDER%%LIMIT% %UNION%%COMMENT%';

    public function __construct() {
        parent::__construct($this->_table);
    }

    public function listByPage($page, $order = array(), $conditions = null, $per_page = 20) {

        $page_option = array(
            'curr_page' => max(1, intval($page)),
            'total' => $this->count($conditions),
            'per_page' => $per_page,
        );

        FPager::build($page_option);

        $start = $page_option['start'];
        $limit = $page_option['per_page'];

        $results = $this->findAll($conditions, array(), array('*'), $start, $limit, $order);

        return array('data' => $results, 'page_option' => $page_option);
    }

    public function get($_pk_key) {

        $sql = "{$this->_pk} = '{$_pk_key}' and status = 1";
        return $this->find($sql);
    }

    public function add($data) {

        if (!$data['create_time']) {
            $data['create_time'] = date('Y-m-d H:i:s');
        }

        if (!$data['status']) {
            $data['status'] = 1;
        }

        $this->save($data);

        return $this->lastInsertId();
    }

    public function find() {

    }

    public function update($_pk_key, $data) {

        if (!$data['update_time']) {
            $data['update_time'] = date('Y-m-d H:i:s');
        }

        return $this->save($data, "{$this->_pk} = '{$_pk_key}'");
    }

    public function delete($_pk_key) {

        $data = array('status' => 2);
        $data['remove_time'] = date('Y-m-d H:i:s');


        return $this->save($data, "{$this->_pk} = '{$_pk_key}'");
    }

    /**
     * 查找记录
     * @access public
     *
     * @param array $options 表达式
     *
     * @return mixed
     */
    public function select($options = array()) {

        // 分析表达式
        $options = $this->_parseOptions($options);
        $sql = $this->buildSelectSql($options);

//        echo $sql;
//        exit;
        $result = $this->query($sql, $this->parseBind(!empty($options['bind']) ? $options['bind'] : array()));
        return $result;
    }

    /**
     * 生成查询SQL
     * @access public
     *
     * @param array $options 表达式
     *
     * @return string
     */
    public function buildSelectSql($options = array()) {
        if (isset($options['page'])) {
            // 根据页数计算limit
            if (strpos($options['page'], ',')) {
                list($page, $listRows) = explode(',', $options['page']);
            } else {
                $page = $options['page'];
            }
            $page = $page ? $page : 1;
            $listRows = isset($listRows) ? $listRows : (is_numeric($options['limit']) ? $options['limit'] : 20);
            $offset = $listRows * ((int)$page - 1);
            $options['limit'] = $offset . ',' . $listRows;
        }
//        if (C('DB_SQL_BUILD_CACHE')) { // SQL创建缓存
//            $key = md5(serialize($options));
//            $value = S($key);
//            if (false !== $value) {
//                return $value;
//            }
//        }
        $sql = $this->parseSql($this->selectSql, $options);
        $sql .= $this->parseLock(isset($options['lock']) ? $options['lock'] : false);
//        if (isset($key)) { // 写入SQL创建缓存
//            S($key, $sql, array('expire' => 0, 'length' => C('DB_SQL_BUILD_LENGTH'), 'queue' => C('DB_SQL_BUILD_QUEUE')));
//        }
        return $sql;
    }

    /**
     * 替换SQL语句中表达式
     * @access public
     *
     * @param $sql
     * @param array $options 表达式
     *
     * @return string
     */
    public function parseSql($sql, $options = array()) {
        $sql = str_replace(
            array('%TABLE%', '%DISTINCT%', '%FIELD%', '%JOIN%', '%WHERE%', '%GROUP%', '%HAVING%', '%ORDER%', '%LIMIT%', '%UNION%', '%COMMENT%'),
            array(
                $this->parseTable($options['table']),
                $this->parseDistinct(isset($options['distinct']) ? $options['distinct'] : false),
                $this->parseField(!empty($options['field']) ? $options['field'] : '*'),
                $this->parseJoin(!empty($options['join']) ? $options['join'] : ''),
                $this->parseWhere(!empty($options['where']) ? $options['where'] : ''),
                $this->parseGroup(!empty($options['group']) ? $options['group'] : ''),
                $this->parseHaving(!empty($options['having']) ? $options['having'] : ''),
                $this->parseOrder(!empty($options['order']) ? $options['order'] : ''),
                $this->parseLimit(!empty($options['limit']) ? $options['limit'] : ''),
                $this->parseUnion(!empty($options['union']) ? $options['union'] : ''),
                $this->parseComment(!empty($options['comment']) ? $options['comment'] : '')
            ), $sql);
        return $sql;
    }

    /**
     * table分析
     * @access protected
     *
     * @param $tables
     *
     * @internal param mixed $table
     * @return string
     */
    protected function parseTable($tables) {

        return $this->_table;
        if (is_array($tables)) { // 支持别名定义
            $array = array();
            foreach ($tables as $table => $alias) {
                if (!is_numeric($table))
                    $array[] = $this->parseKey($table) . ' ' . $this->parseKey($alias);
                else
                    $array[] = $this->parseKey($table);
            }
            $tables = $array;
        } elseif (is_string($tables)) {
            $tables = explode(',', $tables);
            array_walk($tables, array(&$this, 'parseKey'));
        }
        $tables = implode(',', $tables);
        return $tables;
    }

    /**
     * limit分析
     * @access protected
     *
     * @param $limit
     *
     * @internal param mixed $lmit
     *
     * @return string
     */
    protected function parseLimit($limit) {
        return !empty($limit) ? ' LIMIT ' . $limit . ' ' : '';
    }

    /**
     * join分析
     * @access protected
     *
     * @param array $join
     *
     * @return string
     */
    protected function parseJoin($join) {
        $joinStr = '';
        if (!empty($join)) {
            $joinStr = ' ' . implode(' ', $join) . ' ';
        }
        return $joinStr;
    }

    /**
     * order分析
     * @access protected
     *
     * @param mixed $order
     *
     * @return string
     */
    protected function parseOrder($order) {
        if (is_array($order)) {
            $array = array();
            foreach ($order as $key => $val) {
                if (is_numeric($key)) {
                    $array[] = $this->parseKey($val);
                } else {
                    $array[] = $this->parseKey($key) . ' ' . $val;
                }
            }
            $order = implode(',', $array);
        }
        return !empty($order) ? ' ORDER BY ' . $order : '';
    }

    /**
     * group分析
     * @access protected
     *
     * @param mixed $group
     *
     * @return string
     */
    protected function parseGroup($group) {
        return !empty($group) ? ' GROUP BY ' . $group : '';
    }

    /**
     * having分析
     * @access protected
     *
     * @param string $having
     *
     * @return string
     */
    protected function parseHaving($having) {
        return !empty($having) ? ' HAVING ' . $having : '';
    }

    /**
     * comment分析
     * @access protected
     *
     * @param string $comment
     *
     * @return string
     */
    protected function parseComment($comment) {
        return !empty($comment) ? ' /* ' . $comment . ' */' : '';
    }

    /**
     * distinct分析
     * @access protected
     *
     * @param mixed $distinct
     *
     * @return string
     */
    protected function parseDistinct($distinct) {
        return !empty($distinct) ? ' DISTINCT ' : '';
    }

    /**
     * union分析
     * @access protected
     *
     * @param mixed $union
     *
     * @return string
     */
    protected function parseUnion($union) {
        if (empty($union)) return '';
        if (isset($union['_all'])) {
            $str = 'UNION ALL ';
            unset($union['_all']);
        } else {
            $str = 'UNION ';
        }
        foreach ($union as $u) {
            $sql[] = $str . (is_array($u) ? $this->buildSelectSql($u) : $u);
        }
        return implode(' ', $sql);
    }

    /**
     * 插入记录
     * @access public
     *
     * @param mixed $data 数据
     * @param array $options 参数表达式
     * @param boolean $replace 是否replace
     *
     * @return false | integer
     */
    public function insert($data, $options = array(), $replace = false) {
        $values = $fields = array();
        $this->model = $options['model'];
        foreach ($data as $key => $val) {
            if (is_array($val) && 'exp' == $val[0]) {
                $fields[] = $this->parseKey($key);
                $values[] = $val[1];
            } elseif (is_scalar($val) || is_null($val)) { // 过滤非标量数据
                $fields[] = $this->parseKey($key);
                if (C('DB_BIND_PARAM') && 0 !== strpos($val, ':')) {
                    $name = md5($key);
                    $values[] = ':' . $name;
                    $this->bindParam($name, $val);
                } else {
                    $values[] = $this->parseValue($val);
                }
            }
        }
        $sql = ($replace ? 'REPLACE' : 'INSERT') . ' INTO ' . $this->parseTable($options['table']) . ' (' . implode(',', $fields) . ') VALUES (' . implode(',', $values) . ')';
        $sql .= $this->parseLock(isset($options['lock']) ? $options['lock'] : false);
        $sql .= $this->parseComment(!empty($options['comment']) ? $options['comment'] : '');
        return $this->execute($sql, $this->parseBind(!empty($options['bind']) ? $options['bind'] : array()));
    }

    /**
     * 设置锁机制
     * @access protected
     *
     * @param bool $lock
     *
     * @return string
     */
    protected function parseLock($lock = false) {
        if (!$lock) return '';
        if ('ORACLE' == $this->dbType) {
            return ' FOR UPDATE NOWAIT ';
        }
        return ' FOR UPDATE ';
    }

    /**
     * set分析
     * @access protected
     *
     * @param array $data
     *
     * @return string
     */
    protected function parseSet($data) {
        foreach ($data as $key => $val) {
            if (is_array($val) && 'exp' == $val[0]) {
                $set[] = $this->parseKey($key) . '=' . $val[1];
            } elseif (is_scalar($val) || is_null($val)) { // 过滤非标量数据
                if (C('DB_BIND_PARAM') && 0 !== strpos($val, ':')) {
                    $name = md5($key);
                    $set[] = $this->parseKey($key) . '=:' . $name;
                    $this->bindParam($name, $val);
                } else {
                    $set[] = $this->parseKey($key) . '=' . $this->parseValue($val);
                }
            }
        }
        return ' SET ' . implode(',', $set);
    }

    /**
     * 参数绑定
     * @access protected
     *
     * @param string $name 绑定参数名
     * @param mixed $value 绑定值
     *
     * @return void
     */
    protected function bindParam($name, $value) {
        $this->bind[':' . $name] = $value;
    }

    /**
     * 参数绑定分析
     * @access protected
     *
     * @param array $bind
     *
     * @return array
     */
    protected function parseBind($bind) {
        $bind = array_merge($this->bind, $bind);
        $this->bind = array();
        return $bind;
    }

    /**
     * 字段名分析
     * @access protected
     *
     * @param string $key
     *
     * @return string
     */
    protected function parseKey(&$key) {
        return $key;
    }

    /**
     * value分析
     * @access protected
     *
     * @param mixed $value
     *
     * @return string
     */
    protected function parseValue($value) {
        if (is_string($value)) {
            $value = '\'' . $this->escapeString($value) . '\'';
        } elseif (isset($value[0]) && is_string($value[0]) && strtolower($value[0]) == 'exp') {
            $value = $this->escapeString($value[1]);
        } elseif (is_array($value)) {
            $value = array_map(array($this, 'parseValue'), $value);
        } elseif (is_bool($value)) {
            $value = $value ? '1' : '0';
        } elseif (is_null($value)) {
            $value = 'null';
        }
        return $value;
    }

    /**
     * field分析
     * @access protected
     *
     * @param mixed $fields
     *
     * @return string
     */
    protected function parseField($fields) {
        if (is_string($fields) && strpos($fields, ',')) {
            $fields = explode(',', $fields);
        }
        if (is_array($fields)) {
            // 完善数组方式传字段名的支持
            // 支持 'field1'=>'field2' 这样的字段别名定义
            $array = array();
            foreach ($fields as $key => $field) {
                if (!is_numeric($key))
                    $array[] = $this->parseKey($key) . ' AS ' . $this->parseKey($field);
                else
                    $array[] = $this->parseKey($field);
            }
            $fieldsStr = implode(',', $array);
        } elseif (is_string($fields) && !empty($fields)) {
            $fieldsStr = $this->parseKey($fields);
        } else {
            $fieldsStr = '*';
        }
        //TODO 如果是查询全部字段，并且是join的方式，那么就把要查的表加个别名，以免字段被覆盖
        return $fieldsStr;
    }

    /**
     * where分析
     * @access protected
     *
     * @param mixed $where
     *
     * @return string
     */
    protected function parseWhere($where) {
        $whereStr = '';
        if (is_string($where)) {
            // 直接使用字符串条件
            $whereStr = $where;
        } else { // 使用数组表达式
            $operate = isset($where['_logic']) ? strtoupper($where['_logic']) : '';
            if (in_array($operate, array('AND', 'OR', 'XOR'))) {
                // 定义逻辑运算规则 例如 OR XOR AND NOT
                $operate = ' ' . $operate . ' ';
                unset($where['_logic']);
            } else {
                // 默认进行 AND 运算
                $operate = ' AND ';
            }
            foreach ($where as $key => $val) {
                $whereStr .= '( ';
                if (is_numeric($key)) {
                    $key = '_complex';
                }
                if (0 === strpos($key, '_')) {
                    // 解析特殊条件表达式
                    $whereStr .= $this->parseThinkWhere($key, $val);
                } else {
                    // 查询字段的安全过滤
                    if (!preg_match('/^[A-Z_\|\&\-.a-z0-9\(\)\,]+$/', trim($key))) {
                        E(L('_EXPRESS_ERROR_') . ':' . $key);
                    }
                    // 多条件支持
                    $multi = is_array($val) && isset($val['_multi']);
                    $key = trim($key);
                    if (strpos($key, '|')) { // 支持 name|title|nickname 方式定义查询字段
                        $array = explode('|', $key);
                        $str = array();
                        foreach ($array as $m => $k) {
                            $v = $multi ? $val[$m] : $val;
                            $str[] = '(' . $this->parseWhereItem($this->parseKey($k), $v) . ')';
                        }
                        $whereStr .= implode(' OR ', $str);
                    } elseif (strpos($key, '&')) {
                        $array = explode('&', $key);
                        $str = array();
                        foreach ($array as $m => $k) {
                            $v = $multi ? $val[$m] : $val;
                            $str[] = '(' . $this->parseWhereItem($this->parseKey($k), $v) . ')';
                        }
                        $whereStr .= implode(' AND ', $str);
                    } else {
                        $whereStr .= $this->parseWhereItem($this->parseKey($key), $val);
                    }
                }
                $whereStr .= ' )' . $operate;
            }
            $whereStr = substr($whereStr, 0, -strlen($operate));
        }
        return empty($whereStr) ? '' : ' WHERE ' . $whereStr;
    }


    /**
     * 指定查询条件 支持安全过滤
     * @access public
     *
     * @param mixed $where 条件表达式
     * @param mixed $parse 预处理参数
     *
     * @return Model
     */
    public function where($where, $parse = null) {
        if (!is_null($parse) && is_string($where)) {
            if (!is_array($parse)) {
                $parse = func_get_args();
                array_shift($parse);
            }
            $parse = array_map(array($this->db, 'escapeString'), $parse);
            $where = vsprintf($where, $parse);
        } elseif (is_object($where)) {
            $where = get_object_vars($where);
        }
        if (is_string($where) && '' != $where) {
            $map = array();
            $map['_string'] = $where;
            $where = $map;
        }
        if (isset($this->options['where'])) {
            $this->options['where'] = array_merge($this->options['where'], $where);
        } else {
            $this->options['where'] = $where;
        }

        return $this;
    }

    /**
     * 指定查询数量
     * @access public
     *
     * @param mixed $offset 起始位置
     * @param mixed $length 查询数量
     *
     * @return Model
     */
    public function limit($offset, $length = null) {
        $this->options['limit'] = is_null($length) ? $offset : $offset . ',' . $length;
        return $this;
    }

    /**
     * 指定分页
     * @access public
     *
     * @param mixed $page 页数
     * @param mixed $listRows 每页数量
     *
     * @return Model
     */
    public function page($page, $listRows = null) {
        $this->options['page'] = is_null($listRows) ? $page : $page . ',' . $listRows;
        return $this;
    }

    /**
     * 分析表达式
     * @access protected
     *
     * @param array $options 表达式参数
     *
     * @return array
     */
    protected function _parseOptions($options = array()) {
        if (is_array($options))
            $options = array_merge($this->options, $options);

        if (!isset($options['table'])) {
            // 自动获取表名
            $options['table'] = $this->getTableName();
            $fields = $this->fields;
        } else {
            // 指定数据表 则重新获取字段列表 但不支持类型检测
            $fields = $this->getDbFields();
        }
        // 查询过后清空sql表达式组装 避免影响下次查询
        $this->options = array();
        // 数据表别名
        if (!empty($options['alias'])) {
            $options['table'] .= ' ' . $options['alias'];
        }
        // 记录操作的模型名称
        $options['model'] = $this->name;

        // 字段类型验证
        if (isset($options['where']) && is_array($options['where']) && !empty($fields) && !isset($options['join'])) {
            // 对数组查询条件进行字段类型检查
            foreach ($options['where'] as $key => $val) {
                $key = trim($key);
                if (in_array($key, $fields, true)) {
                    if (is_scalar($val)) {
                        $this->_parseType($options['where'], $key);
                    }
                } elseif (!is_numeric($key) && '_' != substr($key, 0, 1) && false === strpos($key, '.') && false === strpos($key, '(') && false === strpos($key, '|') && false === strpos($key, '&')) {
                    unset($options['where'][$key]);
                }
            }
        }

        // 表达式过滤
        $this->_options_filter($options);
        return $options;
    }

    // 表达式过滤回调方法
    protected function _options_filter(&$options) {
    }

    /**
     * 得到完整的数据表名
     * @access public
     * @return string
     */
    public function getTableName() {
        return $this->_table;
    }

    // where子单元分析
    protected function parseWhereItem($key, $val) {
        $whereStr = '';
        if (is_array($val)) {
            if (is_string($val[0])) {
                if (preg_match('/^(EQ|NEQ|GT|EGT|LT|ELT)$/i', $val[0])) { // 比较运算
                    $whereStr .= $key . ' ' . $this->comparison[strtolower($val[0])] . ' ' . $this->parseValue($val[1]);
                } elseif (preg_match('/^(NOTLIKE|LIKE)$/i', $val[0])) { // 模糊查找
                    if (is_array($val[1])) {
                        $likeLogic = isset($val[2]) ? strtoupper($val[2]) : 'OR';
                        if (in_array($likeLogic, array('AND', 'OR', 'XOR'))) {
                            $likeStr = $this->comparison[strtolower($val[0])];
                            $like = array();
                            foreach ($val[1] as $item) {
                                $like[] = $key . ' ' . $likeStr . ' ' . $this->parseValue($item);
                            }
                            $whereStr .= '(' . implode(' ' . $likeLogic . ' ', $like) . ')';
                        }
                    } else {
                        $whereStr .= $key . ' ' . $this->comparison[strtolower($val[0])] . ' ' . $this->parseValue($val[1]);
                    }
                } elseif ('exp' == strtolower($val[0])) { // 使用表达式
                    $whereStr .= ' (' . $key . ' ' . $val[1] . ') ';
                } elseif (preg_match('/IN/i', $val[0])) { // IN 运算
                    if (isset($val[2]) && 'exp' == $val[2]) {
                        $whereStr .= $key . ' ' . strtoupper($val[0]) . ' ' . $val[1];
                    } else {
                        if (is_string($val[1])) {
                            $val[1] = explode(',', $val[1]);
                        }
                        $zone = implode(',', $this->parseValue($val[1]));
                        $whereStr .= $key . ' ' . strtoupper($val[0]) . ' (' . $zone . ')';
                    }
                } elseif (preg_match('/BETWEEN/i', $val[0])) { // BETWEEN运算
                    $data = is_string($val[1]) ? explode(',', $val[1]) : $val[1];
                    $whereStr .= ' (' . $key . ' ' . strtoupper($val[0]) . ' ' . $this->parseValue($data[0]) . ' AND ' . $this->parseValue($data[1]) . ' )';
                } else {
                    E(L('_EXPRESS_ERROR_') . ':' . $val[0]);
                }
            } else {
                $count = count($val);
                $rule = isset($val[$count - 1]) ? (is_array($val[$count - 1]) ? strtoupper($val[$count - 1][0]) : strtoupper($val[$count - 1])) : '';
                if (in_array($rule, array('AND', 'OR', 'XOR'))) {
                    $count = $count - 1;
                } else {
                    $rule = 'AND';
                }
                for ($i = 0; $i < $count; $i++) {
                    $data = is_array($val[$i]) ? $val[$i][1] : $val[$i];
                    if ('exp' == strtolower($val[$i][0])) {
                        $whereStr .= '(' . $key . ' ' . $data . ') ' . $rule . ' ';
                    } else {
                        $whereStr .= '(' . $this->parseWhereItem($key, $val[$i]) . ') ' . $rule . ' ';
                    }
                }
                $whereStr = substr($whereStr, 0, -4);
            }
        } else {
            //对字符串类型字段采用模糊匹配
//            if (C('DB_LIKE_FIELDS') && preg_match('/(' . C('DB_LIKE_FIELDS') . ')/i', $key)) {
//                $val = '%' . $val . '%';
//                $whereStr .= $key . ' LIKE ' . $this->parseValue($val);
//            } else {
            $whereStr .= $key . ' = ' . $this->parseValue($val);
//            }
        }
        return $whereStr;
    }

    /**
     * 特殊条件分析
     * @access protected
     *
     * @param string $key
     * @param mixed $val
     *
     * @return string
     */
    protected function parseThinkWhere($key, $val) {
        $whereStr = '';
        switch ($key) {
            case '_string':
                // 字符串模式查询条件
                $whereStr = $val;
                break;
            case '_complex':
                // 复合查询条件
                $whereStr = is_string($val) ? $val : substr($this->parseWhere($val), 6);
                break;
            case '_query':
                // 字符串模式查询条件
                parse_str($val, $where);
                if (isset($where['_logic'])) {
                    $op = ' ' . strtoupper($where['_logic']) . ' ';
                    unset($where['_logic']);
                } else {
                    $op = ' AND ';
                }
                $array = array();
                foreach ($where as $field => $data)
                    $array[] = $this->parseKey($field) . ' = ' . $this->parseValue($data);
                $whereStr = implode($op, $array);
                break;
        }
        return $whereStr;
    }

    /**
     * SQL指令安全过滤
     * @access public
     *
     * @param string $str SQL字符串
     *
     * @return string
     */
    public function escapeString($str) {
        return addslashes($str);
    }

    /**
     * 查询SQL组装 join
     * @access public
     *
     * @param mixed $join
     * @param string $type JOIN类型
     *
     * @return Model
     */
    public function join($join, $type = 'INNER') {
        $prefix = $this->tablePrefix;
        if (is_array($join)) {
            foreach ($join as $key => &$_join) {
                $_join = preg_replace_callback("/__([A-Z_-]+)__/sU", function ($match) use ($prefix) {
                    return $prefix . strtolower($match[1]);
                }, $_join);
                $_join = false !== stripos($_join, 'JOIN') ? $_join : $type . ' JOIN ' . $_join;
            }
            $this->options['join'] = $join;
        } elseif (!empty($join)) {
            //将__TABLE_NAME__字符串替换成带前缀的表名
            $join = preg_replace_callback("/__([A-Z_-]+)__/sU", function ($match) use ($prefix) {
                return $prefix . strtolower($match[1]);
            }, $join);
            $this->options['join'][] = false !== stripos($join, 'JOIN') ? $join : $type . ' JOIN ' . $join;
        }
        return $this;
    }

    /**
     * 查询SQL组装 union
     * @access public
     *
     * @param mixed $union
     * @param boolean $all
     *
     * @return Model
     */
    public function union($union, $all = false) {
        if (empty($union)) return $this;
        if ($all) {
            $this->options['union']['_all'] = true;
        }
        if (is_object($union)) {
            $union = get_object_vars($union);
        }
        // 转换union表达式
        if (is_string($union)) {
            $prefix = $this->tablePrefix;
            //将__TABLE_NAME__字符串替换成带前缀的表名
            $options = preg_replace_callback("/__([A-Z_-]+)__/sU", function ($match) use ($prefix) {
                return $prefix . strtolower($match[1]);
            }, $union);
        } elseif (is_array($union)) {
            if (isset($union[0])) {
                $this->options['union'] = array_merge($this->options['union'], $union);
                return $this;
            } else {
                $options = $union;
            }
        } else {
            E(L('_DATA_TYPE_INVALID_'));
        }
        $this->options['union'][] = $options;
        return $this;
    }

    /**
     * SQL查询
     * @access public
     *
     * @param string $sql SQL指令
     * @param mixed $parse 是否需要解析SQL
     *
     * @return mixed
     */
    public function query($sql, $parse = false) {
        if (!is_bool($parse) && !is_array($parse)) {
            $parse = func_get_args();
            array_shift($parse);
        }
        $sql = $this->parseSql($sql, $parse);
        echo $sql;

        return $this->db->query($sql);
    }
}
