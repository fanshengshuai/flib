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
                $auth_str = md5("{$auth_info['school_id']}|{$auth_info['username']}|{$auth_info['password']}");
//                var_dump($auth_str);
                //var_dump(time());
                Cookie::set('auth', "{$auth_info['school_id']}\t{$auth_str}");
                //exit;
                redirect("http://{$auth_info['cname']}.{$_G['top_domain']}/admin/main/index");
            }
        }

        $this->view->disp('admin/auth/login');
    }

    public function logoutAction() {
        Cookie::set('auth', "anjoyo", -1);
        redirect('/');
    }
}
