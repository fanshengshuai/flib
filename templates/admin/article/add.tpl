<div style="margin: 20px;">
    <div style="text-align:center">编辑</div>
    <form ajax="true" method="post" action="/admin/article/save" enctype="multipart/form-data">
        <table class="tmain" border="0">
            <tr>
                <th>标题:</th>
                <td><input type="text" name="title" style="width:500px;" value="{$article_info['title']}"> </td>
            </tr>
            <tr>
                <th>图片:</th>
                <td>
                    <input type="file" name="pic_url" />
                </td>
            </tr>
            <tr style="display:none;">
                <th>分类：</th>
                <td>
                    <input name="cat_id" value="{$article_info['cat_id']}" />
                    <!--
                    <select name="cat_id">
                        <option value="1">校园活动</option>
                        <option value="2">就业保障</option>
                    </select>
                    -->
                </td>
            </tr>
            {if $cat_id eq 2}
            <tr>
                <th>就业单位：</th>
                <td>
                    <input name="extra_1" value="{$article_info['extra_1']}" />
                </td>
            </tr>
            {/if}
            <tr>
                <th>摘要：</th>
                <td>
                    <textarea rows="2" cols="50" name="description">{$article_info['description']}</textarea>
                </td>
            </tr>
            <tr>
                <td colspan="2">详细介绍:
                    <textarea name="content" id="content" style="width:700px;height: 300px" >{$article_info['content']}</textarea>
                </td>
            </tr>
            <tr>
                <input type="hidden" name="article_id" value="{$article_info['article_id']}">
                <td colspan="2"><button type="submit" class="button green">提交</button> </td>
            </tr>
        </table>
    </form>
</div>
<script type="text/javascript">

    $(function() {
           $('input[name=cat_id]').val({$cat_id}); });

    apply_ajax();
    $.getScript('/js/editor/kindeditor-min.js', function() { fancybox_editor = KindEditor.create('#content', { afterBlur:function() { this.sync(); }}); });
</script>
