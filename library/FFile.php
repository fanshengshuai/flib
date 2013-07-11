<?php

/**
 *
 * 作者: 范圣帅(fanshengshuai@gmail.com)
 *
 * 创建: 2012-07-31 12:08:29
 * vim: set expandtab sw=4 ts=4 sts=4 * 
 *
 * $Id: FlibFile.php 92 2012-07-31 08:08:22Z fanshengshuai $
 */
class FFile {

    public static function getFileExtion($file_name) {
        return addslashes(strtolower(substr(strrchr($file_name, '.'), 1, 10)));
    }

    public static function getHashPath($seed, $deep  = 3) {

        $md5 = md5($seed);

        $dir = '';
        for ($i = 0; $i < $deep; $i ++) {
            $dir .= $md5{$i};
            $i ++;
            $dir .= $md5{$i} . '/';
        }

        return array('dir' => $dir, 'file' => $md5);
    }
}
