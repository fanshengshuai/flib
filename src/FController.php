<?php

/**
 *
 * 作者: 范圣帅(fanshengshuai@gmail.com)
 * 时间: 2010-11-02 01:12:12
 *
 * vim: set expandtab sw=4 ts=4 sts=4
 * $Id: FController.php 764 2015-04-14 15:09:06Z fanshengshuai $
 */
abstract class FController {
    protected $view;

    protected function showMessage($message, $msgType, $url = null) {
        if ($this->view->msg_tpl) {
            $this->assign('msg', $message);
            $this->assign('msgType', $msgType);
            $this->assign('url', $url);
            $this->display($this->view->msg_tpl);
        } else {
            echo $message;
            if ($url) {
                echo "<script> setTimeout(function() { location = $url; },2000); </script>";
            }
        }

        exit;
    }

    public function indexAction() {
        die('FSS Tips: this is the indexAction, 这是默认的Action，请实现此方法保证显示正确内容');
    }

    public function __construct() {
        $this->view = new FView;
    }

    protected function isPost() {
        return FRequest::isPost();
    }

    /**
     * 检查空项目
     */
    protected function _ajaxCheckNullPostItems($not_null_fields) {

        $check_results = null;

        foreach ($not_null_fields as $item) {
            if (!$_POST[$item]) {
                $check_results[$item] = '不能为空';
            }
        }

        if ($check_results) {
            echo json_encode(array('result' => 'failed', 'items' => $check_results));
            exit;
        }
        return true;
    }

    /**
     * 成功提示
     * $items = array('username' => '已经存在', 'password' => '长度不够');
     */
    protected function _ajaxSuccessMessage($items, $url = '', $close_time = 0) {

        $result = array('result' => 'success', 'close_time' => $close_time);

        if ($url) {
            $result['url'] = $url;
        }

        if (is_array($items)) {
            $result['items'] = $items;
        } else {
            $result['content'] = $items;
        }

        ob_clean();
        //header("Content-Type: application/json; charset=UTF-8");
        //echo '{"result":"failed","items":{"real_name":"\u4e0d\u80fd\u4e3a\u7a7a","username":"\u4e0d\u80fd\u4e3a\u7a7a","id_card":"\u4e0d\u80fd\u4e3a\u7a7a","phone":"\u4e0d\u80fd\u4e3a\u7a7a","email":"\u4e0d\u80fd\u4e3a\u7a7a","good_at":"\u4e0d\u80fd\u4e3a\u7a7a","join_date":"\u4e0d\u80fd\u4e3a\u7a7a","comment":"\u4e0d\u80fd\u4e3a\u7a7a","teacher_Frade":"\u4e0d\u80fd\u4e3a\u7a7a"}}';
        //exit;
        echo json_encode($result);
        exit;
    }

    /**
     * 检查是否是数字
     */
    protected function _ajaxCheckIsNum($num_fields) {
        $check_results = null;

        foreach ($num_fields as $item) {
            if (!is_numeric($_POST[$item])) {
                $check_results[$item] = '必须是数字！';
            }
        }

        if ($check_results) {
            echo json_encode(array('result' => 'failed', 'items' => $check_results));
            exit;
        }
        return true;
    }

    /**
     * 检查下拉列表的选中值是否为-1
     */
    protected function _ajaxCheckSelect($select_fields) {

        $check_results = null;

        foreach ($select_fields as $item) {
            if ($_POST[$item] == -1)
                $check_results[$item] = '请选择下拉列表';
        }

        if ($check_results) {
            echo json_encode(array('result' => 'failed', 'items' => $check_results));
            exit;
        }
        return true;
    }

    protected function _ajaxErrorMessage($items) {

        $result = array('result' => 'failed');

        if (is_array($items)) {
            $result['items'] = $items;
        } else {
            $result['msg'] = $items;
        }

        ob_clean();
        echo json_encode($result);
        exit;
    }

    public function ajaxRedirect($url) {
        $result = array('result' => 'redirect', 'url' => $url);

        ob_clean();
        echo json_encode($result);
        exit;
    }

    protected function success($message, $url = '', $close_time = 0) {
        global $_F;

        if ($url == 'r')
            $url = $_SERVER['HTTP_REFERER'];

        if ($_F['in_ajax'])
            $this->_ajaxSuccessMessage($message, $url, $close_time);
        else
            $this->showMessage($message, 'success', $url);

        return 1;
    }

    protected function error($message, $url = '') {
        global $_F;

        if ($url == 'r')
            $url = $_SERVER['HTTP_REFERER'];

        if ($_F['in_ajax'])
            $this->_ajaxErrorMessage($message);
        else
            $this->showMessage($message, 'error', $url);

        return -1;
    }

    /**
     * 取出POST内容
     */
    protected function checkPostData($items) {

        global $_F;

        if ($_F['in_ajax']) {
            $this->_ajaxCheckNullPostItems($items);
        } else {
            $check_results = null;

            foreach ($items as $item) {
                if (!$_POST[$item]) {
                    $check_results .= $item . '不能为空 <br />';
                }
            }

            if ($check_results) {
                $this->error($check_results);
                exit;
            }
        }
    }

    /**
     * 取出POST内容
     */
    protected function getPostData($form_fields, $force = false) {

        $post_data = array();
        foreach ($form_fields as $item) {
            if ($force || $_POST[$item]) {
                $post_data[$item] = $_POST[$item];
            }
        }

        return $post_data;
    }

    protected function load($tpl = null) {
        if ($this->tpl_engine == 'smarty')
            return $this->view->load($tpl);
        else {
            ob_clean();
            $this->display($tpl);
            $content = ob_get_clean();
            return $content;
        }
    }

    protected function display($tpl = null) {
        $this->view->display($tpl);
    }

    protected function assign($var, $value) {
        $this->view->assign($var, $value);
    }

    protected function ajaxReturn($mix) {
        FResponse::output($mix);

        return true;
    }

    protected function openDebug() {
        global $_F;

        $_F['debug'] = 1;
    }
}
