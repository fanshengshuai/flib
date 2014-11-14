<?php

/**
 *
 * 作者: 范圣帅(fanshengshuai@gmail.com)
 *
 * 创建: 2011-04-19 09:08:23
 * vim: set expandtab sw=4 ts=4 sts=4 *
 *
 * $Id: App.php 46 2012-07-26 09:13:51Z fanshengshuai $
 */
class FApp {
    /**
     * +----------------------------------------------------------
     * 应用程序初始化
     * +----------------------------------------------------------
     * @access public
     * +----------------------------------------------------------
     * @return void
    +----------------------------------------------------------
     */
    static public function init() {
        global $_F;

        if (FConfig::get('global.debug')) {
            $_F['debug'] = true;
        }

        if (FConfig::get('global.session.type') == 'db') {

            $handler = new FSession();
            session_set_save_handler(
                array(&$handler, "open"),
                array(&$handler, "close"),
                array(&$handler, "read"),
                array(&$handler, "write"),
                array(&$handler, "destroy"),
                array(&$handler, "gc"));

            $handler->start();
        }
    }

    /**
     * +----------------------------------------------------------
     * 运行应用实例 入口文件使用的快捷方法
     * +----------------------------------------------------------
     * @access public
     * +----------------------------------------------------------
     * @return void
    +----------------------------------------------------------
     */
    static public function run() {
        global $_F;

        FApp::init();

        //if (FCache::check()) {
        //   echo FCache::getContent();
        //} else {
        FDispatcher::dispatch();
        //}
    }
}
