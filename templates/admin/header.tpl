{if !$_G['in_ajax']}<!DOCTYPE html>
<html>
    <head>
        <title>{$_G['header']['keywords']}</title>

        <meta charset="utf-8">
        <meta name="keywords" content="{$_G['header']['keywords']}" />
        <meta name="description" content="{$_G['header']['description']} " />

        <link rel="stylesheet" type="text/css" href="/css/admin_style.css" />

        <script charset="utf-8" src="/js/jquery.js" type="text/javascript"></script>
        <script charset="utf-8" src="/js/jquery-ui.min.js" type="text/javascript"></script>
        <script charset="utf-8" src="/js/jquery.form.js"></script>

        <script charset="utf-8" src="/js/flib.js"></script>

        <link rel="stylesheet" type="text/css" href="/js/fancybox/jquery.fancybox-1.3.4.css" media="screen" />

        <script type="text/javascript" src="/js/fancybox/jquery.mousewheel-3.0.4.pack.js"></script>
        <script type="text/javascript" src="/js/fancybox/jquery.fancybox-1.3.4.pack.js"></script>
        <script charset="utf-8" src="/js/editor/kindeditor-min.js"></script>
        <script type="text/javascript" src="/js/auto_ajax_form.js"></script>
    </head>

    <body>
        <div id="append_parent"></div>
        <div class="topbar" style="height:20px; overflow:hidden;">
            {$_G['auth_info']['school_name']}，<a href="/admin/auth/logout">退出</a>
            &nbsp;
            <span><a target="_blank" href="http://{$_G['domain']}">查看页面</a></span>
            &nbsp;
            <span><a href="/admin/global/setting" style="{if $_G['controller'] eq 'Controller_Admin_Global'}color:#ccc; font-weight:bold; {/if}">网站设置</a></span>
        </div>
        <br />
        {include "admin/top.tpl"}
{/if}
