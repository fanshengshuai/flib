<?php

/**
 *
 * 作者: 范圣帅(fanshengshuai@gmail.com)
 *
 * 创建: 2012-08-05 16:16:30
 * vim: set expandtab sw=4 ts=4 sts=4 * 
 *
 * $Id$
 */
class Controller_Admin_Main extends Controller_Admin_Abstract {

    public function indexAction() {

        $this->view->disp('admin/main/index');
    }
}
