<?php

/**
 *
 * 作者: 范圣帅(fanshengshuai@gmail.com)
 * 时间: 2010-11-12 01:21:07
 *
 * vim: set expandtab sw=4 ts=4 sts=4
 * $Id: Cache.php 11 2012-07-24 03:42:35Z fanshengshuai $
 */

class Cache {
    public static function set($cache_key, $cache_content, $cache_time = 7200, $force = false) {

        $save_content = json_encode(array('cache_time' => $cache_time, 'content' => $cache_content));

        $cache_file = Cache::getFileCachePath($cache_key);

        if ($force || !file_exists($cache_file) || (filemtime($cache_file) < time() - $cache_time)) {
            file_put_contents($cache_file, $save_content);
        }
    }

    public static function get($cache_key) {

        $cache_file = Cache::getFileCachePath($cache_key);
        $content = json_decode(file_get_contents($cache_file), true);

        if ($content &&
            (filemtime($cache_file) + intval($content['cache_time'])) < time()
        ) {
            return $content['content'];
        } else {
            return null;
        }
    }

    public static function getFileCachePath($cache_key) {

        if (!defined("APP_ROOT")) {
            throw new Exception("Cache Exception: APP_ROOT not defined.");
        }

        $cache_file = APP_ROOT . "data/cache/c_" . md5($cache_key);

        return $cache_file;
    }

    public static function delete($cache_key) {
        $cache_file = Cache::getFileCachePath($cache_key);

        unlink($cache_key);
    }

    public static function deleteAll() {
        $cache_dir = APP_ROOT . "data/cache/";

        $d = opendir($cache_dir);
        while ($file = readdir($d)) {
            $cache_file = $cache_dir . $file;

            if (is_file($cache_file) && $file != '.' && $file != '..') {
                unlink($cache_file);
            }
        }

        closedir($d);
    }
}
