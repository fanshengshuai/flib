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

        if ($_G['cname'] != 'www') {
            $_G['is_school'] = true;
        }
    }
}
