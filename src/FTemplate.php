<?php

class FTemplate {
    public $debug = 0;
    protected $view_path_param;
    private $vars = array();
    private $conf = '';
    private $tpl_suffix = '.html';//如果CONFIG没配置默认后缀 则显示
    private $tpl_compile_suffix = '.tpl.php';//编译模板路径
    private $template_tag_left = '{';//模板左标签
    private $template_tag_right = '}';//模板右标签
    private $template_c = '';//编译目录
    private $template_path = '';//模板完整路径 
    private $template_name = '';//模板名称 index.html


    // 定义每个模板的标签的元素
    private $tag_foreach = array('from', 'item', 'key');

    public function __construct($conf = array('template_c' => 'data/template_c/')) {
        $this->conf = &$conf;

        $this->template_c = $this->conf['template_c'];//编译目录
        $this->_tpl_suffix = $this->tpl_suffix();
    }


    private function str_replace($search, $replace, $content) {
        if (empty($search) || empty($replace) || empty($content)) return false;
        return str_replace($search, $replace, $content);
    }

    /**
     * preg_match_all
     * @param $pattern string 正则
     * @param $content string 内容
     * @return array
     * @throws Exception
     */
    private function preg_match_all($pattern, $content) {
        if (empty($pattern) || empty($content)) $this->error('查找模板标签失败!');
        preg_match_all("/" . $this->template_tag_left . $pattern . $this->template_tag_right . "/is", $content, $match);
        return $match;
    }

    /**
     * 模板文件后缀
     */
    public function tpl_suffix() {
        $tpl_suffix = empty($this->conf['template_suffix']) ?
            $this->tpl_suffix :
            $this->conf['template_suffix'];
        return $tpl_suffix;
    }

    /**
     *  此处不解释了
     * @param $key
     * @param $value
     */
    public function assign($key, $value) {
        $this->vars[$key] = $value;
    }

    /**
     *  渲染页面
     * @param string $filename
     * @param string $view_path
     * @throws Exception
     * param $ 使用方法 1*  使用方法 1
     *  $this->view->display('error', 'comm/');
     *  默认是指向TPL模版的跟目录，所以comm/就是 tpl/comm/error.html
     *  使用方法 2
     *  $this->view->display('error_file');
     *  默认指向控制器固定的文件夹
     *  例如你的域名是 http://xxx/admin/index, 那么正确路径就是tpl/admin/index/error_file.html
     */
    public function display($tpl) {

        if ($tpl[0] == '/') {
            $tpl_file = $tpl;
            $compiled_file = F_APP_ROOT.$this->template_c . 'o/' . md5($tpl) . ".tpl.php";
        } else {
            $tpl_file = $this->conf['tpl_path_root'] . $tpl . ".tpl.php";
            $compiled_file = F_APP_ROOT . $this->template_c . $tpl . ".tpl.php";
        }

        if ($this->debug) {
            $this->compile($tpl_file, $compiled_file);
        }

        $tpl_file_mtime = -1;
        extract($this->vars);
        ob_start();
        include($compiled_file);

        if ($tpl_file_mtime != filemtime($tpl_file)) {
            $this->compile($tpl_file, $compiled_file);
            ob_clean();
            include $compiled_file;
        }
    }

    /**
     * @param $content string 模板文件主体
     * @param string $save_file 保存目录，相对于data目录
     * @return string 模板文件主体
     */
    public function compile($tpl_file, $save_file = "", $isSubTpl = false) {
        $content = file_get_contents($tpl_file);
        $compiled_content = $this->parse($content);

        $header_comment = "Create On##" . time() . "|Compiled from##" . $this->template_path . $this->template_name;
        $str = "<? if(!defined('FLIB')) exit('Access Denied'); global \$_F; ";
        if (!$isSubTpl) {
            $str .= "\$tpl_file_mtime = " . intval(filemtime($tpl_file)) . ";";
        }

        $str .= "/*{$header_comment}*/ ?>$compiled_content";

        if ($save_file) {
            FFile::save($save_file, $str);
        }

        return $str;
    }

    /**
     *  开始解析相关模板标签
     * @param $content string 模板内容
     * @return string 模板内容
     */
    public function parse($content) {
        //foreach
        $content = $this->parse_foreach($content);

        //include
        $content = $this->parse_include($content);

        //if
        $content = $this->parse_if($content);

        //elseif
        $content = $this->parse_elseif($content);

        //模板标签公用部分
        $content = $this->parse_comm($content);

        //转为PHP代码
        $content = $this->parse_php($content);
        return $content;
    }

    /**
     * 转换为PHP
     * @param $content string 模板内容
     * @return string 替换好的HTML
     */
    private function parse_php($content) {
        if (empty($content)) return false;
        $content = preg_replace("/" . $this->template_tag_left . "([\$\\d\\w_]+?)" . $this->template_tag_right . "/i", "<?php echo $1; ?>", $content);

        return $content;
    }

    /**
     * if判断语句
     * <{if empty($zhang)}>
     * zhang
     * <{elseif empty($liang)}>
     *  liang
     * <{else}>
     *  zhangliang
     * <{/if}>
     * @param $content
     * @return bool|mixed
     */
    private function parse_if($content) {
        if (empty($content)) return false;

        $match = $this->preg_match_all("if\s+(.*?)", $content);
        if (!isset($match[1]) || !is_array($match[1])) return $content;

        foreach ($match[1] as $k => $v) {
            $content = str_replace($match[0][$k], "<?php if({$v}) { ?>", $content);
        }

        return $content;
    }

    private function parse_elseif($content) {
        if (empty($content)) return false;
        $match = $this->preg_match_all("elseif\s+(.*?)", $content);
        if (!isset($match[1]) || !is_array($match[1])) return $content;

        foreach ($match[1] as $k => $v) {
            $content = str_replace($match[0][$k], "<?php } elseif ({$v}) { ?>", $content);
        }

        return $content;
    }

    /**
     * 解析 include 标签不是实时更新的  当主体文件更新的时候 才更新标签内容，所以想include生效 请修改一下主体文件
     * 使用方法 <{include file="aaa.tpl.php"}>
     * @param $content string 模板内容
     * @return string html
     */
    private function parse_include($content) {
        if (empty($content)) return false;

        $match = $this->preg_match_all("include\s+['\"](.*?)['\"]", $content);
        if (!isset($match[1]) || !is_array($match[1])) return $content;

        foreach ($match[1] as $match_key => $subTpl) {
            $conf_view_tpl = $this->conf['tpl_path_root'] . $subTpl;
            if (is_file($conf_view_tpl)) {
                $tpl_file = $conf_view_tpl;
                $compiled_file = F_APP_ROOT . $this->template_c . $subTpl;
                $this->compile($tpl_file, $compiled_file, true);
                $content = str_replace($match[0][$match_key], '<?php include("' . $compiled_file . '"); ?>', $content);
            } else {
                $this->error('模板文件不存在:' . $conf_view_tpl);
            }
        }

        return $content;
    }

    /**
     * 解析 foreach
     * 使用方法 <{foreach from=$lists item=value key=kk}>
     * @param $content string 模板内容
     * @return string 解析后的内容
     */
    private function parse_foreach($content) {
        if (empty($content)) return false;

        $match = $this->preg_match_all("foreach\s+(.*?)", $content);
        if (!isset($match[1]) || !is_array($match[1])) return $content;

        foreach ($match[1] as $match_key => $value) {

            $split = preg_split("/\s+/is", $value);
            $split = array_filter($split);

            $new_tag = array();
            foreach ($split as $v) {
                $a = explode("=", $v);
                if (in_array($a[0], $this->tag_foreach)) {//此处过滤标签 不存在过滤
                    $new_tag[$a[0]] = $a[1];
                }
            }
            $key = '';

            extract($new_tag);
            $key = ($key) ? '$' . $key . ' => ' : '';
            $s = '<?php foreach(' . $from . ' as ' . $key . '$' . $item . ') { ?>';
            $content = $this->str_replace($match[0][$match_key], $s, $content);
        }

        return $content;
    }

    /**
     * 匹配结束 字符串
     */
    private function parse_comm($content) {
        $search = array(
            "/" . $this->template_tag_left . "\/foreach" . $this->template_tag_right . "/is",
            "/" . $this->template_tag_left . "\/if" . $this->template_tag_right . "/is",
            "/" . $this->template_tag_left . "else" . $this->template_tag_right . "/is",

        );

        $replace = array(
            "<?php } ?>",
            "<?php } ?>",
            "<?php } else { ?>"
        );
        $content = preg_replace($search, $replace, $content);
        return $content;
    }

    /**
     *  写文件
     * @param  string $filename 文件名
     * @param  string $content 模板内容
     * @param $dir
     * @return string 文件名
     * @throws Exception
     */
    private function compile_file($filename, $content, $dir) {
        if (empty($filename)) $this->error("{$filename} Creation failed");

        $content = $this->body_content($content);//对文件内容操作
        //echo '开始编译了=====';
        $f = $dir . $filename . $this->tpl_compile_suffix;

        //$this->check_file_limits($f, 'w');
        if (($fp = @fopen($f, 'wb')) === false) {
            $this->error($f . '<br/>编译文件失败，请检查文件权限.');
        }
        //开启flock
        flock($fp, LOCK_EX + LOCK_NB);
        fwrite($fp, $content, strlen($content));
        flock($fp, LOCK_UN + LOCK_NB);
        fclose($fp);

        return $f;
    }

    public function __destruct() {
        $this->vars = null;
        $this->view_path_param = null;
    }


    private function error($msg) {
        echo $msg;
        exit;
    }
}
