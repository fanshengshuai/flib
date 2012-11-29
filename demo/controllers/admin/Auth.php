<?php

/**
 *
 * 作者: 范圣帅(fanshengshuai@gmail.com)
 *
 * 创建: 2012-08-05 16:16:17
 * vim: set expandtab sw=4 ts=4 sts=4 * 
 *
 * $Id$
 */
class Controller_Admin_Auth extends Controller_Admin_Abstract {

    public function loginAction() {
        global $_G;

        if ($this->isPost()) {
            $username = trim($_POST['username']);
            $password = trim($_POST['password']);

            $auth_info = Service_Auth::checkUser($username, $password);
            if ($auth_info) {
                $auth_str = md5("{$auth_info['username']}|{$auth_info['password']}");
                Cookie::set('auth', "{$auth_info['uid']}\t{$auth_str}");
                redirect("/admin/main/index");
            }
        }

        $this->view->disp('admin/auth/login');
    }

    public function logoutAction() {
        Cookie::set('auth', "", -1);
        redirect('/');
    }
}
