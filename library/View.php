<?php

/**
 *
 * 作者: 范圣帅(fanshengshuai@gmail.com)
 *
 * 创建: 2012-07-28 10:57:45
 * vim: set expandtab sw=4 ts=4 sts=4 * 
 *
 * $Id: View.php 128 2012-08-06 08:58:30Z fanshengshuai $
 */
$smarty_class_file = SYS_ROOT . '../smarty/Smarty.class.php';
if (!file_exists($smarty_class_file)) {
    $smarty_class_file = SYS_ROOT . 'smarty/Smarty.class.php';
}

require_once $smarty_class_file;

class View extends Smarty {

    public function __construct() {
        parent::__construct();

        $this->cache_dir = APP_ROOT . "data/cache";
        $this->compile_dir = APP_ROOT . 'data/template/';
        $this->template_dir = APP_ROOT . 'templates/';

        $this->caching = false;
        $this->debugging = false;
        $this->cache_lifetime = 120;
    }

    public function set($val, $value) {
        $this->assign($val, $value);
    }

    public function displaySysPage($tpl) {
        global $_G;

        $this->template_dir = SYS_ROOT . 'View/';
        $contents = $this->fetch($tpl);

        echo $contents;
        exit;
    }

    public function disp($tpl) {
        $contents = $this->fetch($tpl . '.tpl');

        echo $contents;
    }

    public function fetch($tpl) {
        global $_G;

        $this->set('_G', $_G);

        $contents = parent::fetch($tpl);

        $view_compress = Config::get('view.compress');

        if ($view_compress) {
            $contents = preg_replace("/^\s*\/\/.*$/im", '', $contents);
            $contents = preg_replace('/>\s+/', '>', $contents);
            $contents = preg_replace('/\s+</', '<', $contents);
            $contents = preg_replace('/;\s+/', ';', $contents);
            $contents = preg_replace('/{\s+/', '{', $contents);
            $contents = preg_replace('/\s*}\s*/', '}', $contents);
            $contents = preg_replace('/<\!\-\-.+?\-\->/s', '', $contents);
        }

        if ($_G['debug']) {
            $contents .= $this->getDebugInfo();
        }


        return $contents . $debug_contents;
    }

    public function getDebugInfo() {
        global $_G;

        $debug_contents = '<style> .debug_table { margin-left:20px; border:1px solid rgb(0, 0, 0);} .debug_table th, .debug_table td { padding:5px; border:1px solid rgb(0, 0, 0); } </style>';

        // SQL DEBUG
        $debug_contents .= '<table class="debug_table" rules="none" cellspacing="0" cellpadding="5"><tr><td colspan="2">SQL：</td></tr>';
        foreach ($_G['debug_info']['sql'] as $key => $item) {
            $debug_contents .= "<tr><th>{$key}</th><td>{$item}</td></tr>";
        }
        $debug_contents .= '</table><br />';

        // SQL DEBUG
        $debug_contents .= '<table class="debug_table" rules="none" cellspacing="0" cellpadding="5"><tr><td colspan="2">COOKIES：</td></tr>';
        foreach ($_COOKIE as $key => $item) {
            $debug_contents .= "<tr><th>{$key}</th><td>{$item}</td></tr>";
        }
        $debug_contents .= '</table><br />';

        // $_G DEBUG
        $debug_g = $_G;
        unset($debug_g['debug_info']);
        $debug_contents .= '<table class="debug_table" rules="none" cellspacing="0" cellpadding="5"><tr><td colspan="2">$_G：</td></tr>';
        foreach ($debug_g as $key => $item) {
            $debug_contents .= "<tr><th>{$key}</th><td>" . var_export($item, true) . "</td></tr>";
        }
        $debug_contents .= '</table><br />';

        // FILE DEBUG
        $debug_contents .= '<table class="debug_table" rules="none" cellspacing="0" cellpadding="5"><tr><td colspan="2">引用文件：</td></tr>';
        foreach ($_G['debug_info']['autoload_files'] as $key => $item) {
            $debug_contents .= "<tr><th>{$key}</th><td>{$item}</td></tr>";
        }
        $debug_contents .= '</table>';

        return $debug_contents;
    }
}
