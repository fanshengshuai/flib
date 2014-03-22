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
	public static function build($total, $page_size = 20, $url = null, $page = -1) {
		global $_G;

		if ($total < $page_size) {
			return array ();
		}

		$page = max ( 1, $page );
		$page_option ['start'] = ($page - 1) * $page_option ['per_page'];

		$pages = ceil ( $total / $page_size );

		$url = self::getUrl ( $url );

		$prev_page = max ( 1, $page - 1 );
		$next_page = min ( $pages, $page + 1 );

		$html = '';
		if ($page == 1) {
			$html .= '<span class="prev disabled">上一页</span>';
		} else {
			$html .= "<a href=\"{$url}{$prev_page}\" class=\"prev pagegbk\">上一页</a>";
		}
		$start_page = max ( 1, $page - 9 );
		$end_page = min ( $pages, $start_page + 20 );

		for($show_page = $start_page; $show_page <= $end_page; $show_page ++) {
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

		if (! $_G ['in_ajax']) {
			$html = str_replace ( 'ajax="true"', '', $html );
		}

		$ret ['html'] = $html;
		$ret ['sql_limit'] = " limit " . ($page - 1) * $page_size . ", {$page_size}";

		return $ret;
	}

	// 简单分页
	public static function buildSimplePge($total, $page_size = 20, $url = null, $page = -1) {
		global $_G;

		if ($total < $page_size) {
			return '';
		}

		$page = max ( 1, $page );
		$pages = ceil ( $total / $page_size );

		$url = self::getUrl ( $url );

		$prev_page = max ( 1, $page - 1 );
		$next_page = min ( $pages, $page + 1 );

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
	 * @param unknown $url
	 * @return string
	 */
	public static function getUrl($url) {
		if (! $url) {
			$url = $_G ['uri'];
		}
		$url = preg_replace ( '#&*page=\d*#i', '', $url );
		$url = trim ( $url, '?' );
		if (strpos ( $url, '?' )) {
			$url .= '&';
		} else {
			$url .= '?';
		}
		$url .= 'page=';

		return $url;
	}
}