<?php

/**
 *
 * 作者: 范圣帅(fanshengshuai@gmail.com)
 *
 * 创建: 2014-07-19 10:26:09
 * vim: set expandtab sw=4 ts=4 sts=4 *
 *
 * $Id: FLogger.php 764 2015-04-14 15:09:06Z fanshengshuai $
 */
class FLogger {

    // 日志级别 从上到下，由低到高
    const LOG_LEVEL_EMERG = 'EMERG'; // 严重错误: 导致系统崩溃无法使用
    const LOG_LEVEL_ALERT = 'ALERT'; // 警戒性错误: 必须被立即修改的错误
    const LOG_LEVEL_CRIT = 'CRIT'; // 临界值错误: 超过临界值的错误，例如一天24小时，而输入的是25小时这样
    const LOG_LEVEL_ERR = 'ERR'; // 一般错误: 一般性错误
    const LOG_LEVEL_WARN = 'WARN'; // 警告性错误: 需要发出警告的错误
    const LOG_LEVEL_NOTICE = 'NOTIC'; // 通知: 程序可以运行但是还不够完美的错误
    const LOG_LEVEL_INFO = 'INFO'; // 信息: 程序输出信息
    const LOG_LEVEL_DEBUG = 'DEBUG'; // 调试: 调试信息
    const LOG_LEVEL_SQL = 'SQL'; // SQL：SQL语句 注意只在调试模式开启时有效

    // 日志记录方式
    const LOG_TYPE_SYSTEM = 0;
    const LOG_TYPE_MAIL = 1;
    const LOG_TYPE_FILE = 3;
    const LOG_TYPE_SAPI = 4;

    // 日志信息
    static $log = array();

    private $log_file = null;
    private $date = null;
    private $time = null;

    public function __construct($log_type = null) {

        $this->logType = $log_type;
    }

    /**
     * 追加日志
     *
     * @param $log_content
     *
     * @throws Exception 日志类型
     */
    public function append($log_content) {

        self::write($log_content, $this->logType);
    }

    /**
     * +----------------------------------------------------------
     * 日志直接写入
     * +----------------------------------------------------------
     * @static
     * @access   public
     * +----------------------------------------------------------
     *
     * @param string $message 日志信息
     * @param int|string $type 日志记录方式
     * @param string $level 日志级别
     *
     * @return void
     */
    static function write($message, $type = 'common', $level = self::LOG_LEVEL_INFO) {
        global $_F;

        if (is_array($message)) {
            $message = json_encode($message);
        }

        $now = date("Y-m-d H:i:s");

        $log_file_size = FConfig::get('logger.LOG_FILE_SIZE');
        $log_file_size = $log_file_size ? $log_file_size : 1024000;

        $file_log_path = FConfig::get('logger.LOG_PATH');
        $file_log_path = $file_log_path ? $file_log_path : F_APP_ROOT . 'data/logs/';

//        $file_log_path = $file_log_path;

        if ($_F['run_in'] == 'shell') {
            $file_log_path .= $_F['run_in'] . '/';
        } elseif ($_F['module']) {
            $file_log_path .= $_F['module'] . '/';
        }

        if ($type) {
            $file_log_path .= "{$type}/";
        }

        $file_log_path .= date('Y-m-d') . '.log';

        FFile::mkdir(dirname($file_log_path));

        if (is_file($file_log_path) && floor($log_file_size) <= filesize($file_log_path))
            rename($file_log_path, str_replace(basename($file_log_path), date('Y-m-d.H_i_s') . '.log', $file_log_path));

        $write_content = "{$now}\t{$level}";

        if ($_SERVER['REQUEST_URI']) {
            $write_content .= "\tURL:http:/" . "/{$_F['http_host']}{$_F['uri']}\t" . ($_F['refer'] ? "REFER:{$_F['refer']}" : '');
//        } else {
//            $write_content .= "\t{$_F['run_in']}\t{$_F['module']}";
        }

        $write_content .= "\t{$message}\r\n";

        file_put_contents($file_log_path, $write_content, FILE_APPEND);
    }
}
