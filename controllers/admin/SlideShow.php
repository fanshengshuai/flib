<?php

class Controller_Admin_SlideShow extends Controller_Admin_Abstract {

    public function listAction() {
        global $_G;

        $slideShowDAO = new DAO_SlideShow;

        if ($this->isPost()) {
            $display_order = $_POST['display_order'];

            foreach ($display_order as $key => $value) {
                $slideShowDAO->update($key, array('display_order' => $value));
            }
            redirect('/admin/slideShow/list');
        }

        $slideShow_list = $slideShowDAO->listByPage($_GET['page'], array('display_order' => 'asc'), "status = 1");

        $this->view->set('slide_show_list', $slideShow_list['data']);
        $this->view->set('pager', $slideShow_list['page_option']['html']);

        $this->view->disp('admin/slide_show/list');
    }


    public function addAction() {
        global $_G;

        $this->view->disp('admin/slide_show/add');
    }

    public function modifyAction() {
        global $_G;

        $pic_id = intval($_GET['pic_id']);

        $slideShowDAO = new DAO_SlideShow;
        $slide_show_info = $slideShowDAO->get($pic_id);
        //var_dump($slideShow_info);

        $this->view->set('slide_show_info', $slide_show_info);
        $this->view->disp('admin/slide_show/add');
    }

    public function saveAction() {
        global $_G;

        $check_fields = array('url');

        $this->_ajaxCheckNullPostItems($check_fields);

        $pic_info_fileds = array('url', 'content', 'display_order');

        $pic_info = array();
        foreach ($pic_info_fileds as $item) {
            if ($_POST[$item]) {
                $pic_info[$item] = $_POST[$item];
            }
        }

        $pic_id = intval($_POST['pic_id']);

        $slideShowDAO = new DAO_SlideShow;
        if ($pic_id) {
            $slideShowDAO->update($pic_id, $pic_info);
        } else {
            $pic_id = $slideShowDAO->add($pic_info);
        }


        $upload_file = Uploader::saveFile('pic_url', 'slide_show');
        $slideShowDAO->update($pic_id, array('pic_url' => $upload_file));

        //$photo_file_abs = APP_ROOT . "www/attachs/" . $photo_file;

        //$image = new Image;
        //$image->thumb($photo_file_abs, $photo_file_abs, 1000, 400, 2);

        $this->success('资料已经修改。', '/admin/slideShow/list');
    }


    public function deleteAction() {
        $pic_id = intval($_GET['pic_id']);

        $slideShowDAO = new DAO_SlideShow;
        $slideShow_info = $slideShowDAO->delete($pic_id);

        $this->success('已经删除', '/admin/slideShow/list');
    }

}
