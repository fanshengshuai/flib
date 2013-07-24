{include file="admin/header.tpl"}

<br />

<div class="g_w">
    <button href="/admin/product/add?cid={$cid}" class="button primary" ajax="true">新增</button>
</div>

<table class="g_w tmain">
    <tr>
        <th width="80">编号</th>
        <th width="auto">标题</th>
        <th width="160">添加时间</th>
        <th width="150">操作</th>
    </tr>
    {foreach from=$product_list['data'] item=item}
    <tr>
        <td>{$item['pid']}</td>
        <td><a target="_blank" href="/p/{$item['pid']}.html">{$item['title']}</a></td>
        <td>{$item['create_time']}</td>
        <td>
            <a href="/admin/product/modify?pid={$item['pid']}" ajax="true">编辑</a>&nbsp;&nbsp;
            <a href="/admin/product/delete?cid={$cid}&pid={$item['pid']}" ajax="true">删除</a>
        </td>
    </tr>
    {/foreach}
</table>
<div class="g_w" style="padding:20px;">
    {$product_list['page_option']['html']}
</div>
{include file="admin/footer.tpl"}
