<?php
/*
//获取创建线程的父线程id
Thread::getCreatorId 
//获取当前线程id
Thread::getCurrentThreadId
//获取当前线程引用
Thread::getCurrentThread 
//将线程加入检测
Thread::join
//查看线程是否被检测（是否被join）
Thread::isJoined
//强行杀死线程
Thread::kill
--------------------- 
作者：Gavin_new 
来源：CSDN 
原文：https://blog.csdn.net/gavin_new/article/details/65444190 
版权声明：本文为博主原创文章，转载请附上博文链接！
 */
class FThread
{

    public $hooks = array();
    public $args  = array();

    public function thread()
    {

    }

    public function addthread($func)
    {
        $args          = array_slice(func_get_args(), 1);
        $this->hooks[] = $func;
        $this->args[]  = $args;
        return true;
    }

    public function runthread()
    {
        if (isset($_GET['flag'])) {
            $flag = intval($_GET['flag']);
        }
        if ($flag || $flag === 0) {
            call_user_func_array($this->hooks[$flag], $this->args[$flag]);
        } else {
            for ($i = 0, $size = count($this->hooks); $i < $size; $i++) {
                $fp = fsockopen($_SERVER['HTTP_HOST'], $_SERVER['SERVER_PORT']);
                if ($fp) {
                    $out = "GET {$_SERVER['PHP_SELF']}?flag=$i HTTP/1.1rn";
                    $out .= "Host: {$_SERVER['HTTP_HOST']}rn";
                    $out .= "Connection: Closernrn";
                    fputs($fp, $out);
                    fclose($fp);
                }
            }
        }
    }
}
