<?php
/**
 *
 * 作者: 范圣帅(fanshengshuai@gmail.com)
 * 时间: 2012-08-02 16:00:51
 *
 * vim: set expandtab sw=4 ts=4 sts=4
 * $Id: Index.php 134 2012-08-07 09:34:52Z fanshengshuai $
 */
class Controller_Index extends Controller_Abstract {

    public function defaultAction() {
        global $_G;

        $this->view->set('cur_nav', 'index');
        $this->view->disp('index');
    }
}