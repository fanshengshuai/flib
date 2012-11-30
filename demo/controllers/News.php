<?php
/**
 *
 * 作者: 范圣帅(fanshengshuai@gmail.com)
 * 时间: 2012-08-02 16:00:51
 *
 * vim: set expandtab sw=4 ts=4 sts=4
 * $Id: Index.php 134 2012-08-07 09:34:52Z fanshengshuai $
 */
class Controller_News extends Controller_Abstract {

    public function listAction() {
        global $_G;

        $cid = intval($_GET['cid']);
        if (!$cid) {
            $cid = 1;
        }

        $this->view->set('cid', $cid);

        $where = "cid='{$cid}' and status=1";

        $newsDAO = new DAO_News;
        $news_list = $newsDAO->listByPage($_GET['page'], array('news_id' => 'desc'),$where);

        $this->view->set('news_list', $news_list);
        $this->view->set('cur_nav', 'index');
        $this->view->disp('news/list');
    }
    public function viewAction() {
        global $_G;

        $news_id = intval($_GET['news_id']);

        $newsDAO = new DAO_News;
        $news_info = $newsDAO->find("news_id='{$news_id}'");

        $this->view->set('news_info', $news_info);
        $this->view->set('cur_nav', 'index');
        $this->view->disp('news/view');
    }
}
