<?php

/**
 *
 * 作者: 范圣帅(fanshengshuai@gmail.com)
 *
 * 创建: 2011-07-19 11:37:41
 * vim: set expandtab sw=4 ts=4 sts=4 * 
 *
 * $Id: Dispatcher.php 126 2012-08-05 08:18:46Z fanshengshuai $
 */
class Dispatcher {

    public static function dispatch() {
        global $_G;

        $dispatcher = new Dispatcher;
        $dispatcher->_getURI();

        $c = $_GET['c'];
        $a = $_GET['a'];


        if (!$c || !$a) {

            $dispatcher->_checkRouter($c, $a);
        }


        if (!$c || !$a) {

            if ($_G['uri'] == '/') {

                $c = 'index';
                $a = 'default';
            } else {

                $path_info = explode('/', $_G['uri']);

                if ($path_info[3]) {
                    $_G['app'] = $app = $path_info[1];
                    $c = $path_info[2];
                    $a = $path_info[3];
                } else {
                    $c = $path_info[1];
                    $a = $path_info[2];
                }
            }
        }


        if (!$c || !$a) {
            throw new Exception("访问路径不正确，没有找到 {$_G['uri']} 。");exit;
        }

        if ($_G['app']) {
            $_G['controller'] = 'Controller_' . ucfirst($_G['app']) . '_' . ucfirst($c);
        } else {
            $_G['controller'] = 'Controller_' . ucfirst($c);
        }

        $_G['action'] = $a;

        if (!class_exists($_G['controller'])) {
            throw new Exception("找不到控制器：{$_G['controller']}");exit;
        }

        $controller = new $_G['controller'];
        $action = $_G['action'].'Action';
        if (method_exists($controller, 'beforeAction')) {
            $controller->beforeAction();
        }

        if (method_exists($controller, $action . "")) {
            $controller->$action();
        } else {
            throw new Exception("找不到 {$_G['action']}Action ");exit;
            //throw new Exception($_G['controller'] . ".{$_G['action']} not exists");exit;
        }
    }

    private function _checkRouter(&$c, &$a) {
        global $_G;

        $router_config_file = APP_ROOT . "config/router.php";

        if (!file_exists($router_config_file)) {
            return false;
        }

        require $router_config_file;

        $uri = strtolower($_G['uri']);

        if (isset($_config['router'][$uri])) {

            $c = $_config['router'][$uri]['controller'];
            $a = $_config['router'][$uri]['action'];

            return true;
        } else {

            foreach ($_config['router'] as $key => $item) {
                if (strpos($key, '(') === false) {
                    continue;
                }

                if (preg_match("#^{$key}$#", $uri, $res)) {
                    $c = $_config['router'][$key]['controller'];
                    $a = $_config['router'][$key]['action'];

                    $params = explode(',', $_config['router'][$key]['params']);

                    foreach ($params as $k => $p) {
                        $p = trim($p);
                        $_GET[$p] = $res[$k + 1];
                    }

                    break;
                }
            }

            return false;
        }
    }

    private function _getURI() {
        global $_G;

        $_G['uri'] = $_SERVER['HTTP_X_ORIGINAL_URL'];
        if (!$_G['uri']) {
            $_G['uri'] = $_SERVER['REQUEST_URI'];
        }

        $_G['uri'] = preg_replace('/\?.*$/', '', $_G['uri']);

        if ($_G['uri']) {
            $_G['uri'] = rtrim($_G['uri'], '/');
        }

        if (!$_G['uri']) {
            $_G['uri'] = '/';
        }
    }
}
