<?php

/**
 *
 * 作者: 范圣帅(fanshengshuai@gmail.com)
 * 时间: 2012-08-02 16:00:43
 *
 * vim: set expandtab sw=4 ts=4 sts=4
 * $Id: Admin.php 223 2012-08-12 13:48:57Z fanshengshuai $
 */
class Controller_Admin extends Controller_Abstract {

    public function defaultAction() {
        global $_G;

        redirect('/admin/main/index');
    }
}
