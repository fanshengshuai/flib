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

    public static function getHashPath($seed, $deep  = 3, $root_path=null, $create_dir=false) {

        $md5 = md5($seed);

        $dir = '';
        for ($i = 0; $i < $deep; $i ++) {
            $dir .= $md5{$i};
            $i ++;
            $dir .= $md5{$i} . '/';
        }

        if ($root_path) {
            if (strpos($root_path, '/') !== 0) {
                throw new Exception('root_path 必须是 / 开头的！');
            }

            $dir = rtrim($root_path, '/') . '/' . $dir;

            if ($create_dir) {
                self::mkdir($dir);
            }
        }

        return array('dir' => $dir, 'file' => $md5, 'file_path' => $dir . $md5);
    }

    public static function mkdir($dir) {
        if (!file_exists($dir)) {
            mkdir($dir, 0777, true);
            chmod($dir, 0777);
        }
    }

    public static function parsePath($file_path) {
        $_tmp = parse_url($file_path);
    }
}
