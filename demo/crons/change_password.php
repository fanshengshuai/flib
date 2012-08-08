<?php

/**
 *
 * 作者: 范圣帅(fanshengshuai@gmail.com)
 *
 * 创建: 2012-07-27 00:40:47
 * vim: set expandtab sw=4 ts=4 sts=4 * 
 *
 * $Id: change_password.php 112 2012-08-02 08:26:56Z www $
 */
define('APP_ROOT', dirname(dirname(__FILE__)) . '/');
define('RUN_MODE', 'cli');

require_once APP_ROOT . "libraries/flib/Flib.php";

$userService = new Service_User;

$userService->changePassword(1, '1');

