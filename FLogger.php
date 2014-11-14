<?php

/**
 *
 * 作者: 范圣帅(fanshengshuai@gmail.com)
 *
 * 创建: 2014-07-19 10:26:09
 * vim: set expandtab sw=4 ts=4 sts=4 *
 *
 * $Id: Log.php 11 2012-07-24 03:42:35Z fanshengshuai $
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

        list($this->date, $this->time) = explode(" ", date("Y-m-d H:i:s"));


        if ($log_type != null) $this->setLogType($log_type);
    }

    public function setLogType($log_type) {

        $this->log_file = APP_ROOT . "logs/{$log_type}/" . $this->date . ".log";

        FFile::mkdir(dirname($this->log_file));
    }

    /**
     * 追加日志
     *
     * @param $log_content
     * @throws Exception 日志类型
     */
    public function append($log_content) {
        $now = $this->date . " " . $this->time;

        $write_content = "{$now}\t{$log_content}\n";

        if (!$this->log_file) {
            throw new Exception("LOG TYPE NOT SET !!");
        }

        file_put_contents($this->log_file, $write_content, FILE_APPEND);
    }

    /**
     * +----------------------------------------------------------
     * 日志直接写入
     * +----------------------------------------------------------
     * @static
     * @access public
     * +----------------------------------------------------------
     *
     * @param string $message 日志信息
     * @param string $level 日志级别
     * @param int|string $type 日志记录方式
     * @param string $destination 写入目标
     * @param string $extra 额外参数
     * +----------------------------------------------------------
     *
     * @return void
    +----------------------------------------------------------
     */
    static function write($message, $level = self::LOG_LEVEL_ERR, $type = '', $destination = '', $extra = '') {
        $now = date("Y-m-d H:i:s");
        $type = $type ? $type : FConfig::get('logger.LOG_TYPE');
        if (self::LOG_TYPE_FILE == $type) { // 文件方式记录日志

            if (empty($destination))
                $destination = FConfig::get('logger.LOG_PATH') . date('Y-m-d') . '.log';

            FFile::mkdir(dirname($destination));

            //检测日志文件大小，超过配置大小则备份日志文件重新生成
            if (is_file($destination) && floor(FConfig::get('logger.LOG_FILE_SIZE')) <= filesize($destination))
                rename($destination, str_replace(basename($destination), date('Y-m-d.H_i_s') . '.log', $destination));

        } else {
            $destination = $destination ? $destination : '';
            $extra = $extra ? $extra : FConfig::get('logger.LOG_EXTRA');
        }

        error_log("{$now}\t" . $_SERVER['REQUEST_URI'] . "\t{$level}\t{$message}\r\n", $type, $destination, $extra);
    }
}
