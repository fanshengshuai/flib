<form ajax="true" method="post" action="/admin/slideShow/save" enctype="multipart/form-data">
    <table class="tmain">
        <tr>
            <th>图片</th>
            <td>
                <input type="file" name="pic_url" />
                ( 图片大小为 1000x300px )
            </td>
        </tr>
        <tr>
            <th>链接</th>
            <td>
                <input name="url" style="width:700px;" value="{$slide_show_info['url']}" />
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <input type="hidden" name="pic_id" value="{$slide_show_info['pic_id']}" />
                {if $slide_show_info['pic_id']}
                <button class="button primary green">修改</button>
                {else}
                <button class="button primary green">添加</button>
                {/if}
            </td>
        </tr>
    </table>
</form>
<script>
    apply_ajax_form();
</script>
