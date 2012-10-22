{include 'admin/header.tpl'}
<table class="tmain g_w">
    <tr>
        <th width="80px">分类编号</th>
        <th width="100px">分类名称</th>

        <th width="160px">添加时间</th>
        <th width="100px">记录状态</th>
        <th width="150px">操作</th>
    </tr>
{foreach from=$list item=item}
    <tr>
        <td>{$item['id']}</td>
        <td>{$item['categoryName']}</td>

        <td>{$item['create_time']}</td>
        <td>{if $item['status']==1}
            正常
            {else}
            删除
        {/if}</td>
        <td>
            <a href="/admin/category/add?id={$item['id']}" display="ajax_window">编辑</a>&nbsp;&nbsp;
            <a href="/admin/category/delete?id={$item['id']}" display="ajax_window">删除</a>
        </td>
    </tr>
{/foreach}
    <tr>
        <td colspan="6" style="text-align: center">
        {$pager}
        </td>
    </tr>
</table>
<script type="text/javascript">
    $().ready(function(){
        $('#addNewRecord').fancybox();
    });
    var fancybox_editor;
    $('a[display=ajax_window]').fancybox();
</script>
{include 'admin/footer.tpl'}
