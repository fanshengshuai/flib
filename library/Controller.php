<?php

/**
 *
 * 作者: 范圣帅(fanshengshuai@gmail.com)
 * 时间: 2010-11-02 01:12:12
 *
 * vim: set expandtab sw=4 ts=4 sts=4
 * $Id: Controller.php 86 2012-07-30 09:30:42Z yanjianshe $
 */

class Controller {
    protected $view;

    public function setView() {
        $this->view = new View;
    }

    public function defaultAction() {
        die('FSS Tips: this is the defaultAction, 这是默认的Action，请实现此方法保证显示正确内容');
    }

    public function __construct() {
        $this->setView();
    }
    protected function isPost() {
        if ($_POST) {
            return true;
        }

        return false;
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
            echo json_encode(array('result' => 'failed', 'items' => $check_results));exit;
        }
        return true;
    }

    /**
     * 成功提示
     * $items = array('username' => '已经存在', 'password' => '长度不够');
     */
    protected function _ajaxSuccessMessage($items, $url = '') {

        $result = array('result' => 'success');

        if ($url) {
            $result['url'] = $url;
        }

        if (is_array($items)) {
            $result['items'] = $items;
        } else {
            $result['message'] = $items;
        }

        ob_clean();
        header("Content-Type: application/json; charset=UTF-8");
        //echo '{"result":"failed","items":{"real_name":"\u4e0d\u80fd\u4e3a\u7a7a","username":"\u4e0d\u80fd\u4e3a\u7a7a","id_card":"\u4e0d\u80fd\u4e3a\u7a7a","phone":"\u4e0d\u80fd\u4e3a\u7a7a","email":"\u4e0d\u80fd\u4e3a\u7a7a","good_at":"\u4e0d\u80fd\u4e3a\u7a7a","join_date":"\u4e0d\u80fd\u4e3a\u7a7a","comment":"\u4e0d\u80fd\u4e3a\u7a7a","teacher_grade":"\u4e0d\u80fd\u4e3a\u7a7a"}}';
        //exit;
        echo json_encode($result);exit;
    }

    /*
     * 检查是否是数字
     */
    protected function _ajaxCheckIsNum($num_fields){
        $check_results = null;

        foreach ($num_fields as $item) {
            if (!is_numeric($_POST[$item])) {
                $check_results[$item] = '必须是数字！';
            }
        }

        if ($check_results) {
            echo json_encode(array('result' => 'failed', 'items' => $check_results));exit;
        }
        return true;
    }

    /*
     * 检查下拉列表的选中值是否为-1
     */

    protected  function  _ajaxCheckSelect($select_fields){

        $check_results = null;

        foreach($select_fields as $item ){
            if($_POST[$item]==-1)
                $check_results[$item] = '请选择下拉列表';
        }

        if($check_results){
            echo json_encode(array('result' => 'failed', 'items' => $check_results));exit;
        }
        return true;
    }
    protected function _ajaxErrorMessage($items) {

        $result = array('result' => 'failed');

        if (is_array($items)) {
            $result['items'] = $items;
        } else {
            $result['message'] = $items;
        }

        ob_clean();
        echo json_encode($result);exit;
    }

    public function ajaxRedirect($url) {
        $result = array('result' => 'redirect', 'url' => $url);

        ob_clean();
        echo json_encode($result);exit;
    }

    public function success($message, $url = '') {
        global $_G;

        if ($_G['in_ajax']) {
            $this->_ajaxSuccessMessage($message, $url);
        } else {
            $this->showMessage($message, $url);
        }
    }

    public function error($message) {
        global $_G;

        if ($_G['in_ajax']) {
            $this->_ajaxErrorMessage($message);
        } else {
            $this->showMessage($message, $url);
        }
    }


    /**
     * 取出POST内容
     */
    protected function getPostData($form_fields) {

        $post_data = array();
        foreach ($form_fields as $item) {
            if ($_POST[$item]) {
                $post_data[$item] = $_POST[$item];
            }
        }

        return $post_data;
    }
}
