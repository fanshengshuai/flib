<form ajax="true" method="post" action="/admin/about/edit">
    <table>
        <tr>
            <td><textarea name="content" id="content" style="width:800px;height: 500px" >{$content}</textarea></td>
        </tr>
        <tr>
            <td colspan="2">
                <input type="hidden" name="key" value="{$key}" />
                <button type="submit" class="button primary green">修改</button>
            </td>
        </tr>
    </table>
</form>
<script type="text/javascript">
    $.getScript('/js/editor/kindeditor-min.js', function() { fancybox_editor = KindEditor.create('#content', { afterBlur:function() { this.sync(); }}); });
    apply_ajax();
</script>
