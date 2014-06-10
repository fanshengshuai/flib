<?php


/**
 * Class FResponse
 *
 * 作者: 范圣帅(fanshengshuai@gmail.com)
 * 时间: 2014-05-10 01:19:41
 *
 * vim: set expandtab sw=4 ts=4 sts=4
 */
class FResponse {

    /**
     * header
     * @var array
     */
    protected $header = array();

    /**
     * 设字符集，如果设置过 Content-type 为 json, 返回false
     *
     * @param string $encoding
     *
     * @return bool
     */
    public function setCharacterEncoding($encoding = 'utf-8') {

        // json 不设编码
        if ($this->header['Content-type'] == 'application/json') return false;

        $this->setHeader('Content-type', 'text/html; charset=' . $encoding);
        return true;
    }


    /**
     * 设置 header
     *
     * @param $headerKey
     * @param $headerValue
     */
    public function setHeader($headerKey, $headerValue) {
        $this->header[$headerKey] = $headerValue;
    }

    public function setContentType($contentType) {

        if ($contentType == 'json') {
            $this->setHeader('Content-type', 'application/json');
        }
    }

    /**
     * 文本输出内容
     *
     * @param $content string 内容
     */
    public function write($content) {
        ob_clean();

        foreach ($this->header as $h_key => $h_value) {
            header("{$h_key}: $h_value");
        }

        echo $content;
    }

    /**
     * 输出内容，可以是数组，可以是文本
     *
     * @param $mix
     */
    public static function output($mix) {
        global $_F;

        $response = new self;

        if (is_array($mix)) {
            $response->setContentType('json');
            $mix['debug_info'] = $_F['debug_info'];
            $response->write(json_encode($mix));;
        } elseif (is_string($mix)) {
            $response->write($mix);
        }

        return;
    }
}