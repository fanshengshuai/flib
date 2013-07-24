<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <link rel="stylesheet" type="text/css" href="/css/admin_style.css" />
        <script charset="utf-8" src="/js/jquery.js" type="text/javascript"></script>
    </head>
    <body>
        <form ajax="true" method="post" action="/admin/auth/login" enctype="multipart/form-data">
            <table style="margin:100px auto; width:500px;">
                <tr>
                    <th style="width:50px;">帐户</th>
                    <td><input name="username" /></td>
                </tr>
                <tr>
                    <th>密码</th>
                    <td><input name="password" /></td>
                </tr>
                <tr>
                    <th></th>
                    <td>
                        <button type="submit" class="button primary green">登陆</button>
                    </td>
                </tr>
            </form>
            {include 'admin/footer.tpl'}
