<!doctype html>
<html lang="zh-CN" dir="ltr">

<head>
    <meta charset="utf-8">
    <title>500 - Flib Exception</title>
    <style>
        body {
            bottom: 0;
            left: 0;
            position: absolute;
            right: 0;
            top: 0;
            color: #999999;
            font: 14px/20px Arial, Helvetica, sans-serif;
        }

        h1, h2, h3, h4, h5, h6 {
            text-transform: uppercase;
            font-weight: normal;
        }

        h1, h2 {
            color: #707070;
        }

        h2 {
            font-size: 27px;
            line-height: 27px;
        }

        .center {
            left: 50%;
            position: absolute;
            text-align: center;
            top: 30%;
        }

        .error {
            font-size: 200px;
            font-weight: bold;
            line-height: 200px;
            margin: 0;
        }

        .title {
            margin: 20px 0 0;
        }

        .message {
            width: 400px;
            margin: 20px auto 0;
        }

        /* Page Defaults
        ----------------------------------------------------------------------------------------------------*/

        .center {
            width: 800px;
            margin-left: -400px;
        }

        .error {
            text-shadow: 0 -1px 0 rgba(0, 0, 0, 0.9), 0 1px 0 rgba(255, 255, 255, 0.7);
        }

        .error > span {
            display: inline-block;
            position: relative;
        }

        .error > span:before {
            content: "";
            position: absolute;
            top: 70%;
            left: -30px;
            right: -30px;
            height: 80px;
            /*background: url(error.png) no-repeat;*/
            background-size: 100% 100%;
        }

        .message {
            width: 400px;
        }

        /* Browser */
        .error-browser .message {
            width: 500px;
        }
    </style>
    <!--[if IE 6]>
    <style>


        body {
            height: 100%;
            width: 100%
        }

        .error, .error span {
            zoom: 1
        }
    </style><![endif]-->
</head>

<body id="page" class="page">

<div class="center error-404">

    <h1 class="error">
        <span>500</span>
    </h1>

    <h2 class="title">Flib Exception</h2>

    <p class="message">服务器内部错误，请稍后访问</p>
</div>

</body>
</html>