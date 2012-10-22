
<form action="/admin/category/save" method="post" ajax="true">
    <table cellpadding="tmain" style="width: 300px">
        <tr>
            <td colspan="2" style="text-align: center">创建分类</td>
        </tr>
        <tr>
            <th>分类名称：</th>
            <td><input type="text" name="categoryName" value="{$categoryInfo['categoryName']}"></td>
        </tr>

        <tr>
            <td colspan="2"><button type="submit">提交</button> </td>
        </tr>
    </table>
    <input type="hidden" name="id" value="{$categoryInfo['id']}">
    </form>
    <script type="text/javascript">
        apply_ajax_form();
    </script>
