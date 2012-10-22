{include file="admin/header.tpl"}

<br />

<div class="g_w">
    <button href="/admin/article/add?cat_id={$cat_id}" class="button primary" ajax="true">新增</button>
</div>

<table class="g_w tmain">
    <tr>
        <th width="80px">编号</th>
        <th width="100px">标题</th>
        <th width="360px">摘要</th>
        <th width="160px">添加时间</th>
        <th width="150px">操作</th>
    </tr>
    {foreach from=$article_list['data'] item=item}
    <tr>
        <td>{$item['article_id']}</td>
        <td><a target="_blank" href="/a/detail/{$item['article_id']}">{$item['title']|truncate:12}...</a></td>
        <td>{$item['description']|truncate:20}...</td>
        <td>{$item['create_time']}</td>
        <td>
            <a href="/admin/article/modify?article_id={$item['article_id']}" ajax="true">编辑</a>&nbsp;&nbsp;
            <a href="/admin/article/delete?cat_id={$cat_id}&article_id={$item['article_id']}" ajax="true">删除</a>
        </td>
    </tr>
    {/foreach}
</table>
<div class="g_w" style="padding:20px;">
    {$article_list['page_option']['html']}
</div>

<script type="text/javascript">
    var fancybox_editor;
</script>
{include file="admin/footer.tpl"}
