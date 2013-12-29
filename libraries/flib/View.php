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
$smarty_class_file = FLIB_ROOT . '../smarty/Smarty.class.php';
if (!file_exists($smarty_class_file)) {
    $smarty_class_file = FLIB_ROOT . 'smarty/Smarty.class.php';
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
        $this->cache_lifetime = 300;
    }

    public function set($val, $value) {
        $this->assign($val, $value);
    }

    public function displaySysPage($tpl) {
        global $_G;

        $this->template_dir = FLIB_ROOT . 'View/';
        $content = $this->fetch($tpl);

        if (!in_array(RUN_MODE, array('web', 'sync'))) {
            $content = str_replace(array('<br />', '</p>', '</tr>'), "\n", $content);
            $content = str_replace(array('&nbsp;'), " ", $content);
            $content = preg_replace('/<head>.+?<\/head>/si', '', $content);
            $content = preg_replace('/<.+?>/', '', $content);
            $content = preg_replace("/\n\s+/i", "\n", $content);
        }

        if ($_G['debug'] && !$_G['in_ajax']) {
            $content .= $this->getDebugInfo();
        }

        echo $content;
        exit;
    }

    public function disp($tpl = '') {
        global $_G;

        if (!$tpl) {
            if ($_G['app']) {
                $c = str_replace('Controller_' . ucfirst($_G['app']) . '_', '', $_G['controller']);
                $c = strtolower($c);
                $tpl = "{$_G['app']}/{$c}/{$_G['action']}";
            }
        }

        if ($this->cache_id) {
            $contents = $this->load($tpl . '.tpl', $this->cache_id);
        } else {
            $contents = $this->load($tpl . '.tpl');
        }

        if (!$_G['uid']) {
            FCache::save($contents);
        }

        echo $contents;
    }

    public function load($tpl) {
        global $_G;

        $this->set('_G', $_G);

        $view_compress = Config::get('view.compress');
        $contents = $this->fetch($tpl);

        if ($view_compress) {
            // 会有 http:// 这样的都替换没了
            //$contents = preg_replace('#//.*$#im', '', $contents);
            $contents = preg_replace('#<!--.+?-->#si', '', $contents);
            $contents = preg_replace('/^\s+/im', '', $contents);
            $contents = preg_replace('/>\s+/im', '>', $contents);
        }

        if ($_G['debug'] && !$_G['in_ajax']) {
            $contents .= $this->getDebugInfo();
        }

        return $contents;
    }

    public function getDebugInfo() {
        global $_G;


        if (RUN_MODE == 'cli') {
            $debug_contents = "DEBUG INFO:\n";
        } else {
            $debug_contents = '<style> .debug_table { margin-left:20px; border:1px solid rgb(0, 0, 0);} .debug_table th, .debug_table td { padding:5px; border:1px solid rgb(0, 0, 0); } </style>';
        }

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

        if (RUN_MODE == 'cli') {
            $debug_contents = str_replace('</tr>', "\n", $debug_contents);
            $debug_contents = preg_match('/<.+?>/', '', $debug_contents);
        }

        return "<div class=\"debug_info\">" . $debug_contents . "</div>";
    }
}
