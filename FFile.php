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

    public static function getHashPath($seed, $deep = 3, $root_path = null, $create_dir = false) {

        $md5 = md5($seed);

        $dir = '';
        for ($i = 0; $i < $deep; $i++) {
            $dir .= $md5{$i};
            $i++;
            $dir .= $md5{$i} . '/';
        }

        if ($root_path) {
            if (strpos($root_path, '/') !== 0 && strpos($root_path, ':') === false) {
                throw new Exception('root_path 必须是 绝对路径！');
            }

            $dir = rtrim($root_path, '/') . '/' . $dir;

            if ($create_dir) {
                self::mkdir($dir);
            }
        }

        return array('dir' => $dir, 'file' => $md5, 'file_path' => $dir . $md5);
    }

    /**
     * 创建目录
     *
     * @param $dir 目录
     *
     * @return bool
     */
    public static function mkdir($dir) {
        $ret = false;

        if (!file_exists($dir)) {
            $ret = mkdir($dir, 0755, true);
            chmod($dir, 0755);
        }

        return $ret;
    }

    public static function parsePath($file_path) {
        $_tmp = parse_url($file_path);
    }

    public static function unlink($file_path) {
        if (!unlink($file_path)) {
            file_put_contents(APP_ROOT . "data/ffile_" . date('Y-m-d') . ".log", "{$file_path} unlink failed.\n", FILE_APPEND);
        }
    }

    /**
     * @param $file_path 文件地址
     * @param $content 文件内容
     */
    public static function save($file_path, $content) {
        $ret = true;

        $path_info = pathinfo($file_path);

        if (!file_exists($path_info['dirname'])) {
            $ret = self::mkdir($path_info['dirname']);
        }

        if ($ret) {
            $ret = file_put_contents($file_path, $content);
        }

        return $ret;
    }

    public static function append($file_path, $content) {
        file_put_contents($file_path, FILE_APPEND);
    }

    public static function isWriteAble($file) {
        return is_writable($file);
    }
}
