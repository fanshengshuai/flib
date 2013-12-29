<?php

/**
 *
 * 作者: 范圣帅(fanshengshuai@gmail.com)
 * 时间: 2012-07-02 01:22:53
 *
 * vim: set expandtab sw=4 ts=4 sts=4
 * $Id: Pager.php 281 2012-08-26 03:43:45Z fanshengshuai $
 */

class Pager {
    public static function build(&$page_option) {
        global $_G;

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

        $html = '共 ' . $page_option['total'] . ' 条&nbsp;';
        //$html = $pages . '页';
        $html .= '<a ajax="true" href="' . $query . '1">首页</a>&nbsp;<a ajax="true" href="' . $query . $pre_page . '">上一页</a>&nbsp;';
        $start_page = max(1, $curr_page - 9);
        $end_page = min($pages, $start_page + 10);

        for ($show_page = $start_page;$show_page <= $end_page; $show_page ++) {
            if ($show_page == $curr_page) {
                $html .= '<span class="current">' . $show_page . '</span>';
            } else {
	            $html .= '<a ajax="true" href="' . $query . ($show_page) . '"> ';
                $html .= $show_page;
	            $html .= ' </a>&nbsp;';
            }
        }

        $html .= '<a ajax="true" href="' . $query . $next_page . '">下一页</a>&nbsp;';
        // $html .= '<a ajax="true" href="' . $query . $pages . '">尾页</a>';

        if (!$_G['in_ajax']) {
            $html = str_replace('ajax="true"', '', $html);
        }

        $page_option['html'] = "<div class=\"ui-bar\" id=\"page-area\"><div class=\"ui-pages commpage\" id=\"comment-pages\">{$html}</div></div>";

    }
}
