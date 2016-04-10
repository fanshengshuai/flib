<?php

/**
 *
 * 作者: 范圣帅(fanshengshuai@gmail.com)
 *
 * 创建: 2011-07-19 11:37:41
 * vim: set expandtab sw=4 ts=4 sts=4 *
 *
 * $Id: FDispatcher.php 764 2015-04-14 15:09:06Z fanshengshuai $
 */
class FDispatcher {

    public static function init() {
        global $_F;

        if (FConfig::get('global.module.status')) {
            $_F['module'] = FConfig::get('global.module.default');
        }

        $dispatcher = new FDispatcher;

        if (!isset($_F['uri'])) {
            self::getURI();
        }

        $c = isset($_GET['c']) ? $_GET['c'] : null;
        $a = isset($_GET['a']) ? $_GET['a'] : null;


        if (!$c || !$a) {

            $dispatcher->_checkRouter($c, $a);
        }


        if (!$c || !$a) {

            if ($_F['uri'] == '/' || $_F['uri'] == '' || $_F['uri'] == '/index') {

                $c = 'index';
                $a = 'default';

            } else {

                $path_info = explode('/', $_F['uri']);

                if (isset($path_info[3])) {
                    $_F['module'] = $app = $path_info[1];
                    $c = $path_info[2];
                    $a = $path_info[3];
                } elseif (isset($path_info[2])) {
                    $c = $path_info[1];
                    $a = $path_info[2];
                } elseif (isset($path_info[1])) {
                    $c = $path_info[1];
                    $a = 'default';
                }
            }
        }

        if (!empty($_F['app'])) {
            $_F['controller'] = 'Controller_' . ucfirst($_F['app']) . '_' . ucfirst($c);
        } elseif ($_F['module']) {
            $_F['controller'] = 'Controller_' . ucfirst($_F['module']) . '_' . ucfirst($c);
        } else {
            $_F['controller'] = 'Controller_' . ucfirst($c);
        }

        $_F['action'] = $a;
    }

    public static function dispatch() {
        global $_F;

        self::init();

        if (!$_F['controller'] || !$_F['action']) {
            if (F_RUN_MODE == 'sync') {
                return false;
            }
            throw new Exception("访问路径不正确，没有找到 {$_F['uri']}", 404);
        }
        $path_info = explode('/', $_F['uri']);

        if(isset($path_info[1])&&!isset($path_info[2])&&!isset($path_info[3]) && FConfig::get("global.openDiy")) {
            $pager_table = new FTable('page');
            $pager_info = $pager_table -> where("url='".$_F['uri']."'") -> find();
            if($pager_info){
                $_F['controller'] = 'Controller_Front_Public';
                $_F['default'] = $_F['action'];
                $_F['action'] = 'default';
            }
        }else{
        if (!class_exists($_F['controller'])) {
            if (F_RUN_MODE == 'sync') {
                return false;
            }

            if ($_F['module']) {
                $c_backup = str_replace(ucfirst($_F['module']) . '_', '', $_F['controller']);
                if (class_exists($c_backup)) {
                    $_F['controller'] = $c_backup;
                } else {
                    throw new Exception("找不到控制器：{$_F['controller']}", 404);
                }
            } else {
                throw new Exception("找不到控制器：{$_F['controller']}", 404);
            }

        }
        }

        $controller = new $_F['controller'];
        $action = $_F['action'] . 'Action';


        if (method_exists($controller, $action . "")) {

            if (method_exists($controller, 'beforeAction')) {
                // IMPORTANT beforeAction must return true
                if (!$controller->beforeAction()) {
                    return false;
                }
            }

            // 加载函数类
            require_once FLIB_ROOT . "functions/function_core.php";
            $controller->$action();
        } else {
            try {
                $fView = new FView;
                $fView->disp();
            }catch (Exception $e) {

                throw new Exception("找不到 {$_F['action']}Action ", 404);
            }
        }

        return true;
    }

    private function _checkRouter(&$c, &$a) {
        global $_F;

        if (isset($_F['module'])) {
            $router = FConfig::get("router.{$_F['module']}");
        } else {
            $router = FConfig::get("router");
        }

        if (!$router) {
            return false;
            throw new Exception("$router_config_file not found !");
        }


        $uri = strtolower($_F['uri']);

        if (isset($router[$uri])) {


            if ($router[$uri]['url']) {
                redirect($router[$uri]['url']);
            }

            if ($router[$uri]['module']) {
                $_F['module'] = $router[$uri]['module'];
            }

            $c = $router[$uri]['controller'];
            $a = $router[$uri]['action'];

            return true;
        } else {

            foreach ($router as $key => $item) {
                if (strpos($key, '(') === false) {
                    continue;
                }

                if (preg_match("#^{$key}$#i", $_F['uri'], $res)) {

                    if ($router[$uri]['url']) {
                        redirect($router[$uri]['url']);
                    }

                    if ($router[$uri]['module']) {
                        $_F['module'] = $router[$uri]['module'];
                    }

                    $c = $router[$key]['controller'];
                    $a = $router[$key]['action'];

                    $params = explode(',', $router[$key]['params']);

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

    public static function getURI() {
        global $_F;

        $_F['uri'] = isset($_SERVER['HTTP_X_ORIGINAL_URL']) ? $_SERVER['HTTP_X_ORIGINAL_URL'] : null;
        if (!$_F['uri']) {
            $_F['uri'] = $_SERVER['REQUEST_URI'];
        }

        $_F['uri'] = preg_replace('/\?.*$/', '', $_F['uri']);

        if ($_F['uri']) {
            $_F['uri'] = rtrim($_F['uri'], '/');
        }

        if (!$_F['uri']) {
            $_F['uri'] = '/';
        }
    }
}
