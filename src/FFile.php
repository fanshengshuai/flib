<?php

/**
 *
 * 作者: 范圣帅(fanshengshuai@gmail.com)
 *
 * 创建: 2012-07-31 12:08:29
 * vim: set expandtab sw=4 ts=4 sts=4 *
 *
 * $Id: FFile.php 764 2015-04-14 15:09:06Z fanshengshuai $
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
     * @param $dir string 目录
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

    /**
     * 删除目录和目录下文件
     *
     * @param $dir
     * @return bool
     * @throws Exception
     */
    public static function rmDir($dir) {
//        global $_F;


        if ($dir == '/' || !FString::endWith($dir, '/')) {
            throw new Exception('DIR must end of / and can not be Root Dir');
        }

        // 先删除目录下的文件
        $dirHandle = opendir($dir);
        while ($file = readdir($dirHandle)) {
            if ($file != "." && $file != "..") {
                $fullpath = $dir . "/" . $file;
                if (!is_dir($fullpath)) {
                    unlink($fullpath);
                } else {
                    self::rmDir($fullpath . "/");
                }
            }
        }

        closedir($dirHandle);

        // 删除当前文件夹
        if (rmdir($dir)) {
            return true;
        } else {
            return false;
        }
    }

    public static function parsePath($file_path) {
        $_tmp = parse_url($file_path);
    }

    public static function unlink($file_path) {
        if (!unlink($file_path)) {
            file_put_contents(F_APP_ROOT . "data/ffile_" . date('Y-m-d') . ".log", "{$file_path} unlink failed.\n", FILE_APPEND);
        }
    }

    /**
     * @param $file_path string 文件地址
     * @param $content string 文件内容
     * @return bool|int
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
