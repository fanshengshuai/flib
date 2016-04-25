<?php

class Gather {
    public static function getSubStr($contents, $from, $end = '') {

        $from_pos = strpos($contents, $from);
        if ($from_pos) {
            $from_pos += strlen($from);
            $_tmp = substr($contents, $from_pos);
        }

        if (!$end) {
            return $_tmp;
        }

        $end_pos = strpos($_tmp, $end);
        if ($end_pos) {
            $_tmp_1 = substr($_tmp, 0, $end_pos);
        }

        return $_tmp_1;
    }

    public static function getFromWeb($url_pre) {
        global $_F;

        $uri = $_SERVER['REQUEST_URI'];

        if (
            strpos($uri, '.j')
            || strpos($uri, '.gif')
            || strpos($uri, '.png')
            || strpos($uri, '.css')
            || strpos($uri, '.js')
        ) {
            if ('/' == $uri[strlen($uri) - 1]) {
                $uri = $uri . "index.html";
            }

            if (!file_exists(APP_ROOT . 'public' . $uri)) {
                $uri_p = explode('/', trim($uri, '/'));
                unset($uri_p[count($uri_p) - 1]);

                $d = join('/', $uri_p);
                $d_r = APP_ROOT . 'public/' . $d;
                if (!file_exists($d_r)) {
                    mkdir($d_r, 0777, true);
                    chmod($d_r, 0777);
                }

                $content = file_GET_contents($url_pre . $uri);

                //$content = str_replace('www.ssyx.com.cn', 'www.zhihuikongjian.com', $content);

                $w_file = APP_ROOT . 'public/' . trim($uri, '/');
                file_put_contents($w_file, $content);
                echo $content;
                exit;
            }
        }
    }
}
