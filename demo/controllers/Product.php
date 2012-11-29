<?php
/**
 *
 * 作者: 范圣帅(fanshengshuai@gmail.com)
 * 时间: 2012-08-02 16:00:51
 *
 * vim: set expandtab sw=4 ts=4 sts=4
 * $Id: Index.php 134 2012-08-07 09:34:52Z fanshengshuai $
 */
class Controller_Product extends Controller_Abstract {

    public function zhantingAction() {
        global $_G;

        $this->pid = 19;
        $this->listAction();
    }

    public function hudongAction() {
        $this->pid = 4;
        $this->listAction();
    }

    public function xuanjiaoAction() {
        $this->pid = 6;
        $this->listAction();
    }

    public function junshiAction() {
        $this->pid = 7;
        $this->listAction();
    }

    public function yingjiAction() {
        $this->pid = 8;
        $this->listAction();
    }

    public function listAction() {
        global $_G;

        $cid = intval($_GET['cid']);

        if (!$cid) {
            $cid = $this->pid;
        }
        $sub_pid = $cid;

        $cateDAO = new DAO_Category;
        $cate_info = $cateDAO->get($cid);

        if ($cate_info['pid']) {
            $top_cate_info = $cateDAO->get($cate_info['pid']);
            $cate_list = $cateDAO->findAll("pid='{$cate_info['pid']}' and status=1");
            // 如果参数是子分类，只查子分类
            $cid_sub=$cid;
            $cid=0;
        } else {
            $top_cate_info = $cate_info;
            $cate_list = $cateDAO->findAll("pid='{$cate_info['cid']}' and status=1");
        }


        $this->view->set('top_cate_info', $top_cate_info);

        $this->view->set('cate_info', $cate_info);
        $this->view->set('cate_list', $cate_list);

        $condition = "status=1 and (cid='{$cid}' or cid_sub='{$cid_sub}')";
        $productDAO = new DAO_Product;
        $product_list = $productDAO->listByPage($_GET['page'], array('pid' => 'desc'), $condition);
        $this->view->set('product_list', $product_list);


        $this->view->disp('product/zhanting');
    }

    public function viewAction() {
        global $_G;

        $pid = intval($_GET['pid']);

        $productDAO = new DAO_Product;
        $p_info = $productDAO->get($pid);


        $cid = $p_info['cid'];

        $cateDAO = new DAO_Category;

        $cate_info = $cateDAO->get($cid);
        $this->view->set('cate_info', $cate_info);

        $cate_list = $cateDAO->findAll("pid='{$cid}'");
        $this->view->set('cate_list', $cate_list);

        $this->view->set('p_info', $p_info);
        $this->view->disp('product/view');
    }
}

