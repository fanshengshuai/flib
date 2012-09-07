{include 'header.tpl'}
<div class="topbar">
    <a href="/login">登陆</a>
</div>
<div class="g_w">
    <p>
    <h2>Ajax</h2>
    如果需要自动应用 Ajax 到 a, from, button，这句话会把 ajax 应用到有 ajax="true" 的元素
    <br />
    <button ajax="true" class="button" href="/login">测试</button>
    <a ajax="true" href="/login">测试</a>
    </p>

    <p>
    <h2>调试信息</h2>
    见 APP_ROOT/config/global.php :<br /><br />
    $_config['global']['debug'] = true;<br />
    $_config['global']['debug_ajax'] = false;
    </p>
</div>

<script type="text/javascript">

    // 如果需要自动应用 Ajax 到 a, from, button，这句话会把 ajax 应用到有 ajax="true" 的元素
$(function() { apply_ajax(); });
</script>
{include 'footer.tpl'}
