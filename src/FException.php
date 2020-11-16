<?php

/**
 *
 * 作者: 范圣帅(fanshengshuai@gmail.com)
 *
 * 创建: 2012-07-24 10:29:39
 * vim: set expandtab sw=4 ts=4 sts=4 *
 *
 * $Id: FException.php 764 2015-04-14 15:09:06Z fanshengshuai $
 */
class FException extends Exception
{
  /**
   * @var $this
   */
  private static $__instance;

  /**
   * @return $this
   */
  public static function getInstance()
  {
    if (self::$__instance === null) {
      self::$__instance = new self;
    }

    return self::$__instance;
  }

  protected $view;

  public function __construct()
  {
    $this->view = new FView;
  }

  /**
   * @param $e Exception
   */
  /**
   * 返回错误
   *
   * @param Exception $e 异常
   * @return void
   */
  public function traceError($e)
  {
    global $_F;

    $error_code = 0;
    $exception_trace = '';

    if (!is_array($e)) {
      $error_code = $e->getCode();
    }

    if (is_array($e)) {
      $error_message = $e['message'];
      $error_file = $e['file'];
      $error_line = $e['line'];
    } else {
      $error_message = $e->getMessage();
      $error_file = $e->getFile();
      $error_line = $e->getLine();
      $exception_trace = nl2br($e->__toString());
    }
    $trace = $e->getTrace();
    $error_file = $trace[1]['file'] . ' 第 ' . $error_line . ' 行';

    // var_dump($e->getTrace());exit;

    $exception_message = $error_message
      . '<br /> 异常出现在：' . $trace[1]['file'] . ' 第 ' . $error_line . ' 行';

    $log_text = "\n--------------------------------------------------------\n";

    if (isset($_F['current_sql'])) {
      $log_text .= "SQL: " . $_F['current_sql'] . "\n";
      $log_text .= "--------------------------------------------------------\n";
    }

    if (is_object($e)) {
      $log_text .= $e->__toString() . "\n";
      $log_text .= "--------------------------------------------------------\n";
    } else {
      $log_text .= $error_message . "\n";
      $log_text .= "--------------------------------------------------------\n";
    }

    if ($_F['run_in'] == 'shell') {
      $log_text_header = "\n\n          ==========================================\n                         ERROR FOUND\n          ========================================== \n";
      die($log_text_header . $log_text);
    }

    FLogger::write($log_text, 'error');

    if (!$_F["config"]['debug']) {
      if ($error_code == 404) {
        FResponse::sendStatusHeader(404);
        $this->view->displaySysPage('404.tpl');
      } else {
        FResponse::sendStatusHeader(500);
        $this->view->displaySysPage('500.tpl');
      }
      exit;
    }

    $exception_message = str_replace(F_APP_ROOT, '', $exception_message);

    if ($_F['in_ajax']) {

      if ($_F['debug']) {
        $ajaxError = preg_replace('/<' . 'br.+?' . '>/i', "\n", $exception_message);

        FResponse::jsonErr($ajaxError, 500);
        exit;
      } else {
        if ($error_code == 404) {
          FResponse::sendStatusHeader(404);
        } else {
          FResponse::sendStatusHeader(500);
        }
        exit;
      }
    }

    $exception_trace = str_replace(F_APP_ROOT, '', $exception_trace);
    $this->view->assign('exception_message', $exception_message);
    $this->view->assign('exception_trace', preg_replace('#[\w\d \#]+?/f.php.+?$#si', ' Flib 引导入口', $exception_trace));

    $this->view->displaySysPage('exception.tpl');
  }
}