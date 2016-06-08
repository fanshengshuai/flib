<?php
/**
 *
 * 作者: 范圣帅(fanshengshuai@gmail.com)
 *
 * 创建: 2012-07-28 10:57:45
 * vim: set expandtab sw=4 ts=4 sts=4 *
 *
 * $Id: FView.php 766 2015-04-14 18:00:54Z fanshengshuai $
 */

if (!class_exists('Smarty')) {

    $smarty_class_file = FLIB_ROOT . '../smarty/Smarty.class.php';
    if (!file_exists($smarty_class_file)) {
        $smarty_class_file = FLIB_ROOT . 'smarty/Smarty.class.php';
    }

    require_once $smarty_class_file;
}

class FView_Smarty extends Smarty {
    public $msg_tpl = null;


    public function __construct() {
        global $_F;
        parent::__construct();

        $this->msg_tpl = "public/msg.tpl.php";

        if ($_F['module']) {

            $this->cache_dir = F_APP_ROOT . "data/cache/{$_F['module']}/";
            $this->compile_dir = F_APP_ROOT . "data/template/{$_F['module']}/";
            $this->template_dir = F_APP_ROOT . "modules/{$_F['module']}/tpl/";
            if (!file_exists($this->template_dir)) {
                $this->template_dir = F_APP_ROOT . "modules/{$_F['module']}/templates/";
                if (!file_exists($this->template_dir)) {
                    $this->template_dir = F_APP_ROOT . "modules/{$_F['module']}/templates/";
                }
            }
        } else {

            $this->cache_dir = F_APP_ROOT . "data/cache";
            $this->compile_dir = F_APP_ROOT . 'data/template/';
            $this->template_dir = F_APP_ROOT . 'tpl/';
            if (!file_exists($this->template_dir)) {
                $this->template_dir = F_APP_ROOT . "templates/";
                if (!file_exists($this->template_dir)) {
                    $this->template_dir = F_APP_ROOT . "template/";
                }
            }
        }

        if (defined('TPL_ROOT')) {
            $this->template_dir = TPL_ROOT;
        }

        $this->php_handling = Smarty::PHP_ALLOW;
        $this->caching = false;
        $this->debugging = false;
        $this->cache_lifetime = 300;

        $this->left_delimiter = "<{";
        $this->right_delimiter = "}>";
    }

    public function setTemplateDir($template_dir) {

        if (!$template_dir) {
            $template_dir = F_APP_ROOT . 'template/';
        }

        $this->template_dir = $template_dir;
    }

    public function __destruct() {

    }

    public function set($val, $value) {
        $this->assign($val, $value);
    }

    public function displaySysPage($tpl) {
        global $_F;

        if ($_F['run_in'] == 'shell') {
            $content = $this->getDebugInfo();
            echo $content;
            exit;
        } else {
            $this->template_dir = FLIB_ROOT . 'View/';
            $this->left_delimiter = "{";
            $this->right_delimiter = "}";
            $content = $this->fetch($tpl);
        }

        if ($_F['debug'] && !$_F['in_ajax']) {
            $content .= $this->getDebugInfo();
        }

        echo $content;
        exit;
    }

    public function disp($tpl = null) {
        global $_F;

        $tpl = $this->getDefaultTpl($tpl);

        if ($this->cache_id) {
            $contents = $this->load($tpl, $this->cache_id);
        } else {
            $contents = $this->load($tpl);
        }


        echo $contents;
    }

    private function getDefaultTpl($tpl = null) {
        global $_F;

        if (!$tpl) {
            if ($_F['app']) {
                $c = str_replace('Controller_' . ucfirst($_F['app']) . '_', '', $_F['controller']);
                $c = strtolower($c);
                $tpl = "{$_F['app']}/{$c}/{$_F['action']}";
            } else {
                $c = strtolower(str_replace('Controller_', '', $_F['controller']));
                $c = str_replace($_F['module'] . '_', '', $c);
                $a = $_F['action'];
                $a = preg_replace('#([A-Z])#e', "_\\1", $a);
                $a = strtolower($a);
                $tpl = "{$c}/{$a}";
            }

            $tpl .= '.tpl.php';
        }


        return $tpl;
    }

    public function load($tpl) {
        global $_F;


        $tpl = $this->getDefaultTpl($tpl);

        $this->set('_F', $_F);

        $view_compress = FConfig::get('global.output_compress');
        $contents = $this->fetch($tpl);

        if ($view_compress) {
            // 会有 http:// 这样的都替换没了
            $contents = preg_replace('#^\s*/' . '/.*$#im', '', $contents);
            $contents = preg_replace('#<!--.+?-->#si', '', $contents);
            $contents = preg_replace('/^\s+/im', '', $contents);
            $contents = preg_replace('/>\s+/im', '>', $contents);
            $contents = preg_replace('/\s*([{};,])\s*/im', '\1', $contents);
//            $contents = preg_replace('/\s+/im', ' ', $contents);
        }

        if ($_F['debug'] && !$_F['in_ajax']) {
            $contents .= FView::getDebugInfo();
        }

        return $contents;
    }

    public function setMsgTpl($msg_tpl) {
        $this->msg_tpl = $msg_tpl;
    }
}