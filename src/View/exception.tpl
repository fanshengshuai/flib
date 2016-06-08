<!doctype html>
<html>
<head>
    <title>系统发生错误</title>
    <meta http-equiv="content-type" content="text/html;charset=utf-8"/>
    <meta content="width=device-width, initial-scale=1, maximum-scale=1.0, minimum-scale=1.0" name="viewport">
    <style type="text/css">
        body { font-family: 'Microsoft Yahei', Verdana, arial, sans-serif; font-size: 14px; word-break: break-all; word-wrap: break-word; }
        .f_notice a { text-decoration: none; color: #174B73; }
        .f_notice a:hover { text-decoration: none; color: #FF6600; }
        .f_notice h2 { border-bottom: 1px solid #DDD; padding: 8px 0; font-size: 25px; }
        .f_notice .title { margin: 4px 0; color: #F60; font-weight: bold; }
        .f_notice .message, #trace { padding: 1em; border: solid 1px #000; margin: 10px 0; background: #FFD; line-height: 150%; }
        .f_notice .message { background: #FFD; color: #2E2E2E; border: 1px solid #E0E0E0; }
        #trace { background: #E7F7FF; border: 1px solid #E0E0E0; color: #535353; }
        .f_notice { padding: 10px; margin: 5px; color: #666; background: #FCFCFC; border: 1px solid #E0E0E0; }
    </style>
</head>
<body>
<div class="f_notice">
    <h2>系统发生错误 </h2>

    <div style="padding:20px;">您可以选择 [ <a href="javascript:location.reload();">重试</a> ] [ <a href="javascript:history.back()">返回</a> ] 或者 [ <a href="/">回到首页</a> ]</div>
    <p class="title">[ 错误信息 ]</p>

    <?php if ($exception_message): ?>
    <p class="message"><?php echo $exception_message; ?></p>
    <?php endif; ?>
    <?php if ( $exception_trace): ?>
        <p id="trace" class="message"><?php echo $exception_trace; ?></p>
    <?php endif; ?>
</div>
<div style="padding:20px;">Power by <a target="_blank" href="https://github.com/fanshengshuai/flib">flib</a>. <a href="http://www.kuboluo.com">fanshengshuai@gmail.com</a></div>
</body>
</html>
