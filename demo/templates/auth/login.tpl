{include 'header.tpl'}
<form ajax="true" method="post" action="/login" enctype="multipart/form-data">
	<table style="margin:100px auto; width:500px;">
		<tr>
			<th colspan="2" style="text-align:left; padding-left:50px;">欢迎登陆Flib系统</th>
		</tr>
		<tr>
			<th style="width:50px;">帐户</th>
			<td><input name="username" /></td>
		</tr>
		<tr>
			<th>密码</th>
			<td><input type="password" name="password" /></td>
		</tr>
		<tr>
			<th></th>
			<td>
				<button type="submit" class="button primary green">登陆</button>
			</td>
		</tr>
	</table>
</form>

<script type="text/javascript">

// 如果需要自动应用 Ajax 到 a, from, button，这句话会把 ajax 应用到有 ajax="true" 的元素
$(function() {
	apply_ajax();
});
</script>
{include 'footer.tpl'}