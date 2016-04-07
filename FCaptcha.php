<?php

// 验证码类
class FCaptcha {
    private $charset = 'abcdefghkmnprstuvwxyzABCDEFGHKMNPRSTUVWXYZ23456789'; // 随机因子
    private $code; // 验证码
    private $codeLen = 4; // 验证码长度
    private $width = 130; // 宽度
    private $height = 50; // 高度
    private $img; // 图形资源句柄
    private $font; // 指定的字体
    private $fontSize = 20; // 指定字体大小
    private $fontColor; // 指定字体颜色

    // 构造方法初始化
    public function __construct() {
        $this->font = dirname(__FILE__) . '/font/airbus.ttf'; // 注意字体路径要写对，否则显示不了图片
    }

    public function setWidth($width) {
        $this->width = max(50, $width);
    }

    public function setHeight($height) {
        $this->height = max(20, $height);
    }

    public function setLength($len) {
        $this->codeLen = max(4, $len);
    }

    // 生成随机码
    private function createCode() {
        $_len = strlen($this->charset) - 1;
        for ($i = 0; $i < $this->codeLen; $i++) {
            $this->code .= $this->charset[mt_rand(0, $_len)];
        }

        FSession::set('captcha', strtolower($this->code));
    }

    // 生成背景
    private function createBg() {
        $this->img = imagecreatetruecolor($this->width, $this->height);
        $color = imagecolorallocate($this->img, mt_rand(157, 255), mt_rand(157, 255), mt_rand(157, 255));
        imagefilledrectangle($this->img, 0, $this->height, $this->width, 0, $color);
    }

    // 生成文字
    private function createFont() {
        $_x = $this->width / $this->codeLen;

        for ($i = 0; $i < $this->codeLen; $i++) {
            $this->fontSize = intval(rand(10, $this->height / 2));
            $this->fontColor = imagecolorallocate($this->img, mt_rand(0, 156), mt_rand(0, 156), mt_rand(0, 156));
            $left = $_x * $i + mt_rand(1, 2);
            imagettftext($this->img, $this->fontSize, mt_rand(-30, 30), $left, $this->height / 1.4, $this->fontColor, $this->font, $this->code[$i]);
        }
    }

    // 生成线条、雪花
    private function createLine() {

        // 线条
        for ($i = 0; $i < 6; $i++) {
            $color = imagecolorallocate($this->img, mt_rand(0, 156), mt_rand(0, 156), mt_rand(0, 156));
            imageline($this->img, mt_rand(0, $this->width), mt_rand(0, $this->height), mt_rand(0, $this->width), mt_rand(0, $this->height), $color);
        }
        // 雪花
        for ($i = 0; $i < 100; $i++) {
            $color = imagecolorallocate($this->img, mt_rand(200, 255), mt_rand(200, 255), mt_rand(200, 255));
            imagestring($this->img, mt_rand(1, 5), mt_rand(0, $this->width), mt_rand(0, $this->height), '*', $color);
        }
    }

    // 输出
    private function _output() {
        ob_clean();

        header('Content-type:image/png');
        imagepng($this->img);
        imagedestroy($this->img);
    }

    // 对外生成
    public function output() {
        $this->createBg();
        $this->createCode();
        $this->createLine();
        $this->createFont();
        $this->_output();
    }

    /**
     * 生成静态方法
     *
     * @param int $w   验证码的宽度
     * @param int $h   验证码的高度
     * @param int $len 验证码的字符数量
     */
    public static function genImg($w = 130, $h = 80, $len = 4) {
        $fCaptcha = new self;

        $fCaptcha->setWidth($w);
        $fCaptcha->setHeight($h);
        $fCaptcha->setLength($len);

        $fCaptcha->output();
    }

    /**
     * 检查验证码是否正确
     */
    public static function checkCaptcha($captcha) {
        return $captcha == FSession::get('captcha');
    }

    // 获取验证码
    public function getCode() {
        return strtolower($this->code);
    }
}