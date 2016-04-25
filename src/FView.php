<?php

/**
 * 作者: 范圣帅(fanshengshuai@gmail.com)
 *
 * 创建: 2012-07-28 10:57:45
 * vim: set expandtab sw=4 ts=4 sts=4 *
 */
class FView {

    /**
     * @var FView_Smarty
     */
    protected $engine;
    public $msg_tpl = "public/msg.tpl.php";


    public function __construct() {
        global $_F;

        $this->tpl_engine = FConfig::get("global.tpl_engine");
        !$this->tpl_engine && $this->tpl_engine = "php";
        if ($this->tpl_engine == 'tiny') {
            require FLIB_ROOT . 'tplEngine/tiny.php';
            $this->engine = new FView_Tiny;
        } elseif ($this->tpl_engine == 'smarty') {
            require FLIB_ROOT . 'tplEngine/smarty.php';
            $this->engine = new FView_Smarty;
        } else {
            require FLIB_ROOT . 'tplEngine/orig.php';
            $this->engine = new FView_Orig;
        }
    }

    public function __destruct() {
    }

    public function assign($var, $value) {
        $this->engine->assign($var, $value);
    }

    public function display($tpl = null) {
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
                $tpl = "{$c}/{$a}";
            }
        }

        $tpl_file = F_APP_ROOT . 'tpl/' . $tpl . ".tpl.php";
        if (!file_exists($tpl_file))
            throw new Exception("模版文件[" . 'tpl/' . $tpl . "]不存在!");

        $this->engine->display($tpl);
    }

    public function displaySysPage($tpl) {
        global $_F;

        if ($_F['run_in'] == 'shell') {
            $content = $this->getDebugInfo();
            echo $content;
            exit;
        } else {
            $this->template_dir = FLIB_ROOT . 'View / ';
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

    public
    function load($tpl) {
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
            $contents .= $this->getDebugInfo();
        }

        return $contents;
    }

    public
    function getDebugInfo() {
        global $_F;

        unset($_F['db']);


        if ($_F['run_in'] == 'shell') {
            $debug_contents = "DEBUG INFO:\n";
        } else {
            $debug_contents = '<style>
            .debug_info { clear: both; position: relative; margin-top:300px; }
            .debug_table { border-collapse: collapse;margin:20px; border:1px solid #000;} .debug_table th, .debug_table td { padding:5px; border:1px solid #000; } </style>';
        }

        // SQL DEBUG
        if ($_F['debug_info']['sql']) {

            $debug_contents .= '<table class="debug_table" rules="none" cellspacing="0" cellpadding="5"><tr><td colspan="2">SQL：</td></tr>';
            foreach ($_F['debug_info']['sql'] as $key => $item) {
                if (is_array($item)) {
                    $debug_contents .= "<tr><th>{$key}</th><td>{$item['sql']}<br/><pre>" .
                        var_export($item['params'], true) . "</pre></td></tr>";

                } else {
                    $debug_contents .= "<tr><th>{$key}</th><td>{$item}</td></tr>";
                }
            }
            $debug_contents .= '</table>';
        }

        // COOKIES DEBUG
        $debug_contents .= '<table class="debug_table" rules="none" cellspacing="0" cellpadding="5"><tr><td colspan="2">COOKIES：</td></tr>';
        foreach ($_COOKIE as $key => $item) {
            $debug_contents .= "<tr><th>{$key}</th><td>{$item}</td></tr>";
        }
        $debug_contents .= '</table>';

        // ERRORS
        if ($_F['errors']) {

            $debug_contents .= '<table class="debug_table" rules="none" cellspacing="0" cellpadding="5"><tr><td colspan="2"><span style="background: #ff0000; color: #fff; padding:5px;"> ERRORS：</span></td></tr>';
            foreach ($_F['errors'] as $key => $item) {
                foreach ($item as $skey => $sItem) {
                    $debug_contents .= "<tr><th>{$key}</th><td>{$sItem}</td></tr>";
                }
            }
            $debug_contents .= '</table>';
            unset($_F['errors']);
        }


        // $_F DEBUG
        $debug_F = $_F;
        unset($debug_F['debug_info']);
        $debug_contents .= '<table class="debug_table" rules="none" cellspacing="0" cellpadding="5"><tr><td colspan="2">$_F：</td></tr>';
        foreach ($debug_F as $key => $item) {
            if (is_string($item)) {
                $item_text = $item;
            } else {
                $item_text = '' . var_export($item, true) . '';
            }
            $debug_contents .= "<tr><th>{$key}</th><td><pre>" . $item_text . "</pre></td></tr>";
        }
        $debug_contents .= '</table>';

        // FILE DEBUG
        $debug_contents .= '<table class="debug_table" rules="none" cellspacing="0" cellpadding="5"><tr><td colspan="2">引用文件：</td></tr>';
        foreach ($_F['debug_info']['autoload_files'] as $key => $item) {
            $key_show = $key + 1;
            $debug_contents .= "<tr><th>{$key_show}</th><td>{$item}</td></tr>";
        }
        $debug_contents .= '</table>';

        if ($_F['run_in'] == 'shell') {
            $debug_contents = str_replace('</tr>', "\n", $debug_contents);
            $debug_contents = preg_match('/<.+?>/', '', $debug_contents);
        }

        return "<div class=\"debug_info clearfix\">" . $debug_contents . "</div>";
    }

//    public function setMsgTpl($msg_tpl) {
//        $this->msg_tpl = $msg_tpl;
//    }
}
