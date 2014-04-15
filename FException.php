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
        $this->view = new FView;
    }

    public function traceError($e) {

        //if ($e->code) { redirect('/e404'); }

        $exception_message = $e->getMessage()
            . '<br /> 异常出现在：' . $e->getFile() . '&nbsp;&nbsp;&nbsp;&nbsp; 第 ' . $e->getLine() . ' 行';
        $exception_trace = nl2br($e->__toString());

        $exception_message = str_replace(APP_ROOT, '', $exception_message);
        $exception_trace = str_replace(APP_ROOT, '', $exception_trace);

        $this->view->set('exception_message', $exception_message);
        $this->view->set('exception_trace', $exception_trace);
        $this->view->displaySysPage('exception.tpl');
    }
    public function printMessage($exception_message) {

    	header('HTTP/1.1 500 FLib Error');
    	header('status: 500 FLib Error');
        $this->view->set('exception_message', $exception_message);
        $this->view->displaySysPage('exception.tpl');
    }
}
