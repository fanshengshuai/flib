<?php

/**
 *
 * 作者: 范圣帅(fanshengshuai@gmail.com)
 *
 * 创建: 2012-08-07 20:51:53
 * vim: set expandtab sw=4 ts=4 sts=4 * 
 *
 * $Id$
 */
class Controller_Admin_Abstract extends Controller {

    public function __construct() {
        global $_G;

        parent::__construct();

        // 不需要登录的
        $no_auth_controllers = array('Controller_Auth', 'Controller_Index');

        if (in_array($_G['controller'], $no_auth_controllers)) {
            $this->checkAuth();
        }
    }

    public function checkAuth() {
        global $_G;

        $auth_cookie = Cookie::get('auth');

        list($uid, $auth_str) = explode("\t", $auth_cookie);

        // todo 认证判断，请补充完整判断条件
        if (!$uid || !$auth_str) {
            redirect('/login');
        }

        $_G['uid'] = $uid;
    }
}
