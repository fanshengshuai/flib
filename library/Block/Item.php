<?php

/**
 * 作者: 范圣帅(fanshengshuai@gmail.com)
 *
 * 创建: 2012-06-22 21:55:08
 * vim: set expandtab sw=4 ts=4 sts=4
 *
 * $Id: Item.php 11 2012-07-24 03:42:35Z fanshengshuai $
 */

class Block_Item {

    private $_table = "block_items";

    public function listByBlockId($block_id) {

        $block_table = new DB_Table($this->_table);

        return $block_table->findAll("block_id={$block_id}");
    }

    public function add($data) {

        if (!$data['create_time']) {
            $data['create_time'] = date('Y-m-d H:i:s');
        }

        if (!$data['status']) {
            $data['status'] = 1;
        }

        $table = new DB_Table($this->_table);

        return $table->save($data);
    }
    public function updateItemsByBlockId($block_id) {

        $block = $this->get($block_id);

        $last_update_time = strtotime($block['update_time']);
        $now = time();
    }
}
