{include "admin/header.tpl"}

<div class="g_w">
    <a ajax="true" href="/admin/category/add?ctype={$ctype}">增加</a>
</div>
<table class="tmain g_w">
    {foreach from=$cate_list item=item}
    <tr>
        <td style="width:50px; text-align:center;">{$item['cid']}</td>
        <td style="text-align:left;">
            <strong><a style="color:#000;" ajax="true" href="/admin/category/add?cid={$item['cid']}">{$item['c_name']}</a></strong>&nbsp;|
            {foreach from=$item['sub'] item=sitem}
            &nbsp;<a style="color:#000;" ajax="true" href="/admin/category/add?cid={$sitem['cid']}">{$sitem['c_name']}</a>
            {/foreach}
        </td>
    </tr>
    {/foreach}
</table>
<script type="text/javascript">
    apply_ajax();
</script>
{include "admin/footer.tpl"}
