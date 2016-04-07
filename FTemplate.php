<?php

class FTemplate {
    private $vars = array();
    private $conf = '';
    private $tpl_name = 'index';//如果模板不存在 会查找当前 controller默认index模板
    private $tpl_suffix = '.html';//如果CONFIG没配置默认后缀 则显示
    private $tpl_compile_suffix = '.tpl.php';//编译模板路径
    private $template_tag_left = '<{';//模板左标签
    private $template_tag_right = '}>';//模板右标签
    private $template_c = '';//编译目录
    private $template_path = '';//模板完整路径 
    private $template_name = '';//模板名称 index.html


    //定义每个模板的标签的元素
    private $tag_foreach = array('from', 'item', 'key');
    private $tag_include = array('file');//目前只支持读取模板默认路径

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
     */
    public function assign($key, $value) {
        $this->vars[$key] = $value;
    }

    /**
     *  渲染页面
     * @param string $filename
     * @param string $view_path
     * @throws Exception
     * @internal param $ 使用方法 1*  使用方法 1
     *  $this->view->display('error', 'comm/');
     *  默认是指向TPL模版的跟目录，所以comm/就是 tpl/comm/error.html
     *  使用方法 2
     *  $this->view->display('error_file');
     *  默认指向控制器固定的文件夹
     *  例如你的域名是 http://xxx/admin/index, 那么正确路径就是tpl/admin/index/error_file.html
     */
    public function display($filename = '', $view_path = '') {
        $tpl_path_arr = $this->get_tpl($filename, $view_path);//获取TPL完整路径 并且向指针传送路径以及名称
        if (!$tpl_path_arr) $this->error($filename . $this->_tpl_suffix . '模板不存在');

        //编译开始
        $this->view_path_param = $view_path;//用户传递过来的模版跟目录
//        $this->compile();
    }

    /**
     *  编译控制器
     */
    private function compilexxx() {
        $file_path = $this->template_path . $this->template_name;
        $compile_dirpath = $this->check_temp_compile();
        $vars_template_c_name = str_replace($this->_tpl_suffix, '', $this->template_name);


        $include_file = $this->template_replace($this->read_file($file_path), $compile_dirpath, $vars_template_c_name);//解析
        if ($include_file) {
            $this->read_config() && $config = $this->read_config();
            extract($this->vars, EXTR_SKIP);
            @include $include_file;
        }
    }

    /**
     * 读取当前项目配置文件
     */
    protected function read_config() {
        if (file_exists(SYSTEM_PATH . 'conf/config.php')) {
            $config = "";
            @include SYSTEM_PATH . 'conf/config.php';
            return $config;
        }

        return false;
    }

    /**
     *  解析模板语法
     * @param $str 内容
     * @param $compile_dirpath 模版编译目录
     * @param $vars_template_c_name 模版编译文件名
     * @return 编译过的PHP模板文件名
     * @throws Exception
     */
    private function template_replace($str, $compile_dirpath, $vars_template_c_name) {
        if (empty($str)) $this->error('模板内容为空！');

        //处理编译头部
        $compile_path = $compile_dirpath . $vars_template_c_name . $this->tpl_compile_suffix;//编译文件

        if (is_file($compile_path)) {
            $tpl_file_update_time = filemtime($this->template_path . $this->template_name);
            $compiled_file_update_time = filemtime($compile_path);

            if ($tpl_file_update_time > $compiled_file_update_time) {
                $ret_file = $this->compile_file($vars_template_c_name, $str, $compile_dirpath);
            } else {
                $ret_file = $compile_path;
            }
        } else {
            $ret_file = $this->compile_file($vars_template_c_name, $str, $compile_dirpath);
        }

        return $ret_file;
    }


    /**
     * @param $content string 模板文件主体
     * @param string $save_file 保存目录，相对于data目录
     * @return string 模板文件主体
     */
    public function compile($content, $save_file = "") {
        $str = $this->parse($content);

        $header_comment = "Create On##" . time() . "|Compiled from##" . $this->template_path . $this->template_name;
        $str = "<? if(!defined('FLIB')) exit('Access Denied');/*{$header_comment}*/?>$str";

        if ($save_file) {
            $save_file = F_APP_ROOT . 'data/' . $save_file;
            FFile::save($save_file, $str);
        }

        return $str;
    }

    /**
     *  开始解析相关模板标签
     * @param $content 模板内容
     * @return bool|html|mixed|模板内容
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
     * echo 如果默认直接<{$config['domain']}> 转成 <?php echo $config['domain']?>
     */
    private function parse_echo($content) {

    }

    /**
     * 转换为PHP
     * @param $content html 模板内容
     * @return html 替换好的HTML
     */
    private function parse_php($content) {
        if (empty($content)) return false;
        $content = preg_replace("/" . $this->template_tag_left . "(.+?)" . $this->template_tag_right . "/is", "<?php echo $1 ?>", $content);

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
     */
    private function parse_if($content) {
        if (empty($content)) return false;
        //preg_match_all("/".$this->template_tag_left."if\s+(.*?)".$this->template_tag_right."/is", $content, $match);

        $match = $this->preg_match_all("if\s+(.*?)", $content);
        if (!isset($match[1]) || !is_array($match[1])) return $content;

        foreach ($match[1] as $k => $v) {
            //$s = preg_split("/\s+/is", $v);
            //$s = array_filter($s);
            $content = str_replace($match[0][$k], "<?php if({$v}) { ?>", $content);
        }

        return $content;
    }

    private function parse_elseif($content) {
        if (empty($content)) return false;
        //preg_match_all("/".$this->template_tag_left."elseif\s+(.*?)".$this->template_tag_right."/is", $content, $match);
        $match = $this->preg_match_all("elseif\s+(.*?)", $content);
        if (!isset($match[1]) || !is_array($match[1])) return $content;

        foreach ($match[1] as $k => $v) {
            //$s = preg_split("/\s+/is", $v);
            //$s = array_filter($s);
            $content = str_replace($match[0][$k], "<?php } elseif ({$v}) { ?>", $content);
        }

        return $content;
    }

    /**
     * 解析 include    include标签不是实时更新的  当主体文件更新的时候 才更新标签内容，所以想include生效 请修改一下主体文件
     * 记录一下 有时间开发一个当DEBUG模式的时候 每次执行删除模版编译文件
     * 使用方法 <{include file="www.phpddt.com"}>
     * @param $content 模板内容
     * @return html
     */
    private function parse_include($content) {
        if (empty($content)) return false;

        //preg_match_all("/".$this->template_tag_left."include\s+(.*?)".$this->template_tag_right."/is", $content, $match);
        $match = $this->preg_match_all("include\s+(.*?)", $content);
        if (!isset($match[1]) || !is_array($match[1])) return $content;

        foreach ($match[1] as $match_key => $match_value) {
            $a = preg_split("/\s+/is", $match_value);

            $new_tag = array();
            //分析元素
            foreach ($a as $t) {
                $b = explode('=', $t);
                if (in_array($b[0], $this->tag_include)) {
                    if (!empty($b[1])) {
                        $new_tag[$b[0]] = str_replace("\"", "", $b[1]);
                    } else {
                        $this->error('模板路径不存在!');
                    }
                }
            }

            extract($new_tag);
            //查询模板文件
            foreach ($this->conf['view_path'] as $v) {
                $conf_view_tpl = $v . $file;//include 模板文件
                if (is_file($conf_view_tpl)) {
                    $c = $this->read_file($conf_view_tpl);


                    $inc_file = str_replace($this->_tpl_suffix, '', basename($file));

                    $this->view_path_param = dirname($file) . '/';
                    $compile_dirpath = $this->check_temp_compile();

                    $include_file = $this->template_replace($c, $compile_dirpath, $inc_file);//解析

                    break;
                } else {
                    $this->error('模板文件不存在,请仔细检查 文件:' . $conf_view_tpl);
                }
            }

            $content = str_replace($match[0][$match_key], '<?php include("' . $include_file . '")?>', $content);
        }

        return $content;

    }

    /**
     * 解析 foreach
     * 使用方法 <{foreach from=$lists item=value key=kk}>
     * @param $content 模板内容
     * @return html 解析后的内容
     */
    private function parse_foreach($content) {
        if (empty($content)) return false;

        //preg_match_all("/".$this->template_tag_left."foreach\s+(.*?)".$this->template_tag_right."/is", $content, $match);
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
            $key = ($key) ? '$' . $key . ' =>' : '';
            $s = '<?php foreach(' . $from . ' as ' . $key . ' $' . $item . ') { ?>';
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
     *  检查编译目录  如果没有创建 则递归创建目录
     * @param  string $path 文件完整路径
     * @return 模板内容
     */
    private function check_temp_compile() {
        //$paht = $this->template_c.

        $tpl_path = ($this->view_path_param) ? $this->view_path_param : $this->get_tpl_path();
        $all_tpl_apth = $this->template_c . $tpl_path;

        if (!is_dir($all_tpl_apth)) {
            FFile::mkdir($tpl_path);
        }

        return $all_tpl_apth;
    }

    /**
     *  读文件
     * @param  string $path 文件完整路径
     * @return 模板内容
     */
    private function read_file($path) {
        //$this->check_file_limits($path, 'r');

        if (($r = @fopen($path, 'r')) === false) {
            $this->error('模版文件没有读取或执行权限，请检查！');
        }
        $content = fread($r, filesize($path));
        fclose($r);
        return $content;
    }

    /**
     *  写文件
     * @param  string $filename 文件名
     * @param  string $content 模板内容
     * @param $dir
     * @return 文件名
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

    /**
     * 这个检查文件权限函数 暂时废弃了
     * @param  [$path] [路径]
     * @param string $status
     * @throws Exception
     */
    public function check_file_limits($path, $status = 'rw') {
        clearstatcache();
        if (!is_writable($path) && $status == 'w') {
            $this->error("{$path}<br/>没有写入权限，请检查.");
        } elseif (!is_readable($path) && $status == 'r') {
            $this->error("{$path}<br/>没有读取权限，请检查.");
        } elseif ($status == 'rw') {//check wirte and read
            if (!is_writable($path) || !is_readable($path)) {
                $this->error("{$path}<br/>没有写入或读取权限，请检查");
            }
        }


    }


    /**
     *  获取模板完整路径 并返回已存在文件
     * @param  string $filename 文件名
     * @param  string $view_path 模板路径
     * @return
     */
    private function get_tpl($filename, $view_path) {
        empty($filename) && $filename = $this->tpl_name;

        //遍历模板路径
        foreach ($this->conf['view_path'] as $path) {
            if ($view_path) {//直接从tpl跟目录找文件
                $tpl_path = $path . $view_path;
                $view_file_path = $tpl_path . $filename . $this->_tpl_suffix;
            } else {//根据目录，控制器，方法开始找文件
                $view_file_path = ($tpl_path = $this->get_tpl_path($path)) ? $tpl_path . $filename . $this->_tpl_suffix : exit(0);
            }

            if (is_file($view_file_path)) {
                //向指针传送模板路径和模板名称
                $this->template_path = $tpl_path;//
                $this->template_name = $filename . $this->_tpl_suffix;
                return true;
            } else {
                $this->error($filename . $this->_tpl_suffix . '模板不存在');
            }
        }
    }

    /**
     *  获取模板路径
     * @param  string $path 主目录
     * @return URL D和M的拼接路径
     */
    private function get_tpl_path($path = '') {
        core::get_directory_name() && $path_arr[0] = core::get_directory_name();
        core::get_controller_name() && $path_arr[1] = core::get_controller_name();
//        (is_array($path_arr)) ? $newpath = implode('/', $path_arr) : $this->error('获取模板路径失败!');

//        return $path . $newpath . '/';
    }


    public function __destruct() {
        $this->vars = null;
        $this->view_path_param = null;
    }


    private function error($msg) {
        $this->error($msg);
    }
}