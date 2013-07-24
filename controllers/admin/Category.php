<?php

class Controller_Admin_Category extends Controller_Admin_Abstract {

    public function listAction() {
        global $_G;

        $ctype = intval($_GET['ctype']);

        $this->view->set('ctype', $ctype);

        $cate_list = Service_Category::get();
        //var_dump($cate_list);

        $this->view->set('cate_list', $cate_list);

        $pid = intval($_GET['pid']);

        $this->view->set('pid', $pid);

        $this->view->set('cur_nav', 'category');
        $this->view->disp('admin/category/list');
    }

    public function addAction() {

        global $_G;

        $cid = intval($_REQUEST['cid']);

        $ctype = intval($_GET['ctype']);
        $cateDAO = new DAO_Category;


        if ($this->isPost()) {
            $fields = array('category_name', 'pid', 'ctype');

            $data = $this->getPostData($fields);

            if ($cid) {

                // 父分类不能和当前分类 ID 一样
                if ($data['pid'] == $cid) {
                    $this->error(array('pid' => '父分类不能和当前分类相同'));
                }

                $cateDAO->update($cid, $data);
            } else {
                $cateDAO->add($data);
            }

            $this->success('分类操作成功。', '/admin/category/list');
        }

        $category_info = $cateDAO->get($cid);
        $this->view->set('category_info', $category_info);

        $cate_list = $cateDAO->findAll('status=1 and pid=0');

        $this->view->set('ctype', $ctype);

        $this->view->set('cate_list', $cate_list);
        $this->view->disp('admin/category/add');
    }
}
