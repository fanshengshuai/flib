<?php

/**
 * 作者: 范圣帅(fanshengshuai@gmail.com)
 *
 * 创建: 2011-04-18 22:35:29
 * vim: set expandtab sw=4 ts=4 sts=4 *
 *
 * $Id: Flib.php 766 2015-04-14 18:00:54Z fanshengshuai $
 */
class Flib {

    /**
     * 系统自动加载Flib类库，并且支持配置自动加载路径
     *
     * @param $className
     *
     * @throws Exception
     * @return mixed
     * @internal param string $class 对象类名
     */
    public static function autoLoad($className) {
        global $_F;

        // if autoload Smarty, return false;
        if (strpos($className, 'Smarty') === 0) {
            return false;
        }

        $class_path = str_replace('_', '/', $className) . ".php";

        // 查是不是 flib 的 class
        $file = $class_path;
        $inc_file = FLIB_ROOT . $file;
        if (file_exists($inc_file)) {
            if (isset($_F ['debug'])) {
                $_F ['debug_info'] ['autoload_files'] [] = $inc_file;
            }

            return require_once($inc_file);
        }

        // 检查项目文件
        $className = str_replace(
            array('Service/', 'DAO/'),
            array('services/', 'dao/'),
            $class_path);

        $class_explode = explode('/', $className);
        $class_explode_len = sizeof($class_explode);
        foreach ($class_explode as $key => $item) {
            if ($key < ($class_explode_len - 1)) {
                $class_explode [$key] = strtolower($item);
            }
        }
        $file = join('/', $class_explode);

        if (strpos($file, 'Ctrl.') !== false) {
            $inc_file = F_APP_ROOT . (isset($_F['module']) ? 'modules/' . $_F['module'] : 'c') . '/' . substr($file, strlen($_F['module']));
        } else {
            $inc_file = F_APP_ROOT . $file;
        }

//        echo $inc_file;

        if (file_exists($inc_file)) {
            if ($_F ['debug']) {
                $_F ['debug_info'] ['autoload_files'] [] = $inc_file;
            }

            return require_once($inc_file);
        }

        if (strpos($inc_file, 'controllers/')) {
            $inc_file = str_replace("controllers/", 'c/', $inc_file);
            if (file_exists($inc_file)) {
                if ($_F ['debug']) {
                    $_F ['debug_info'] ['autoload_files'] [] = $inc_file;
                }

                return require_once($inc_file);
            }
        }

        if (count(spl_autoload_functions()) == 1) {
            throw new Exception('File no found: ' . $inc_file, 404);

//            if ($_F ['debug']) {
//                $_F ['debug_info'] ['autoload_files'] [] = "<span style='color:red'>{$inc_file} <strong>[ FAILED ]</strong></span><br /> Class: {$className}";
//            }
        } else {
//            spl_autoload_unregister(array('Flib', 'autoLoad'));
        }

        return false;
    }

    /**
     * 自定义异常处理
     *
     * @access public
     *
     * @param mixed $e
     *            异常对象
     */
    static public function appException($e) {
        $exception = new FException ();
        $exception->traceError($e);
        exit ();
    }

    /**
     * 致命错误捕获
     */
    static public function fatalError() {
        if ($e = error_get_last()) {
            switch ($e['type']) {
                case E_ERROR:
                case E_PARSE:
                case E_CORE_ERROR:
                case E_COMPILE_ERROR:
                case E_USER_ERROR:
                    ob_end_clean();
                    require_once FLIB_ROOT . 'FException.php';
                    $exception = new FException ();
                    $exception->traceError($e);
                    break;
            }
        }
    }

    /**
     * 自定义错误处理
     *
     * @param $err_no int 错误类型
     * @param $err_str string 错误信息
     * @param $err_file string 错误文件
     * @param $err_line int 错误行数
     */
    static public function appError($err_no, $err_str, $err_file, $err_line) {
        global $_F;

//        if (error_reporting() === 0) {
//            return;
//        }

        switch ($err_no) {
            case E_ERROR :
            case E_USER_ERROR :
                $errorStr = "[$err_no] $err_str " . basename($err_file) . " 第 $err_line 行.";
                // if(C('LOG_RECORD')) Log::write($errorStr,Log::ERR);
                $exception = new FException ();
                $exception->traceError(new Exception($errorStr));
                break;
            case E_STRICT :
                $_F['errors']['STRICT'][] = "[$err_no] $err_str " . basename($err_file) . " 第 $err_line 行.";
                break;
            case E_WARNING:
            case E_USER_WARNING :
                $_F['errors']['WARNING'][] = "[$err_no] $err_str " . basename($err_file) . " 第 $err_line 行.";
                break;
            case E_NOTICE:
            case E_USER_NOTICE :
                // $_F['errors']['NOTICE'][] = "[$err_no] $err_str " . basename($err_file) . " 第 $err_line 行.";
                break;
            default :
                $_F['errors']['OTHER'][] = "[$err_no] $err_str " . basename($err_file) . " 第 $err_line 行.";
                break;
        }
    }

    public static function createFlibMin() {
        $files = "FConfig, FCookie,FDebug, FFile, FView, FDB, FTable, FException, FDispatcher, FController, FSession,FCache, FApp, FPager, FRequest, FRedis, FLogger";
        $files = explode(',', $files);

        $flib_str = '';
        foreach ($files as $class) {
            $class = trim($class);
            $f = FLIB_ROOT . trim($class) . '.php';
            $_content = file_GET_contents($f);
            $flib_str .= "\nif (!class_exists('$class')) {" . $_content . ' } ';
        }

        $flib_str = str_replace('<?php', '', $flib_str);
        $flib_str = preg_replace('#/\*.+?\*/#si', '', $flib_str);
        $flib_str = preg_replace('#//.+?$#sim', '', $flib_str);
        $flib_str = preg_replace("#\s{2,}#si", ' ', $flib_str);

        file_put_contents(F_APP_ROOT . "data/_flib_min.php", "<?php {$flib_str}");
    }

    public static function init() {
        global $_F;

        self::initEnv();


        $_F ['config'] = array();
        header("Content-type: text/html; charset=utf-8");
        header("Access-Control-Allow-Origin: *");

        if (!defined('FLIB_ROOT')) {
            define('FLIB_ROOT', dirname(__FILE__) . '/');
        }

        if (!defined('F_APP_ROOT')) {
            if (isset($_SERVER['PWD']))
                define('F_APP_ROOT', $_SERVER['PWD'] . '/');
            else
                define('F_APP_ROOT', getcwd() . '/');
        } else {
            //exit('please define F_APP_ROOT');
        }

        define('CURRENT_TIMESTAMP', time());
        define('CURRENT_DATE_TIME', date('Y-m-d H:i:s'));

        $_F['user_agent'] = $_SERVER ['HTTP_USER_AGENT'];
        $_F['query_string'] = $_SERVER ['QUERY_STRING'];

        $_F['http_host'] ? $_F['http_host'] : $_F['http_host'] = $_SERVER ['HTTP_HOST'];

        $last_part = substr($_F ['http_host'], strrpos($_F ['http_host'], '.'));
        if ($last_part == '.local') {
            $_F['dev_mode'] = true;
        } elseif ($last_part == '.lan') {
            $_F['test_mode'] = true;
        }

        // 注册AUTOLOAD方法，设定错误和异常处理
        spl_autoload_register(array('Flib', 'autoLoad'));

        register_shutdown_function(array('Flib', 'fatalError'));
        restore_error_handler();
        set_error_handler(array('Flib', 'appError'));
        restore_exception_handler();
        set_exception_handler(array('Flib', 'appException'));

        $left_part = str_replace($last_part, '', $_F ['http_host']);
        if ($left_part) {
            $_F['cookie_domain'] = substr($left_part, strrpos($left_part, '.')) . $last_part;
        }
        $_F['domain'] = trim($_F['cookie_domain'], '.');

        $_F['subdomain'] = str_replace($_F['cookie_domain'], '', $_F ['http_host']);
        FDispatcher::getURI();

        $_F['refer'] = $_REQUEST ['refer'] ? $_REQUEST ['refer'] : $_SERVER ['HTTP_REFERER'];

        $_F['in_ajax'] = ($_REQUEST['in_ajax'] || $_GET ['in_ajax'] || $_POST ['in_ajax']) ? true : false;

        if ($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') {
            $_F['in_ajax'] = true;
        }


        $_F['is_post'] = ($_POST) ? true : false;

        $_F['run_in'] = isset($_SERVER ['HTTP_HOST']) ? 'web' : 'shell';

        define('IS_POST', $_F['is_post']);

        !$_F['debug'] && ($_F['debug'] = FConfig::get("global.debug"));

        if (FConfig::get('global.flib_compress')) {
            if (!file_exists(F_APP_ROOT . "data/_flib_min.php")) {
                self::createFlibMin();
            }
            include_once(F_APP_ROOT . "data/_flib_min.php");
        }

        $sub_domain_status = FConfig::get('global.sub_domain.status');

        // 是否开了子域名
        if ($sub_domain_status) {
            foreach (FConfig::get('global.sub_domain.sub_domain_rewrite') as $key => $value) {
                if ($key == $_F['subdomain']) {
                    $_F['module'] = $value;
                }

                if ($key == '*') {
                    $default_module = $value;
                }
            }

            if (!$_F['module']) {
                $_F['module'] = $default_module;
            }
        }

        if (!$_F ['uri']) {
            FDispatcher::init();
        }

    }

    private static function initEnv() {
        date_default_timezone_set('Asia/Chongqing');
        ini_set("error_reporting", E_ALL & ~E_NOTICE);

        if (phpversion() < '5.3.0') set_magic_quotes_runtime(0);

        !ob_get_status() && ob_start();
    }

    public static function resetAll() {
        global $_F;

        $_F = array();
    }

    public static function destroy() {
        Flib::resetAll();
        spl_autoload_unregister(array('Flib', 'autoLoad'));
        restore_error_handler();
        restore_exception_handler();
    }


}

define('FLIB', 1);
Flib::init();

if (FLIB_RUN_MODE != 'manual') {
    FApp::run();
}
