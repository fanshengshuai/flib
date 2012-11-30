{include "admin/header.tpl"}

{if $success}
<div style="border:red 1px solid; width: 98%; margin: auto;">
    <div style="padding: 5px;">更新完成</div>
</div>
{/if}

<form method="post" action="/admin/block/edit">
    <table class="tmain g_w" cellspacing="1" cellpadding="3" style="margin-bottom:10px;">
        <tr>
            <th colspan="12" style="text-align:left">
                <button type="button" class="button primary " onclick="location='/admin/block/list?page={$block['page']}';">返回列表</button>
            </th>
        </tr>
        <tr>
            <th width="100">标识</th>
            <td>
                <input type="text" name="name" value="{$block['name']}" class="px" />
            </td>
            <th width="100">所在页面</th>
            <td>
                <input type="radio" class="radio" id="page_enterprise" name="page" value="1" {if $block['page'] == '1'}checked="true"{/if}/>
                <label for="page_enterprise">首页</label>
                <input type="radio" class="radio" id="page_goods" name="page" value="2" {if $block['page'] == '2'}checked="true"{/if}/>
                <label for="page_goods">宾馆</label>
                <input type="radio" class="radio" id="page_goods" name="page" value="3" {if $block['page'] == '3'}checked="true"{/if}/>
                <label for="page_goods">列表</label>
            </td>
        </tr>
        <tr>
            <th width="100">所在区域</th>
            <td>
                <input type="text" name="area" value="{$block['area']}" class="px" />
            </td>

            <th>显示数量</th>
            <td>
                <input type="text" name="shownum" value="{$block['shownum']}" class="px" />
                <input type="hidden" name="parameter[bannedids]" value="$block[param][bannedids]" />
            </td>
        </tr>
        <tr>
            <th width="100">显示名称</th>
            <td>
                <input type="text" name="title" value="{$block[title]}" class="px" />
            </td>

            <th width="100">类型</th>
            <td>
                <input type="radio" class="radio" id="block_type_enterprise" name="block_type" value="1" {if $block['blocktype'] == '1'}checked="true"{/if}/>
                <label for="block_type_enterprise">企业</label>
                <input type="radio" class="radio" id="block_type_goods" name="block_type" value="2" {if $block['blocktype'] == '2'}checked="true"{/if}/>
                <label for="block_type_goods">商品</label>
                <input type="radio" class="radio" id="block_type_ad" name="block_type" value="3" {if $block['blocktype'] == '3'}checked="true"{/if}/>
                <label for="block_type_goods">广告</label>
                <input type="radio" class="radio" id="block_type_ad" name="block_type" value="4" {if $block['blocktype'] == '4'}checked="true"{/if}/>
                <label for="block_type_goods">视频</label>
            </td>
        </tr>
        <tr>
            <td colspan="12">
                <input type="hidden" name="bid" value="{$bid}" />
                <button type="submit" class="button">修改</button>
            </td>
        </tr>
    </table>
</form>

<form method="post" action="?m=block&do=updateDisplay_order&bid={$bid}">
    <table class="tmain g_w" cellspacing="1" cellpadding="3" style="margin-bottom:10px;">
        <tr>
            <th colspan="6" style="text-align:left">
                <button type="button" class="button primary" onclick="location='?m=block&do=block_itemAdd&bid={$_GET['bid']}';">增加</button>
            </th>
        </tr>
        <tr>
            <th style="width:35px;">排序</th>
            <th style="width:100px;">图片</th>
            <th style="width:500px;">标题</th>
            <th>简介</th>
            <th style="width:100px; text-align:center;">操作</th>
        </tr>
        {foreach from=$block_items item=block_item}
        <tr>
            <td>
                <input name="display_order[{$block_item['item_id']}]" value="{$block_item['display_order']}" style="width:30px;" />
            </td>
            <td>
                {if $block_item['pic']}
                <img style="padding:1px;border:#ccc 1px solid;width:100px;" src="{$block_item['pic']}" />
                {/if}
            </td>
            <td>
                <a target="_blank" href="{$block_item['url']}">{$block_item['title']}</a>
            </td>
            <td>{$block_item['summary']}</td>
            <td style="text-align: center;">
                <a href="/admin/block/editBlockItem?block_item_id={$block_item['item_id']}">修改</a>
                <a href="/admin/block/editBlockItem?block_item_id={$block_item['item_id']}">删除</a>
            </td>
        </tr>
        {/foreach}
        <tr>
            <th colspan="60" style="text-align:left">
                <button type="submit">排序</button>
            </th>
        </tr>
    </table>
</form>
{include "admin/footer.tpl"}