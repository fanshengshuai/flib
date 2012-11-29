<form ajax="true" method="post" action="/admin/news/save" enctype="multipart/form-data">
    <table class="tmain" border="0">
        <tr>
            <th>新闻名称:</th>
            <td><input type="text" name="title" value="{$news_info['title']}"> </td>
        </tr>
        <tr>
            <th>新闻分类：</th>
            <td>
                <select name="cid">
                    {foreach from=$categoryList item=item}
                    <option value="{$item['cid']}" {if $item['cid'] eq $news_info['cid']}selected="" {/if}>{$item['c_name']}</option>
                    {/foreach}
                </select>
            </td>
        </tr>
        <!--tr>
            <th>图片:</th>
            <td>
                <input type="file" name="pic_url" />
            </td>
        </tr-->
        <tr>
            <th>新闻描述:</th>
            <td>
                <textarea rows="2" cols="50" name="description">{$news_info['description']}</textarea>
            </td>
        </tr>

        <tr>
            <td colspan="2">
                <textarea name="content" id="content" style="width:700px;height: 300px" >{$news_info['content']}</textarea>
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <input type="hidden" name="news_id" value="{$news_info['news_id']}">
                <button type="submit" class="button">确定</button>
            </td>
        </tr>
    </table>
</form>
<script type="text/javascript">
    $.getScript('/js/editor/kindeditor-min.js', function() { fancybox_editor = KindEditor.create('#content', { afterBlur:function() { this.sync(); }}); });
    apply_ajax();
</script>
