<?php

/**
 *
 * 作者: 范圣帅(fanshengshuai@gmail.com)
 * 时间: 2010-11-12 01:21:07
 *
 * vim: set expandtab sw=4 ts=4 sts=4
 * $Id: FCache.php 11 2012-07-24 03:42:35Z fanshengshuai $
 */

class FCache {
    public static function set($cache_key, $cache_content, $cache_time = 7200, $force = false) {
        global $_F;

        $cache_key = $_F['domain'] . $cache_key;

        self::init();

        if ($_F['memcache']) {

            $_F['memcache']->set($cache_key, $cache_content, MEMCACHE_COMPRESSED, $cache_time );
			return true;
		}
		$save_content = json_encode ( array (
				'cache_time' => $cache_time, 'content' => $cache_content));

        $cache_file = FCache::getFileFCachePath($cache_key);

        file_put_contents($cache_file, $save_content);
    }

    public static function get($cache_key) {
        global $_F;

        $cache_key = $_F['domain'] . $cache_key;

        self::init();

        if ($_F['memcache']) {
            return $_F['memcache']->get($cache_key);
        }

        $cache_file = FCache::getFileFCachePath($cache_key);
        $content = json_decode(file_GET_contents($cache_file), true);

        if ($content &&
            (filemtime($cache_file) + intval($content['cache_time'])) > time()
        ) {
            return $content['content'];
        } else {
            return null;
        }
    }

    public static function getFileFCachePath($cache_key) {

        if (!defined("APP_ROOT")) {
            throw new Exception("FCache Exception: APP_ROOT not defined.");
        }

        $cache_dir = FConfig::get('global.cache_dir');

        if ($cache_dir) { $cache_dir = "{$cache_dir}/"; }
        else { $cache_dir = APP_ROOT . "data/cache/"; }

        $hash_file_path = FFile::getHashPath($cache_key, 3, $cache_dir, true);
        $cache_file = $hash_file_path['file_path'];

        return $cache_file;
    }

    public static function delete($cache_key) {
        $cache_file = FCache::getFileFCachePath($cache_key);

        unlink($cache_key);
    }

    public static function deleteAll() {
        global $_F;

        self::init();

        if ($_F['memcache']) {
            return $_F['memcache']->flush();
        }

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

    public static function init() {
        global $_F;

        if (FConfig::get('global.memcache.enable')) {
	        $_F['memcache'] = new Memcache;
	        $_F['memcache']->connect('127.0.0.1', 11211);
        }
    }
}
