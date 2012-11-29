<form ajax="true" method="post" action="/admin/category/add">
    <table class="tmain">
        <tr>
            <th>父目录</th>
            <td>
                <select name="pid">
                    <option value="0">无</option>
                    {foreach from=$cate_list item=item}
                    <option value="{$item['cid']}" {if $item['cid'] eq $category_info['pid']}selected{/if}></option>
                    {/foreach}
                </select>
            </td>
        </tr>
        <tr>
            <th>名称</th>
            <td>
                <input name="c_name" value="{$category_info['c_name']}" />
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <input name="cid" value="{$category_info['cid']}" type="hidden" />
                <input name="ctype" type="hidden" value="{$ctype}" />
                <button type="submit" class="button">提交</button>
            </td>
        </tr>
    </table>
</form>
<script> apply_ajax(); </script>
