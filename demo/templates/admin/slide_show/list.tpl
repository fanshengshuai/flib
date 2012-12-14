{include 'admin/header.tpl'}
<br />
<div class="g_w">
    <h1>幻灯片编辑</h1>
    <button ajax="true" href="/admin/slideShow/add" class="button primary">新增</button>
</div>
<form method="post" action="/admin/slideShow/list">
    <table class="tmain g_w">
        <tr>
            <th style="width:50px;">排序</th>
            <th></th>
            <th style="width:100px;"></th>
        </tr>
        {foreach from=$slide_show_list item=item}
        <tr>
            <th><input style="width:50px" name="display_order[{$item['pic_id']}]" value="{$item['display_order']}" /></th>
            <td>
                <img width="100" height="30" src="/attachs/{$item['pic_url']}" />
            </td>
            <td valign="top" style="padding-top:20px; padding-left:20px;">
                {$item['url']}
                <br />
                <a ajax="true" href="/admin/slideShow/modify?pic_id={$item['pic_id']}">修改</a>
                <a ajax="true" href="/admin/slideShow/delete?pic_id={$item['pic_id']}">删除</a>
            </td>
        </tr>
        {/foreach}
        <tr>
            <td colspan="2">
                <button type="submit" class="button primary">修改排序</button>
                {$pager}
            </td>
        </tr>
    </table>
</form>

<script>
    var fancybox_editor;
    $('a[display=ajax_window]').fancybox();
</script>
{include 'admin/footer.tpl'}
