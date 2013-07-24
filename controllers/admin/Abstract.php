<?php

/**
 *
 * 作者: 范圣帅(fanshengshuai@gmail.com)
 *
 * 创建: 2012-08-07 20:51:53
 * vim: set expandtab sw=4 ts=4 sts=4 * 
 *
 * $Id$
 */
class Controller_Admin_Abstract extends Controller {

    public function __construct() {
        global $_G;

        parent::__construct();

    }

    public function beforeAction() {
        global $_G;

        if ($_G['controller'] != 'Controller_Admin_Auth') {
            //$this->checkAuth();

            // 检查权限
            //$this->checkRole();
        }
    }


    protected function checkAuth() {
        global $_G;

        $auth_str = Cookie::get('auth');
        list($uid, $auth) = explode("\t", $auth_str);

        if (!$auth) {
            redirect('/admin/auth/login');
        }


        $check_auth = md5("admin|tenghuivu168");

        if ($check_auth == $auth) {
            $_G['auth_info'] = array('');;
            return true;
        } else {
            redirect('/admin/auth/login');
        }
    }

    protected function checkRole() {
        global $_G;

        $common_models = array(
            array('c' => 'global', 'a' => 'setting', 'title' => '设置'),
            //array('c' => 'union', 'a' => '*', 'title' => '设置'),
            //array('c' => 'training', 'a' => '*', 'title' => '培训项目'),
            array('c' => 'course', 'a' => '*', 'title' => '开课'),
            array('c' => 'teacher', 'a' => '*', 'title' => '老师'),
            array('c' => 'baoming', 'a' => '*', 'title' => '报名'),
            array('c' => 'slideShow', 'a' => '*', 'title' => '报名'),
            array('c' => 'main', 'a' => 'index', 'title' => '后台首页'),
            //array('c' => 'works', 'a' => '*', 'title' => '作品'),
            //array('c' => 'teaching', 'a' => '*', 'title' => '环境'),
            //array('c' => 'about', 'a' => '*', 'title' => '关于'),
            //array('c' => 'news', 'a' => '*', 'title' => '新闻'),
        );

        $admin_models = array(
            //array('c' => 'school', 'a' => '*', 'title' => '分校管理'),
            //array('c' => 'investor', 'a' => '*', 'title' => '投资者关系'),
            //array('c' => 'partner', 'a' => '*', 'title' => '合作关系'),
            //array('c' => 'job', 'a' => '*', 'title' => '招聘'),
            //array('c' => 'team', 'a' => '*', 'title' => '团队'),
            //array('c' => 'employ', 'a' => '*', 'title' => '就业'),
            //array('c' => 'certification', 'a' => '*', 'title' => '认证'),
        );


        $admin_models = array_merge($common_models, $admin_models);
        $check_c = strtolower($_G['controller']);
        $check_c = str_replace('controller_admin_', '', $check_c);

        // 检查搜保护的模块
        if ($_G['is_school']) {
            foreach ($admin_models as $item) {
                $allow_cs[] = strtolower($item['c']);
            }

            if (!in_array($check_c, $allow_cs)) {
                $this->showMessage('没有权限');
            }
        }
    }

    public function showMessage($message) {
        $this->view->set('message', $message);
        $this->view->disp('admin/message');
        exit;
    }

    public function successMessage($message) {
        $this->view->set('message', $message);
        $this->view->disp('admin/message');
        exit;
    }

    public function errorMessage($message) {
        $this->view->set('message', $message);
        $this->view->disp('admin/message');
        exit;
    }
}
