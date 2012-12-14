<?php
/**
 *
 * 作者: 范圣帅(fanshengshuai@gmail.com)
 *
 * 创建: 2012-08-11 21:34:59
 * vim: set expandtab sw=4 ts=4 sts=4 * 
 *
 * $Id: Global.php 330 2012-09-03 06:35:34Z www $
 */
class Controller_Admin_Global extends Controller_Admin_Abstract {

    public function settingAction() {
        global $_G;

        $setting_keys = array('site_title', 'site_keywords', 'site_description', 'phone', 'map_point', 'address', 'address_nav');

        if ($this->isPost()) {

            foreach ($setting_keys as $item) {
                Service_Setting::set($_G['cname'] . '_' . $item, trim($_POST[$item]));
            }

            if ($_G['is_base']) {
                Service_Setting::set('stat_code', trim($_POST['stat_code']));
            }
            $this->success('已经保存');
            exit;
        }

        foreach ($setting_keys as $item) {
            $value = Service_Setting::get($_G['cname'] . '_' . $item);
            $this->view->set($item, $value);
        }

        $stat_code = Service_Setting::get('stat_code');
        $this->view->set('stat_code', $stat_code);
        $this->view->disp('admin/global/setting');
    }

    public function passwdAction() {
        global $_G;

        $school_id = $_G['auth_info']['school_id'];

        if ($this->isPost()) {
            $new_passwd = trim($_POST['new_passwd']);
            //var_dump($new_passwd);exit;
            $schoolDAO = new DAO_School;
            $schoolDAO->update($school_id, array('password' => md5($new_passwd)));
            $this->success('密码已经修改，请牢记');
        }

        $this->view->disp('admin/global/passwd');
    }
}
