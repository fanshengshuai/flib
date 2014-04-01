<?php

class FCache {
    public static function getContent() {
        global $_F;

        $content = file_GET_contents($_F['cache_file']);
        $content .= "<!-- hit from fcache -->";

        return $content;
    }

    public static function check() {
        global $_F;

        if (!Config::get('fcache.enable')) {
            return false;
        }

        if (!file_exists(APP_ROOT . "data/fcache")) {
            mkdir(APP_ROOT . "data/fcache", 0777, true);
        }

        $str_query = "";
        foreach ($_GET as $_k => $_v) {
            $str_query .= "_{$_k}_{$_v}";
        }

        $_F['cache_file'] = APP_ROOT . "data/fcache/{$_F['controller']}_{$_F['action']}{$str_query}.html";
        $_F['cache_file'] = strtolower(APP_ROOT . "data/fcache/{$_F['controller']}_{$_F['action']}{$str_query}.html");

        if (!file_exists($_F['cache_file'])) {
            return false;
        }

        if ($_COOKIE['tudai_TxRVlS_auth']) {
            return false;
        }

        if (!FCache::checkCacheUri()) {
            return false;
        }

        $cache_mtime = filemtime($_F['cache_file']);
        if (time() - $cache_mtime > 600) {
            return false;
        }

        return true;
    }

    public static function save($content) {
        global $_F;

        if (!Config::get('fcache.enable')) {
            return ;
        }

        if (!FCache::checkCacheUri()) {
            return ;
        }

        $res = file_put_contents($_F['cache_file'], $content);
        if ($_F['debug'] && $res === FALSE) throw new Exception("前端缓存写入失败! 没有目录，或者权限对？文件：" . $_F['cache_file']);
        return true;
    }

    public static function checkCacheUri() {
        global $_F;

        if ($_F['controller'] == 'Controller_Index' && $_F['action'] == 'default') {
            return true;
        }

        if ($_GET['page']) {
            return false;
        }
    }
}
