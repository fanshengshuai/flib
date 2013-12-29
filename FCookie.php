<?php

/**
 *
 * 作者: 范圣帅(fanshengshuai@gmail.com)
 * 时间: 2012-07-02 01:22:06
 *
 * vim: set expandtab sw=4 ts=4 sts=4
 * $Id: Cookie.php 23 2012-07-25 05:38:34Z fanshengshuai $
 */

class FCookie {
    public static function set($var, $value, $life = 7200) {
        global $_G;

//        ob_flush(); ob_clean();

        $domain = $_G['cookie_domain'];

        $timestamp = time();
        $path = "/";
        $httponly = false;

        $secure = $_SERVER['SERVER_PORT'] == 443 ? 1 : 0;
        $life = $life > 0 ? $timestamp + $life : ($life < 0 ? $timestamp - 31536000 : 0);

        setcookie($var, $value, $life, $path);//, $domain, $secure);
        return ;

        if(PHP_VERSION < '5.2.0') {
            setcookie($var, $value, $life, $path, $domain, $secure);
        } else {
            setcookie($var, $value, $life, $path, $domain, $secure, $httponly);
        }
    }

    public static function get($key) {

        return $_COOKIE[$key];
    }

    public static function delete($key) {
        Cookie::set($key, null, -1);
    }
    public static function remove($key) {
        self::set($key, null, -1);
    }
}
