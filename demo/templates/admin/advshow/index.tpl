<div style="margin: 20px;">
    <div style="text-align:center">首页广告管理</div>
    <form ajax="true" method="post" action="/admin/advshow/save" enctype="multipart/form-data">
        <table class="tmain" border="0">
            <tr>
                <th>广告名称:</th>
                <td><input type="text" name="title" value="{$adv['title']}"> </td>
            </tr>
            <tr>
                <th>广告链接:</th>
                <td>
                    <input type="text" name="link" value="{$adv['link']}">
                </td>
            </tr>
            <tr>
                <th>缩略图：</th>
                <td><input type="file" name="thumb_pic"></td>
            </tr>


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