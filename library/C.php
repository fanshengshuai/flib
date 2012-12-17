<?php
/**
 *
 * 作者: 范圣帅(fanshengshuai@gmail.com)
 *
 * 创建: 2012-12-16 10:12:53
 * vim: set expandtab sw=4 ts=4 sts=4 * 
 *
 * $Id$
 */

class C {
    public static function set($cache_key, $cache_content, $cache_time=3600) {
        Cache::set($cache_key, $cache_content, $cache_time);
    }

    public static function get($cache_key) {
        return Cache::get($cache_key);
    }
}

