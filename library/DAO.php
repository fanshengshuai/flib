<?php

/**
 *
 * 作者: 范圣帅(fanshengshuai@gmail.com)
 *
 * 创建: 2012-07-24 22:57:17
 * vim: set expandtab sw=4 ts=4 sts=4 * 
 *
 * $Id: DAO.php 62 2012-07-27 16:45:54Z fanshengshuai $
 */
class DAO extends DB_Table {

    public function __construct() {
        parent::__construct($this->_table);
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

    public function listByPage($page, $order = array(), $conditions = null) {

        $page_option = array(
            'curr_page' => max(1, intval($page)),
            'total' => $this->count($conditions),
            'per_page' => 20,
        );

        Pager::build($page_option);

        $start = $page_option['start'];
        $limit = $page_option['per_page'];

        $results = $this->findAll($conditions, array(), array('*'), $start, $limit, $order = array());

        return array('data' => $results, 'page_option' => $page_option);
    }
}
