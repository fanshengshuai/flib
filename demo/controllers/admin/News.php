<?php

class Controller_Admin_News extends Controller_Admin_Abstract {

    public function listAction() {

        $where='status=1';
        if($_POST){
            if(trim($_POST['title1'])!='') {
                $where.=" and title like '%".$_POST['title1']."'";
            }
            $this->view->set('title',$_POST['title1']);
        }

        $newsDAO=new DAO_News();
        $list = $newsDAO->listByPage($_GET['page'], array('news_id' => 'desc'),$where);

        $this->view->set('list', $list['data']);
        $this->view->set('cur_nav', 'news');
        $this->view->set('pager', $list['page_option']['html']);
        $this->view->disp('admin/news/list');
    }

    public function addAction(){

        $news_id = intval($_GET['news_id']);
        if($news_id){
            $newsDAO = new DAO_News();
            $news_info=$newsDAO->find("news_id='{$news_id}'");
            $this->view->set('news_info', $news_info);
        }

        $categoryDAO=new DAO_Category();
        $cate_list = Service_Category::get();
        $this->view->set('cate_list', $cate_list);

        $this->view->disp('admin/news/add');
    }

    public function saveAction(){

        $news_id = intval($_POST['news_id']);

        $require_fields = array('title');
        $this->_ajaxCheckNullPostItems($require_fields);

        if(!$_POST['description']){
            $this->error(array('description' => '描述不能为空！'));
        }

        $data['title']=$_POST['title'];
        $data['description']=$_POST['description'];
        $data['content'] = $_POST['content'];

        $data['cid'] = intval($_POST['cid']);


        $newsDAO=new DAO_News();
        if($news_id) {
            $newsDAO->update($news_id, $data);
        } else {

            $data['click_time']=1;
            $news_id = $newsDAO->add($data);
        }

        $photo_file = "news/{$news_id}.jpg";
        $uploader = new Uploader;
        $upload_file = $uploader->saveAttach('pic_url', $photo_file);
        if ($upload_file) {
            $newsDAO->update($news_id, array('pic_url' => $photo_file));

            /*
            $photo_file_abs = APP_ROOT . "www/attachs/" . $photo_file;
            $image = new Image;
            $image->thumb($photo_file_abs, $photo_file_abs, 180, 240, 2);
             */
        }
        $this->_ajaxSuccessMessage('新闻更新成功！','/admin/news/list');
    }

    public function deleteAction(){

        if($_GET['news_id']){
            $newsDAO=new DAO_News();
            $newsDAO->delete($_GET['news_id']);
            $this->_ajaxSuccessMessage('新闻删除成功！','/admin/news/list');
        }
    }

}
