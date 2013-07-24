{include "admin/header.tpl"}
<table class="tmain" cellspacing="1" cellpadding="3">
    <tr bgcolor="#eeeeee">
       
        <td style="width: 80px; background:{if strtolower($_GET['page']) eq 'index'}#ccc; color:red{else}#eee{/if};" align="center"><a href="?page=index">首页</a></td>
        <td style="width: 80px; background:{if strtolower($_GET['page']) eq 'hotel'}#ccc; color:red{else}#eee{/if};" align="center"><a href="?page=hotel">宾馆</a></td>
        <td style="width: 80px; background:{if strtolower($_GET['page']) eq 'list'}#ccc; color:red{else}#eee{/if};" align="center"><a href="?page=list">列表页面</a></td>
        <td bgcolor="#eeeeee" align="center"><a href="?m=Sys&do=Config"></a></td>
    </tr>
</table>

<br />
{foreach from=$blocks key=area item=blockArr}
<table class="tmain g_w" cellspacing="1" cellpadding="3">
    <tr><th colspan="10" style="text-align:left;">区域 {$area}</th></tr>
    <tr>
        {foreach from=$blockArr key=item_key item=block}
        <th width="50" style="text-align:right;">{$block['bid']}</th>
        <td onclick="selectThisrow({$co['eid']});" onmouseover="this.className='hover';" onmouseout="this.className='nomal';">
		<a href="/admin/block/edit?bid={$block['bid']}">{$block['name']}</a></td>
	{if ($item_key % 5) eq 4} </tr><tr>{/if}
        {/foreach}
    </tr>
</table>
<br />
{/foreach}
<style>
    .hover {
        background:#f3f3f3;
        color:red;
    }
    .hover a {
        color:red;
    }
    .nomal {
        background:#fff;
        colro:#000;
    }
    .nomal a {
        color:#003399;
    }
</style>
{include "admin/footer.tpl"}
