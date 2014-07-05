<?php

/**
 *
 * 作者: 范圣帅(fanshengshuai@gmail.com)
 * 时间: 2012-07-02 01:22:53
 *
 * vim: set expandtab sw=4 ts=4 sts=4
 * $Id: Pager.php 281 2012-08-26 03:43:45Z fanshengshuai $
 */
class FPager {

    /**
     * @var int
     */
    var $count = 0;
    var $page_size = 10;

    public static function build($total, $page_size = 20, $url = null, $page = -1) {
        global $_F;

        if ($total < $page_size) {
            return array();
        }

        if ($page == -1) {
            $page = intval($_GET['page']);
        }

        $page = max(1, $page);
        $page_option ['start'] = ($page - 1) * $page_option ['per_page'];

        $pages = ceil($total / $page_size);

        $url = self::getUrl($url);

        $prev_page = max(1, $page - 1);
        $next_page = min($pages, $page + 1);

        $html = '';
        if ($page == 1) {
            $html .= '<span class="prev disabled">上一页</span>';
        } else {
            $html .= "<a href=\"{$url}{$prev_page}\" class=\"prev pagegbk\">上一页</a>";
        }
        $start_page = max(1, $page - 9);
        $end_page = min($pages, $start_page + 20);

        for ($show_page = $start_page; $show_page <= $end_page; $show_page++) {
            if ($show_page == $page) {
                $html .= '<span class="current">' . $show_page . '</span>';
            } else {
                $html .= "<a href=\"{$url}{$show_page}\">{$show_page}</a>";
            }
        }

        if ($page == $pages) {
            $html .= '<span class="next disabled">下一页</span>';
        } else {
            $html .= "<a href=\"{$url}{$next_page}\" class=\"next pagegbk\">下一页</a>";
        }

        if (!$_F ['in_ajax']) {
            $html = str_replace('ajax="true"', '', $html);
        }

        $ret ['html'] = $html;
        $ret ['sql_limit'] = " limit " . ($page - 1) * $page_size . ", {$page_size}";

        return $ret;
    }

    // 简单分页
    public static function buildSimplePge($total, $page_size = 20, $url = null, $page = -1) {
        global $_F;

        if ($total < $page_size) {
            return '';
        }

        $page = max(1, $page);
        $pages = ceil($total / $page_size);

        $url = self::getUrl($url);

        $prev_page = max(1, $page - 1);
        $next_page = min($pages, $page + 1);

        $html = "<label>{$page}/{$pages}</label>";
        if ($page == 1) {
            $html .= '<span class="prev disabled">上一页</span>';
        } else {
            $html .= "<a href=\"{$url}{$prev_page}\" class=\"prev pagegbk\">上一页</a>";
        }
        if ($page == $pages) {
            $html .= '<span class="next disabled">下一页</span>';
        } else {
            $html .= "<a href=\"{$url}{$next_page}\" class=\"next pagegbk\">下一页</a>";
        }

        return $html;
    }

    /**
     * 分页page url
     *
     * @param string $url
     *
     * @return string
     */
    public static function getUrl($url = '') {
        global $_F;

        if (!$url) {
            $url = $_SERVER ['REQUEST_URI'];
        }
        $url = preg_replace('#&*page=(\d)*#i', '', $url);
        $url = trim($url, '?');
        if (strpos($url, '?')) {
            $url .= '&';
        } else {
            $url .= '?';
        }
        $url .= 'page=';

        return $url;
    }


    public static function getPagerInfo($total, $currentPage=1, $per_page = 10, $page_list_num = 10) {
        global $_F;

        $ret = array('total' => $total, 'per_page' => $per_page);

        $page = max(1, $currentPage);
        $pages = ceil($total / $per_page);

        if ($page > $pages) {
            $page = $pages;
        }

        $ret['current'] = $page;
        $ret['first'] = 1;
        $ret['last'] = $pages;

        $ret['prev'] = max(1, $page - 1);
        $ret['next'] = min($pages, $page + 1);

        $ret['url_pre'] = self::getUrl();

        $start = max(1, $ret['current'] - intval($page_list_num / 2));

        $ret['start'] = $start;
        $ret['end'] = min($ret['last'], $start + $page_list_num - 1);

        $sql_limit_start = max(0, ($page - 1) * $per_page);

        $ret ['sql_limit'] = " limit " . $sql_limit_start . ", {$per_page}";

        return $ret;
    }
}
