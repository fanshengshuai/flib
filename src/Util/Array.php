<?php

/**
 *
 * 作者: 范圣帅(fanshengshuai@gmail.com)
 *
 * 创建: 2012-08-27 11:19:33
 * vim: set expandtab sw=4 ts=4 sts=4 *
 *
 * $Id: Array.php 764 2015-04-14 15:09:06Z fanshengshuai $
 */
class Util_Array {

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
}
