<?php

/**
 * 数据库方式Session驱动
 *    CREATE TABLE sys_session (
 *      session_id varchar(255) NOT NULL,
 *      session_expire int(11) NOT NULL,
 *      session_data blob,
 *      UNIQUE KEY `session_id` (`session_id`)
 *    );
 */
class FSession {

    /**
     * Session有效时间
     */
    protected $lifeTime = 0;

    /**
     * session保存的数据库名
     */
    protected $sessionTable = '';

    protected $sessionSavePath = '';
    protected $sessionName = '';

    public function start() {
        global $_F;

        // 先关闭session
        session_abort();

        $life_time = FConfig::get('global.session.life_time');
        $life_time = $life_time ? $life_time : ini_get('session.gc_maxlifetime');


        ini_set('session.name', 'sid');

        // 不使用 GET/POST 变量方式
        ini_set('session.use_trans_sid', 1);

        // 设置垃圾回收最大生存时间
        ini_set('session.gc_maxlifetime', $life_time);
        ini_set('session.gc_probability', 1);
        ini_set('session.gc_divisor', 100);

        // 使用 COOKIE 保存 SESSION ID 的方式
        ini_set('session.use_cookies', 1);

        ini_set('session.cookie_path', '/');
        // 多主机共享保存 SESSION ID 的 COOKIE,注意此处域名为一级域名
        if ($_F['cookie_domain']) {
            ini_set('session.cookie_domain', $_F['cookie_domain']);
        }

        $session_id = FRequest::getRequestString('session_id');
        if ($session_id) {
            session_id($session_id);
        }

        session_start();
    }

    /**
     * 打开Session
     * @access public
     * @param string $savePath
     * @param mixed $session_name
     * @return bool
     */
    public function open($savePath, $session_name) {
        global $_F;

        if (FConfig::get('global.session.type') != 'db') {
            $this->sessionSavePath = $savePath;
            $this->sessionName = $session_name;
        }


        $life_time = FConfig::get('global.session.life_time');
        $sessionTable = FConfig::get('global.session.table');
        $this->lifeTime = $life_time ? $life_time : ini_get('session.gc_maxlifetime');
        $this->sessionTable = $sessionTable ? $sessionTable : "sys_session";

        return true;
    }

    /**
     * 关闭Session
     * @access public
     */
    public function close() {
        return true;
    }

    /**
     * 读取Session
     * @access public
     * @param string $session_id
     * @return string
     */
    public function read($session_id) {

        $sql = "SELECT session_data AS data FROM " . $this->sessionTable . " WHERE session_id = '$session_id'   AND session_expire >" . time();
        $res = FDB::fetchFirst($sql);

        if ($res) {
            return $res['data'];
        }
        return "";
    }

    /**
     * 写入Session
     * @access public
     * @param string $session_id
     * @param String $session_data
     * @return bool
     */
    public function write($session_id, $session_data) {
        $expire = time() + $this->lifeTime;

        $sql = "REPLACE INTO  " . $this->sessionTable . " (  session_id, session_expire, session_data)  VALUES( '$session_id', '$expire',  '$session_data')";

        FDB::query($sql);

        return true;
    }

    /**
     * 删除Session
     * @access public
     * @param string $session_id
     * @return bool
     */
    public function destroy($session_id) {
        $sql = "DELETE FROM " . $this->sessionTable . " WHERE session_id = '$session_id'";
        FDB::query($sql);
        return true;
    }

    /**
     * Session 垃圾回收
     * @access public
     * @internal param string $sessionMaxLifeTime
     * @return int
     */
    public function gc() {
        $sql = "DELETE FROM " . $this->sessionTable . " WHERE session_expire < " . time();
        return FDB::query($sql);
    }

    /**
     * 设置 SESSION
     * @param $key
     * @param $value
     */
    public static function set($key, $value) {
        $_SESSION[$key] = $value;
    }

    /**
     * 获取 SESSION
     * @param $key
     * @return mixed
     */
    public static function get($key) {
        return $_SESSION[$key];
    }

    /**
     * 删除 SESSION
     * @param $key
     */
    public static function remove($key) {
        unset($_SESSION[$key]);
    }
}