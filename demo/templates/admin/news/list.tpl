{include file="admin/header.tpl"}
{include 'admin/abouttop.tpl'}
<div style="width: 1000px">
    <form action="" method="post">
        <table class="tmain">
            <tr>
                <td colspan="4" style="text-align:center">新闻搜索</td>
            </tr>
            <tr>
                <th>新闻标题：</th>
                <td><input type="text" name="title1" value="{$title}"></td>
                <th>所属分类:</th>
                <td><select name="cid">
                    <option value="-1">请选择分类</option>
                    {foreach from=$categoryList item=item}
                        <option value="{$item['id']}">{$item['categoryName']}</option>
                    {/foreach}
                </select></td>
            </tr>
            <tr>
                <td colspan="4"><button type="submit">搜索</button> &nbsp;&nbsp;
                    <input type="button" value="新增" href="/admin/news/add"  id="addNewRecord"  />
                    &nbsp;&nbsp;
                    <a href="/admin/category/list">分类管理</a>
                </td>
            </tr>
        </table>
    </form>
</div>
<table class="tmain">
    <tr>
        <th width="80px">编号</th>
        <th width="100px">新闻标题</th>
        <th width="360px">新闻描述</th>
        <th width="160px">添加时间</th>
        <th width="100px">记录状态</th>
        <th width="150px">操作</th>
    </tr>
{foreach from=$list item=item}
    <tr>
        <td>{$item['id']}</td>
        <td>{$item['title']|truncate:12}...</td>
        <td>{$item['description']|truncate:20}...</td>
        <td>{$item['create_time']}</td>
        <td>{if $item['status']==1}
            正常
            {else}
            删除
        {/if}</td>
        <td>
            <a href="/admin/news/add?id={$item['id']}" display="ajax_window">编辑</a>&nbsp;&nbsp;
            <a href="/admin/news/delete?id={$item['id']}" display="ajax_window">删除</a>
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
