# flib
====

Flib (Fast Libraries for PHP) 是一个可以灵活使用和扩充的库。

起初，是为了开发方便，将好用得方法都集成到了一起，直到后来，文件越来越多，便将这些文件用面向对象的方法进行封装，并命名成 Flib 。

## 规范：

 * 逻辑代码和用户呈现分开，保证安全性。
 * 所有目录都小写，所有文件名首字母大写。
 * 所有程序文件采用 utf8 编码。
 * 程序缩进采用 4 个空格代替 Tab 。
 * PHP 文件以 <?php 开头，结尾不加 ?>
 * 方法名采用小驼峰式命名法，普通变量一般采用小写，多个单词用下划线线连接，实例一般采用小驼峰式命名法。
 * if while for 等按照 vim 默认缩进。

## DEMO 样例
 * demo 代码位于 demo 目录中
 * 将域名配置到 demo/public 目录
 * 修改 demo/public/index.php，将 flib 框架中的 Flib.php 包含过来。

    require_once APP_ROOT . "libraries/flib/Flib.php";
