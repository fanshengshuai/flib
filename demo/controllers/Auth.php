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
			$this->success('登陆成功', '/');
		}

        $this->view->set('cur_nav', 'index');
        $this->view->disp('auth/login');
    }
}