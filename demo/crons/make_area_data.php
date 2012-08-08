<?php

/**
 *
 * 作者: 范圣帅(fanshengshuai@gmail.com)
 *
 * 创建: 2012-07-27 00:40:36
 * vim: set expandtab sw=4 ts=4 sts=4 * 
 *
 * $Id: make_area_data.php 112 2012-08-02 08:26:56Z www $
 */
define('APP_ROOT', dirname(dirname(__FILE__)) . '/');
define('RUN_MODE', 'cli');

require_once APP_ROOT . "libraries/flib/Flib.php";

$table = new DB_Table('area');
$provinces = $table->findAll("parent_id=0");


$res_data = array();

foreach ($provinces as $p) {
    $data_citys = $table->findAll("parent_id = '{$p['area_id']}'");

    $citys = array();
    foreach ($data_citys as $c) {
        $citys[] = array(
            $c['area_id'] => $c['city']
        );
    }

    $res_data[] = array(
        'id' => $p['area_id'],
        'name' => $p['province'],
        'citys' => $citys,
    );
}

$data = json_encode($res_data);
file_put_contents(APP_ROOT . "www/js/area_data.js", $data);
