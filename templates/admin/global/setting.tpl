{include 'admin/header.tpl'}
<br />
<form ajax="true" method="post" action="/admin/global/setting" enctype="multipart/form-data">
    <table class="tmain g_w">
        <tr>
            <th style="width:80px;">幻灯片设置</th>
            <td>
                <a href="/admin/slideShow/list">设置</a>
            </td>
        </tr>
        {if $_G['is_base']}
        <tr>
            <th style="width:100px;">首页广告设置</th>
            <td>
                <a href="/admin/advshow/index" id="setAdv">设置</a>
            </td>
        </tr>
        {/if}
        <tr>
            <th style="width:100px;">密码</th>
            <td>
                <a href="/admin/global/passwd" ajax="true">修改</a>
            </td>
        </tr>
        <tr>
            <th style="width:80px;">网站标题</th>
            <td>
                <input style="width:600px;" name="site_title" value="{$site_title}" />
            </td>
        </tr>
        <tr>
            <th style="width:80px;">关键字</th>
            <td>
                <input style="width:600px;" name="site_keywords" value="{$site_keywords}" />
            </td>
        </tr>
        <tr>
            <th style="width:80px;">描述</th>
            <td>
                <input style="width:600px;" name="site_description" value="{$site_description}" />
            </td>
        </tr>
        <tr>
            <th style="width:80px;">招聘电话</th>
            <td>
                <input style="width:600px;" name="phone" value="{$phone}" />
            </td>
        </tr>
        <tr>
            <th>地址</th>
            <td>
                <input style="width:600px;" name="address" value="{$address}" />
            </td>
        </tr>
        <tr>
            <th>来校线路</th>
            <td>
                <input style="width:600px;" name="address_nav" value="{$address_nav}" />
            </td>
        </tr>
        <tr>
            <th>地图API</th>
            <td>
                <input style="width:600px;" name="map_point" value="{$map_point}" />
                <a target="_blank" href="http://dev.baidu.com/wiki/static/map/API/tool/getPoint/">拾取坐标</a>
            </td>
        </tr>
        {if $_G['is_base']}
        <tr>
            <th>统计代码</th>
            <td>
                <textarea style="width:600px; height:100px;" name="stat_code">{$stat_code}</textarea>
            </td>
        </tr>
        {/if}
        <tr>
            <td></td>
            <td>
                <button type="submit" class="button primary">保存</button>
            </td>
        </tr>
    </table>
</form>

<div class="g_w">
</div>

<script>
    $('a[display=ajax_window]').fancybox();
            $().ready(function(){
                $('#setAdv').fancybox();
            });


</script>
{include 'admin/footer.tpl'}
