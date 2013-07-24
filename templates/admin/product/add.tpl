{include file="admin/header.tpl"}
<form ajax="true" method="post" action="/admin/product/save" enctype="multipart/form-data">
    <table class="tmain" border="0" style="width:700px;">
        <tr>
            <th>标题:</th>
            <td><input type="text" name="title" style="width:500px;" value="{$p_info['title']}"> </td>
        </tr>
        <tr>
            <th>排序:</th>
            <td>
                <input type="text" name="display_order" value="{$p_info['display_order']}" />
            </td>
        </tr>
        <tr>
            <th>图片:</th>
            <td>
                <input type="file" name="pic_url" />
            </td>
        </tr>
        <tr>
            <th>分类：</th>
            <td>
                <select name="cid" id="select_cid">
                    {foreach from=$cate_list item=item}
                    <optgroup label="{$item['c_name']}">
                        {foreach from=$item['sub'] item=sub_item}
                        <option value="{$sub_item['cid']}" {if $sub_item['cid'] eq $p_info['cid_sub']}selected{/if}>{$sub_item['c_name']}</option>
                        {/foreach}
                    </optgroup>
                    {/foreach}
                </select>
            </td>
        </tr>
        <tr>
            <th>摘要：</th>
            <td>
                <textarea rows="2" cols="50" name="description">{$p_info['description']}</textarea>
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <textarea name="content" id="content" style="width:700px;height: 300px" >{$p_info['content']}</textarea>
            </td>
        </tr>
        <tr>
            <input type="hidden" name="pid" value="{$p_info['pid']}">
            <td colspan="2"><button type="submit" class="button green">提交</button> </td>
        </tr>
    </table>
</form>
<script type="text/javascript">
    //$(function() { $('#select_cid').val({$cid}); });
    $.getScript('/js/editor/kindeditor-min.js', function() { fancybox_editor = KindEditor.create('#content', { afterBlur:function() { this.sync(); }}); });
</script>
{include file="admin/footer.tpl"}
