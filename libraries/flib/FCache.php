<?php

class FCache {
    public static function getContent() {
        global $_G;

        $content = file_get_contents($_G['cache_file']);
        $content .= "<!-- hit from fcache -->";

        return $content;
    }

    public static function check() {
        global $_G;

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

        $_G['cache_file'] = APP_ROOT . "data/fcache/{$_G['controller']}_{$_G['action']}{$str_query}.html";
        $_G['cache_file'] = strtolower(APP_ROOT . "data/fcache/{$_G['controller']}_{$_G['action']}{$str_query}.html");

        if (!file_exists($_G['cache_file'])) {
            return false;
        }

        if ($_COOKIE['tudai_TxRVlS_auth']) {
            return false;
        }

        if (!FCache::checkCacheUri()) {
            return false;
        }

        $cache_mtime = filemtime($_G['cache_file']);
        if (time() - $cache_mtime > 600) {
            return false;
        }

        return true;
    }

    public static function save($content) {
        global $_G;

        if (!Config::get('fcache.enable')) {
            return ;
        }

        if (!FCache::checkCacheUri()) {
            return ;
        }

        $res = file_put_contents($_G['cache_file'], $content);
        if ($_G['debug'] && $res === FALSE) throw new Exception("前端缓存写入失败! 没有目录，或者权限对？文件：" . $_G['cache_file']);
        return true;
    }

    public static function checkCacheUri() {
        global $_G;

        if ($_G['controller'] == 'Controller_Index' && $_G['action'] == 'default') {
            return true;
        }

        if ($_GET['page']) {
            return false;
        }
    }
}
