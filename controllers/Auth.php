<?php
/**
 *
 * 作者: 范圣帅(fanshengshuai@gmail.com)
 * 时间: 2012-08-02 16:00:51
 *
 * vim: set expandtab sw=4 ts=4 sts=4
 * $Id: Index.php 134 2012-08-07 09:34:52Z fanshengshuai $
 */
class Controller_Auth extends Controller_Abstract {

    public function loginAction() {
        global $_G;

        if ($this->isPost()) {
            $not_null_fileds = array('username', 'password');
            $this->_ajaxCheckNullPostItems($not_null_fileds);

            // 取用户输入的帐号信息
            $username = addslashes(trim($_POST['username']));
            $password = addslashes(trim($_POST['password']));

            // 判断是否没有输入
            if (!$username || !$password) {
                $this->showMessage('用户名或密码不能为空。','/auth/login');
            }

            // todo 登录成功处理，请补充完整
            if ($username == 'admin' && $password == 'admin') {
                Cookie::set('auth', "1\t" . md5($password), 99999);
                $this->success('登陆成功', '/');
            } else {
                $this->error(array('password' => '密码错误'));
            }
        }

        $this->view->set('cur_nav', 'index');
        $this->view->disp('auth/login');
    }
}
