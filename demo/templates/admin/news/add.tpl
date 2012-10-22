<div style="margin: 20px;">
    <div style="text-align:center">新闻管理</div>
    <form ajax="true" method="post" action="/admin/news/save" enctype="multipart/form-data">
        <table class="tmain" border="0">
            <tr>
                <th>新闻名称:</th>
                <td><input type="text" name="title" value="{$news['title']}"> </td>
            </tr>
            <tr>
                <th>新闻分类：</th>
                <td>
                    <select name="cid">
                        {foreach from=$categoryList item=item}
                        <option value="{$item['id']}" {if $item['id'] eq $news['cid']}selected="" {/if}>{$item['categoryName']}</option>
                        {/foreach}
                    </select>
                </td>
            </tr>
            <tr>
                <th>图片:</th>
                <td>
                    <input type="file" name="pic_url" />
                </td>
            </tr>
            <tr>
                <th>新闻描述:</th>
                <td>
                    <textarea rows="2" cols="50" name="description">{$news['description']}</textarea>
                </td>
            </tr>

            <tr>
                <th>详细介绍:</th>
                <td><textarea name="content" id="content" style="width:600px;height: 300px" >{$news['content']}</textarea></td>
            </tr>

            <input type="hidden" name="id" value="{$news['id']}">
            <tr>
                <td colspan="2"><button type="submit">确定</button> </td>
            </tr>
        </table>
    </form>
</div>
<script type="text/javascript">
    $.getScript('/js/editor/kindeditor-min.js', function() {
        fancybox_editor = KindEditor.create('#content', { afterBlur:function() { this.sync(); }});
    });
    apply_ajax_form();
</script>
