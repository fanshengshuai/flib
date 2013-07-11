<?php

/**
 *
 * 作者: 范圣帅(fanshengshuai@gmail.com)
 *
 * 创建: 2012-07-24 10:29:39
 * vim: set expandtab sw=4 ts=4 sts=4 * 
 *
 * $Id: FException.php 11 2012-07-24 03:42:35Z fanshengshuai $
 */
class FException extends Exception {

    public function __construct() {
        $this->view = new View;
    }

    public function traceError($e) {

        //if ($e->code) { redirect('/e404'); }

        $exception_message = $e->getMessage() . '<br /> 异常出现在：' . $e->getFile() . '&nbsp;&nbsp;&nbsp;&nbsp; 第 ' . $e->getLine() . ' 行';
        $exception_trace = nl2br($e->__toString());

        $this->view->set('exception_message', $exception_message);
        $this->view->set('exception_trace', $exception_trace);
        $this->view->displaySysPage('exception.tpl');
    }
    public function printMessage($exception_message) {

        $this->view->set('exception_message', $exception_message);
        $this->view->displaySysPage('exception.tpl');

        echo $contents;exit;
    }
}
