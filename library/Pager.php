<?php

/**
 *
 * 作者: 范圣帅(fanshengshuai@gmail.com)
 * 时间: 2012-07-02 01:22:53
 *
 * vim: set expandtab sw=4 ts=4 sts=4
 * $Id: Pager.php 49 2012-07-26 16:46:00Z fanshengshuai $
 */

class Pager {
    public static function build(&$page_option) {

        if (!$page_option['per_page']) {
            $page_option['per_page'] = 20;
        }

        $page_option['curr_page'] = max(1, $page_option['curr_page']);
        $page_option['start'] = ($page_option['curr_page'] - 1) * $page_option['per_page'];

        $per_page = $page_option['per_page'];
        $curr_page = $page_option['curr_page'];

        $pages = ceil($page_option['total']/$per_page);

        $query = $_GET;
        if ($query['page']) {
            unset($query['page']);
        }

        if ($query) {
            $query = http_build_query($query) . '&';
        } else {
            $query = '';
        }

        if (strpos($_G['uri'], '?')) {
            $uri = substr($_G['uri'], 0, strpos($_G['uri'], '?'));
        } else {
            $uri = $_G['uri'];
        }
        $query = $uri . ('?') . $query . 'page=';

        $pre_page = max(1, $curr_page - 1);
        $next_page = min($pages, $curr_page + 1);

        $html = '共 ' . $page_option['total'] . ' 条，' . $pages . '页&nbsp;';
        $html .= '<a href="' . $query . '1">首页</a>&nbsp;<a href="' . $query . $pre_page . '">上一页</a>&nbsp;';
        $start_page = max(1, $curr_page - 9);
        $end_page = min($pages, $start_page + 20);

        for ($show_page = $start_page;$show_page <= $end_page; $show_page ++) {
            $html .= '<a href="' . $query . ($show_page) . '"> [';
            if ($show_page == $curr_page) {
                $html .= '<font style="color: #f00">' . $show_page . '</font>';
            } else {
                $html .= $show_page;
            }
            $html .= '] </a>&nbsp;';
        }

        $html .= '<a href="' . $query . $next_page . '">下一页</a>&nbsp;<a href="' . $query . $pages . '">尾页</a>';

        $page_option['html'] = $html;

    }
}
