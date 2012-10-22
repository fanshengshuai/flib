{include file="admin/header.tpl"}
<div style="width: 1000px">
    <form action="" method="post">
        <table class="tmain">
            <tr>
                <td colspan="4">分类搜索</td>
            </tr>
            <tr>
                <th>分类名称：</th>
                <td><input type="text" name="categoryName" value="{$categoryName}"></td>
                <th></th>
                <td></td>
            </tr>
            <tr>
                <td colspan="4"><button type="submit">搜索</button> &nbsp;&nbsp;
                    <input type="button" value="新增" href="/admin/category/add"  id="addNewRecord"  />
                </td>
            </tr>
        </table>
    </form>
</div>
<table class="tmain">
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
{include file="admin/footer.tpl"}
<script type="text/javascript">
    $().ready(function(){
        $('#addNewRecord').fancybox();
    });
    var fancybox_editor;
    $('a[display=ajax_window]').fancybox();
</script>