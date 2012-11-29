{include file="admin/header.tpl"}
<form action="" method="post">
    <table class="tmain g_w">
        <tr>
            <th width="50">标题：</th>
            <td width="100"><input type="text" name="title1" value="{$title}"></td>
            <th width="50">分类:</th>
            <td width="100">
                <select name="cid">
                    <option value="-1">请选择分类</option>
                    {foreach from=$categoryList item=item}
                    <option value="{$item['id']}">{$item['categoryName']}</option>
                    {/foreach}
                </select>
            </td>
            <td>
                <button type="submit" class="button primary green">搜索</button> &nbsp;&nbsp;
                <button ajax="true" type="button" class="button" href="/admin/news/add" >新增</button>
                &nbsp;&nbsp;
                <a href="/admin/category/list">分类管理</a>
            </td>
        </tr>
    </table>
</form>
<table class="tmain g_w">
    <tr>
        <th width="30">编号</th>
        <th width="auto">新闻标题</th>
        <th width="100">添加时间</th>
        <th width="100">操作</th>
    </tr>
    {foreach from=$list item=item}
    <tr>
        <td style="text-align:center;">{$item['news_id']}</td>
        <td>{$item['title']}</td>
        <td>{$item['create_time']}</td>
        <td>
            <a ajax="true" href="/admin/news/add?news_id={$item['news_id']}">编辑</a>&nbsp;&nbsp;
            <a ajax="true" href="/admin/news/delete?news_id={$item['news_id']}">删除</a>
        </td>
    </tr>
    {/foreach}
    <tr>
        <td colspan="6" style="text-align: center">
            {$pager}
        </td>
    </tr>
</table>
{include file="admin/footer.tpl"}
<script type="text/javascript">
    $().ready(function(){
            $('#addNewRecord').fancybox();
            });
var fancybox_editor;
$('a[display=ajax_window]').fancybox();
</script>
