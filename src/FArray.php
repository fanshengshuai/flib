<?php

/**
 *
 * 作者: 范圣帅(fanshengshuai@gmail.com)
 *
 * 创建: 2012-08-27 11:19:33
 * vim: set expandtab sw=4 ts=4 sts=4 *
 *
 * $Id: FArray.php 764 2015-04-14 15:09:06Z fanshengshuai $
 */
class FArray {
    var $array;

    public static function getCol($array, $col) {
        $step = 0;

        $new_array = array();
        foreach ($array as $key => $a) {
            if ($step == 0) {
                $cols = array_keys($a);
            }

            $new_array[$key] = $a[$col];
        }

        return $new_array;
    }

    public static function getInstance($array) {
        static $fArray = null;

        if (!$fArray) {
            $fArray = new self($array);
        }

        return $fArray;
    }

    public function __construct($array) {
        $this->array = $array;
    }

    public function getByPage($page, $limit) {
        if (!$this->array) {
            return null;
        }

        $counter = 0;
        $from = ($page - 1) * $limit;

        $index = 0;
        $retData = array();
        foreach ($this->array as $row) {
            $index++;

            if ($index <= $from) {
                continue;
            }

            if ($counter >= $limit) break;

            $retData[] = $row;
            $counter++;

        }

        return $retData;
    }
}
