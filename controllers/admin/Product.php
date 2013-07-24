<?php

class Controller_Admin_Product extends Controller_Admin_Abstract {

    public function beforeAction() {
        parent::beforeAction();

        $cid = intval($_GET['cid']);
        $this->view->set('cur_nav', $cid);
    }

    public function listAction() {
        global $_G;

        $cid = intval($_GET['cid']);

        if (!$cid) {
            $cid = 1;
        }

        $this->view->set('cid', $cid);

        $condition = "status=1 and (cid='{$cid}' or cid_sub='{$cid}')";
        $productDAO = new DAO_Product;
        $product_list = $productDAO->listByPage($_GET['page'], array('pid' => 'desc'), $condition);
        $this->view->set('product_list', $product_list);

        $this->view->disp('admin/product/list');
    }

    public function addAction() {
        $cid = intval($_GET['cid']);

        if (!$cid) {
            $cid = 1;
        }

        $this->view->set('cid', $cid);
        $this->view->disp('admin/product/add');
    }

    public function modifyAction() {
        global $_G;

        $pid = intval($_GET['pid']);
        $productDAO = new DAO_Product;
        $p_info = $productDAO->get($pid);

        $this->view->set('cid', $p_info['cid']);
        $this->view->set('p_info', $p_info);
        $this->view->disp('admin/product/add');
    }

    public function saveAction(){
        global $_G;

        $require_fields = array('title');
        $this->_ajaxCheckNullPostItems($require_fields);

        $data_fileds = array('title', 'cid', 'description', 'extra_1', 'content');
        $data = $this->getPostData($data_fileds);

        $cateDAO = new DAO_Category;
        $cate_info = $cateDAO->get($data['cid']);

        if ($cate_info['pid']) {
            $data['cid_sub'] = $data['cid'];
            $data['cid'] = $cate_info['pid'];
        }

        $pid = intval($_POST['pid']);
        $productDAO = new DAO_Product;
        if ($pid) {
            $productDAO->update($pid, $data);
        } else {
            $pid = $productDAO->add($data);
        }

        $upload_file = Uploader::saveFile('pic_url');

        //var_dump($upload_file);exit;
        if ($upload_file) {
            $productDAO->update($pid, array('pic_url' => $upload_file));

            $photo_file_abs = APP_ROOT . "public/attachs/" . $upload_file;
            $image = new Image;
            $image->thumb($photo_file_abs, $photo_file_abs, 250, 130, 2);
        }

        $this->success('保存成功！', '/admin/product/list?cid=' . $data['cid']);
    }

    public function deleteAction(){

        $pid = intval($_GET['pid']);
        if($pid){
            $productDAO = new DAO_Product;
            $productDAO->delete($pid);
            $url = $_SERVER['HTTP_REFERER'];
            $this->_ajaxSuccessMessage('删除成功！', $url);
        }
    }
}
