<?php

/**
 * Class FResponse
 *
 * 作者: 范圣帅(fanshengshuai@gmail.com)
 * 时间: 2014-05-10 01:19:41
 *
 * vim: set expandtab sw=4 ts=4 sts=4
 */
class FResponse
{

  /**
   * header
   * @var array
   */
  protected $_header = array();
  private $header;

  public static function getUrl()
  {
    return $GLOBALS['F']['url'];
  }
  /**
   * 设字符集，如果设置过 Content-type 为 json, 返回false
   *
   * @param string $encoding
   *
   * @return bool
   */
  public function setCharacterEncoding($encoding = 'utf-8')
  {

    // json 不设编码
    if ($this->header['Content-type'] == 'application/json') {
      return false;
    }

    $this->setHeader('Content-type', 'text/html; charset=' . $encoding);
    return true;
  }

  /**
   * 设置 header
   *
   * @param $headerKey
   * @param $headerValue
   */
  public function setHeader($headerKey, $headerValue)
  {
    $this->header[$headerKey] = $headerValue;
  }

  public function setContentType($contentType)
  {

    if ($contentType == 'json') {
      $this->setHeader('Content-type', 'application/json');
    }
  }

  /**
   * 文本输出内容
   *
   * @param $content string 内容
   */
  public function write($content = null)
  {
    global $_F;

    // 日志写入
    FLogger::flush();

    if ($_F['run_in'] !== 'shell') {
      ob_clean();
    }

    if ($this->header) {
      foreach ($this->header as $h_key => $h_value) {
        header("{$h_key}: $h_value");
      }
    }

//        if ($_F['run_in'] == 'shell') {
    //            echo '当前页面：' . $_F['uri'] . "\n";
    //        }

    if ($content) {
      echo $content;
    }

  }

  /**
   * 输出内容，可以是数组，可以是文本
   *
   * @param array | string $mix 输出内容
   * @param bool $exit 是否结束程序
   */
  public static function output($mix, $exit = true)
  {
    global $_F;

    if (!$mix) {
      return;
    }

    $response = new self;

    if (is_array($mix)) {
      $response->setContentType('json');

      if ($_F['debug']) {
        $mix['debug_info'] = $_F['debug_info'];
      }

      if ($_F['run_in'] == 'shell') {
        $mix = json_encode($mix, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
      } else {
        $mix = json_encode($mix, JSON_UNESCAPED_UNICODE);
      }

      $response->write($mix);
    } elseif (is_string($mix)) {
      $response->write($mix);
    }

    $exit && exit;
  }

  public static function jsonResult($status, $result, $error_code = 0)
  {
    $result = array("status" => $status, "result" => $result, "err_code" => $error_code);
    if ($error_code > 0 || is_string($result["result"])) {
      $result['msg'] = $result["result"];
    }
    if ($status == 'err' && is_string($result["result"])) {
      $result['msg'] = $result["result"];
      unset($result["result"]);
    }

    $token = FSession::get('token');
    if ($token) {
      $result['token'] = $token;
    }

    self::output($result);
  }

  public static function jsonOk($result = '', $error_code = 0)
  {
    self::jsonResult('ok', $result, $error_code);
  }

  /**
   * @param string $result
   * @param int $error_code
   */
  public static function jsonErr($result = '', $error_code = 0)
  {
    self::jsonResult('err', $result, $error_code);
  }

  public static function sendHeader($headerKey, $headerValue = null)
  {

    if (is_numeric($headerKey) && $headerValue == null) {
      self::sendStatusHeader($headerKey);
    } else {
      header($headerKey . ': ' . $headerValue);
    }
  }

  /**
   * 发送HTTP状态
   *
   * @param integer $code 状态码
   *
   * @return void
   */
  public static function sendStatusHeader($code)
  {
    static $httpStatusMap = array(
      // Success 2xx
      200 => 'OK',
      // Redirection 3xx
      301 => 'Moved Permanently',
      302 => 'Moved Temporarily ', // 1.1
      // Client Error 4xx
      400 => 'Bad Request',
      403 => 'Forbidden',
      404 => 'Not Found',
      // Server Error 5xx
      500 => 'Internal Server Error',
      503 => 'Service Unavailable',
    );

    if (isset($httpStatusMap[$code])) {
      header('HTTP/1.1 ' . $code . ' ' . $httpStatusMap[$code]);
      // 确保FastCGI模式下正常
      header('Status:' . $code . ' ' . $httpStatusMap[$code]);
    }
  }

  /**
   * 跳转
   *
   * @param $url
   * @param null $target
   * @return bool
   */
  public static function redirect($url, $target = null)
  {
    global $_F;
    if ($url == 'r') {
      $url = $_SERVER['HTTP_REFERER'];
    }

    if ($_F['in_ajax']) {
      self::output(array('result' => 'redirect', 'redirect_url' => $url, 'target' => $target));
      exit;
    }

    if ($target == 301) {
      self::sendStatusHeader(301);
      self::sendHeader('Location', $url); // 跳转到新地址
    } elseif ($target) {
      echo "<script> {$target}.location.href = '{$url}'; </script>";
    } else {
      header("location: " . $url);
    }

    exit;
  }

  /**
   * 刷新页面
   */
  public static function refresh()
  {
    self::redirect('r');
  }

  public function ajaxMsg($msg)
  {
    self::output(array('status' => 'ok', 'msg' => $msg));
  }

  public function ajaxSuccess($msg)
  {
    self::output(array('status' => 'ok', 'msg' => $msg));
  }

  public function ajaxError($msg)
  {
    if (is_string($msg)) {
      self::output(array('status' => 'error', 'msg' => $msg));
    } else {
      $msg['status'] = 'error';
      self::output($msg);
    }
  }

  public function ajaxResult($result)
  {
    self::output(array('status' => 'ok', 'result' => $result));
  }

}
