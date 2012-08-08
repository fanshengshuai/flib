<?php

/**
 *
 * 作者: 范圣帅(fanshengshuai@gmail.com)
 *
 * 创建: 2012-06-22 21:44:05
 * vim: set expandtab sw=4 ts=4 sts=4
 *
 * $Id: Block.php 11 2012-07-24 03:42:35Z fanshengshuai $
 */

class Block {

    private $_table = "blocks";

    public function get($block_id) {

        $block_table = new DB_Table($this->_table);

        return $block_table->find("block_id={$block_id}");
    }

    public function add($block_data) {

        if (!$block_data['create_time']) {
            $block_data['create_time'] = date('Y-m-d H:i:s');
        }

        if (!$block_data['status']) {
            $block_data['status'] = 1;
        }

        $block_table = new DB_Table($this->_table);

        return $block_table->save($block_data);
    }
}
