<?php

require_once "FString.php";
require_once "FConfig.php";

/**
 * 作者: 范圣帅(fanshengshuai@gmail.com)
 *
 * 创建: 2011-04-18 22:35:29
 * vim: set expandtab sw=4 ts=4 sts=4 *
 *
 * $Id: Flib.php 766 2015-04-14 18:00:54Z fanshengshuai $
 */
class Flib
{

  /**
   * 系统自动加载Flib类库，并且支持配置自动加载路径
   *
   * @param $className
   *
   * @return mixed
   * @throws Exception
   * @internal param string $class 对象类名
   */
  public static function autoLoad($className)
  {
    global $_F;

    $err_msg = "File no found: \n";

    // if autoload Smarty, return false;
    // if ($className == 'Mongo' || $className == 'MongoClient' || strpos($className, 'Smarty') === 0) {
    //     return false;
    // }

    $class_path = str_replace('_', '/', $className) . ".php";
    //         echo $class_path . "\n";

    if (strpos($class_path, "Service.") > 0) {
      $inc_file = F_APP_ROOT . "services/{$className}.php";
      if (file_exists($inc_file)) {
        self::addDebugInfo('autoload_files', $inc_file);

        return require_once $inc_file;
      }
      $err_msg .= "$inc_file\n";
    }

    if (strpos($class_path, 'Ctrl.') !== false) {
      $inc_file = F_APP_ROOT . (isset($_F['module']) ? 'modules/' . $_F['module'] . '/controllers/' : 'c/') . $className . ".php";

      if (file_exists($inc_file)) {
        self::addDebugInfo('autoload_files', $inc_file);

        return require_once $inc_file;
      }

      $err_msg .= "$inc_file\n";

      // var_dump($inc_file);exit;

      $inc_file = F_APP_ROOT . (isset($_F['module']) ? 'modules/' . $_F['module'] . '/controllers/' : 'c/') . substr($className, strlen($_F['module'])) . ".php";
      if (file_exists($inc_file)) {
        self::addDebugInfo('autoload_files', $inc_file);

        return require_once $inc_file;
      } else {
        $err_msg .= "$inc_file\n";

        FException::getInstance()->traceError(new Exception(nl2br($err_msg)));
        return '';
      }
    }

    if (strpos($class_path, "Task.") > 0) {
      $inc_file = F_APP_ROOT . "tasks/{$className}.php";
      if (file_exists($inc_file)) {
        self::addDebugInfo('autoload_files', $inc_file);

        return require_once $inc_file;
      }
    }

    // 查是不是 flib 的 class
    $file = $class_path;
    $inc_file = FLIB_ROOT . $file;
    if (file_exists($inc_file)) {
      self::addDebugInfo('autoload_files', $inc_file);

      return require_once $inc_file;
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
        $class_explode[$key] = strtolower($item);
      }
    }
    $file = join('/', $class_explode);

    if (strpos($file, 'Ctrl.') !== false) {
      $inc_file = F_APP_ROOT . (isset($_F['module']) ? 'modules/' . $_F['module'] . '/controllers' : 'c') . '/' . $file;
    } else {
      $inc_file = F_APP_ROOT . $file;
    }

    //    echo $inc_file;

    if (file_exists($inc_file)) {
      self::addDebugInfo('autoload_files', $inc_file);

      return require_once $inc_file;
    }

    if (strpos($inc_file, 'controllers/')) {
      $inc_file = str_replace("controllers/", 'c/', $inc_file);
      if (file_exists($inc_file)) {
        self::addDebugInfo('autoload_files', $inc_file);

        return require_once $inc_file;
      }
    }

    if (count(spl_autoload_functions()) == 1) {
      throw new Exception('File no found: ' . $inc_file, 404);

      //    if ($_F ['debug']) {
      //                $_F ['debug_info'] ['autoload_files'] [] = "<span style='color:red'>{$inc_file} <strong>[ FAILED ]</strong></span><br /> Class: {$className}";
      //            }
    } else {
      //    spl_autoload_unregister(array('Flib', 'autoLoad'));
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
  public static function appException($e)
  {
    $exception = new FException();
    $exception->traceError($e);
    exit();
  }

  /**
   * 致命错误捕获
   */
  public static function fatalError()
  {
    if ($e = error_get_last()) {
      //            $exception = new FException();
      //            $exception->traceError(new Exception($e['message'] . '<br/>异常出现在：' . $e['file'] . ':' . $e['line'], 500));
      //            return;
      switch ($e['type']) {
        case E_ERROR:
        case E_PARSE:
        case E_CORE_ERROR:
        case E_COMPILE_ERROR:
        case E_USER_ERROR:
          //                    ob_end_clean();
          //                    require_once FLIB_ROOT . 'FException.php';
          $exception = new FException();
          $exception->traceError(new Exception($e['message'] . $e['file'] . ':' . $e['line'], 500));
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
  public static function appError($err_no, $err_str, $err_file, $err_line)
  {
    global $_F;

    //        if (error_reporting() === 0) {
    //            return;
    //        }

    switch ($err_no) {
      case E_ERROR:
      case E_USER_ERROR:
        $errorStr = "[$err_no] $err_str " . basename($err_file) . " 第 $err_line 行.";
        // if(C('LOG_RECORD')) Log::write($errorStr,Log::ERR);
        $exception = new FException();
        $exception->traceError(new Exception($errorStr));
        break;
      case E_STRICT:
        $_F['errors']['STRICT'][] = "[$err_no] $err_str " . basename($err_file) . " 第 $err_line 行.";
        break;
      case E_WARNING:
      case E_USER_WARNING:
        $_F['errors']['WARNING'][] = "[$err_no] $err_str " . basename($err_file) . " 第 $err_line 行.";
        break;
      case E_NOTICE:
      case E_USER_NOTICE:
        // $_F['errors']['NOTICE'][] = "[$err_no] $err_str " . basename($err_file) . " 第 $err_line 行.";
        break;
      default:
        $_F['errors']['OTHER'][] = "[$err_no] $err_str " . basename($err_file) . " 第 $err_line 行.";
        break;
    }
  }

  public static function createFlibMin()
  {
    $files = "FConfig, FCookie, FFile, FView, FDB, FTable, FException, FDispatcher, FController, FSession,FCache, FApp, FPager, FRequest, FLogger,FTemplate,FSetting";
    $files = explode(',', $files);

    $flib_str = '';
    foreach ($files as $class) {
      $class = trim($class);
      $f = FLIB_ROOT . trim($class) . '.php';
      $_content = file_GET_contents($f);
      //            $flib_str .= "\nif (!class_exists('$class')) {" . $_content . ' } ';
      $flib_str .= trim($_content, '<?php');
    }

    $flib_str = preg_replace('#/\*.+?\*/#si', '', $flib_str);
    // 斜线注释，如：// xxxxx 和 末尾注释： $a = 1; // xxx
    $flib_str = preg_replace('#^\s*#sim', '', $flib_str);
    $flib_str = preg_replace('#^[\s|]*//.+?$#sim', '', $flib_str);
    $flib_str = preg_replace('#;\s*//.+?$#sim', ';', $flib_str);
    $flib_str = preg_replace('#([{()|,;:])\s*#si', '\1', $flib_str);
    //        $flib_str = preg_replace("#\s{2,}#si", ' ', $flib_str);

    file_put_contents(F_APP_ROOT . "data/_flib_min.php", "<?php {$flib_str}");
  }

  public static function init()
  {
    global $_F;

    self::__checkVersion();

    self::__initEnv();
    //        self::safe();

    $_F['config'] = include WEB_ROOT_DIR . 'config/global.php';
    !$_F['debug'] && ($_F['debug'] = $_F['config']['debug']);

    !defined("F_RUN_MODE") && define('F_RUN_MODE', 'web');
    !defined('FLIB_ROOT') && define('FLIB_ROOT', dirname(__FILE__) . '/');

    if (!defined('F_APP_ROOT')) {
      $ctrlDir = dirname(dirname(dirname(__DIR__))) . '/';

      if (!is_dir($ctrlDir . 'config/')) {
        if (php_sapi_name() == 'cli') {
          $ctrlDir .= $_SERVER['PWD'] . '/';
        } else {
          $ctrlDir .= $_SERVER['DOCUMENT_ROOT'] . '/';
        }
      }
      define('F_APP_ROOT', $ctrlDir);
    }

    !defined('WEB_ROOT_DIR') && define('WEB_ROOT_DIR', F_APP_ROOT);

    //        $_F['user_agent'] = $_SERVER ['HTTP_USER_AGENT'];
    $_F['query_string'] = $_SERVER['QUERY_STRING'];

    !isset($_F['http_host']) && ($_F['http_host'] = $_SERVER['HTTP_HOST']);

    $last_part = substr($_F['http_host'], strrpos($_F['http_host'], '.'));
    if ($last_part == '.local') {
      $_F['dev_mode'] = true;
    } elseif ($last_part == '.lan') {
      $_F['test_mode'] = true;
    }

    if ($_F['config']['f_compress']) {
      if (!file_exists(F_APP_ROOT . "data/_flib_min.php")) {
        self::createFlibMin();
      }
      @include_once F_APP_ROOT . "data/_flib_min.php";
    }

    // 注册AUTOLOAD方法，设定错误和异常处理
    spl_autoload_register(array('Flib', 'autoLoad'));

    if ($_F['config']['debug'] || F_RUN_MODE == 'manual') {
      register_shutdown_function(array('Flib', 'fatalError'));
      restore_error_handler();
      set_error_handler(array('Flib', 'appError'));
      restore_exception_handler();
      set_exception_handler(array('Flib', 'appException'));
    }

    $left_part = str_replace($last_part, '', $_F['http_host']);
    $_F['cookie_domain'] = FConfig::get("global.cookie_domain");
    if (!$_F['cookie_domain']) {

      if ($left_part) {
        $_F['cookie_domain'] = "." . $_F['http_host'];
      }
    }

    $_F['domain'] = substr($left_part, strrpos($left_part, '.')) . $last_part;

    $_F['subdomain'] = str_replace($_F['cookie_domain'], '', $_F['http_host']);
    FDispatcher::getURI();

    $_F['refer'] = $_REQUEST['refer'] ? $_REQUEST['refer'] : $_SERVER['HTTP_REFERER'];

    $_F['in_ajax'] = (isset($_REQUEST['in_ajax'])) ? true : false;

    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') {
      $_F['in_ajax'] = true;
    }

    $_F['is_post'] = ($_POST || $_SERVER['REQUEST_METHOD'] == 'POST') ? true : false;

    if (!$_F['run_in']) {
      $_F['run_in'] = isset($_SERVER['HTTP_HOST']) ? 'web' : 'shell';
    }

    define('IS_POST', $_F['is_post']);

    $sub_domain_status = FConfig::get('global.sub_domain.status');

    // 是否开了子域名
    if ($sub_domain_status) {
      $default_module = 'www';
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

    self::handleCrossOrigin();

    if (!$_F['uri']) {
      FDispatcher::init();
    }

  }

  private static function __initEnv()
  {
    date_default_timezone_set('Asia/Chongqing');
    //        @ini_set("error_reporting", E_ERROR);
    //        ini_set("error_reporting", E_ALL);
    ini_set("error_reporting", E_ALL ^ E_NOTICE);
    @ini_set('display_errors', 'On');

    //        if (phpversion() < '5.3.0') set_magic_quotes_runtime(0);

    header("Content-type: text/html; charset=utf-8");
    header("Access-Control-Allow-Origin: *");

    !ob_get_status() && ob_start();
  }

  public static function resetAll()
  {
    global $_F;

    $_F = array();
  }

  public static function destroy()
  {
    Flib::resetAll();
    spl_autoload_unregister(array('Flib', 'autoLoad'));
    restore_error_handler();
    restore_exception_handler();
  }

  private static function __checkVersion()
  {
    if (phpversion() < '5.3') {
      echo "PHP版本太低,请升级到 5.3 以上, 建议版本 5.4.x ";
      exit;
    }
  }

  /**
   * 安全转义, 会导致处理麻烦. 在接到数据库时候, 做安全处理
   */
  private static function __safe()
  {
    if (!get_magic_quotes_gpc()) { //首先判断未开启
      if ($_GET) {
        $_GET = self::addslashes_deep($_GET);
      }
      if ($_POST) {
        $_POST = self::addslashes_deep($_POST);
      }
      if ($_REQUEST) {
        $_REQUEST = self::addslashes_deep($_REQUEST);
      }

    }
  }

  /**
   * 递归方式的对变量中的特殊字符进行转义
   *
   * @access  public
   * @param    $value
   * @return array|string
   */
  private static function __addslashes_deep($value)
  {
    if (empty($value)) {
      return $value;
    } else {
      return is_array($value) ? array_map(array("Flib", "addslashes_deep"), $value) : addcslashes($value, '\'');
    }
  }

  private static function addDebugInfo($subKey, $content)
  {
    global $_F;

    if ($_F['debug']) {
      $debugConfig = FConfig::get('debug');

      if ($subKey == 'autoload_files' && !$debugConfig['autoload_files']) {
        return;
      }

      $_F["debug_info"][$subKey][] = $content;

    }

  }

  private static function handleCrossOrigin()
  {

    global $_F;

    if (file_exists(WEB_ROOT_DIR . 'config/dev.php')) {
      $_F['config']['dev'] = include WEB_ROOT_DIR . 'config/dev.php';
    } else {
      return;
    }

    $local_url = $_F['config']['dev']['local_url'];
    if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
      //  解决预请求OPTIONS
      header('Access-Control-Allow-Origin:' . $local_url);
      header('Access-Control-Allow-Headers:Accept,Referer,os,ver,Host,Keep-Alive,User-Agent,X-Requested-With,Cache-Control,Content-Type,Cookie,Token,x-token,Ver');
      header('Access-Control-Allow-Credentials:true');
      header('Access-Control-Allow-Methods:GET,POST,OPTIONS');
      header('Access-Control-Max-Age:1728000');
      header('Content-Type:text/plain charset=UTF-8');
      header('Content-Length: 0', true);
      header('status: 200');
      header('HTTP/1.0 204 No Content');
      exit;
    } else {
      //   获取ajax请求header
      header('Access-Control-Allow-Origin:' . $local_url); //允许跨域请求的域名
      header('Access-Control-Allow-Credentials: true');
      header("Access-Control-Allow-Methods:GET, POST, PUT,DELETE,POSTIONS"); //  允许跨域请求的方式
      header("Access-Control-Allow-Headers: Origin, X-Requested-With,ver,Content-Type, Accept, Connection, User-Agent, Cookie,Token"); //  将前端自定义的header头名称写入，红色部分
    }
  }

}

define('FLIB', 1);
Flib::init();

if (F_RUN_MODE != 'manual') {
  FApp::run();
}