<?php

/**
 * Class FResponse
 *
 * User: fanshengshuai
 * Date: 14-6-22
 * Time: 19:32
 *
 * vim: set expandtab sw=4 ts=4 sts=4
 */
class FRequest {

    /**
     * 是否为 POST
     * @return bool
     */
    public static function isPost() {
        return (isset($_SERVER['REQUEST_METHOD']) && strtoupper($_SERVER['REQUEST_METHOD']) == 'POST');
    }

    public static function getAllParams($key = null) {
        if ($key) {
            return $_GET[$key];
        }

        return $_GET;
    }

    public static function getAllPostParams($key = null) {
        if ($key) {
            return $_POST[$key];
        }

        return $_POST;
    }

    public static function getInt($param) {
        return intval($_GET[$param]);
    }

    public static function getString($param) {
        return trim($_GET[$param]);
    }

    public static function getPostString($param) {
        return $_POST[$param];
    }

    /**
     * 获得 Request 中的数据并转成 int
     *
     * @param $param string request 参数
     *
     * @return int Request 中的数据
     */
    public static function getRequestInt($param) {
        return intval($_REQUEST[$param]);
    }

    public static function getRequestString($param) {
        return $_REQUEST[$param];
    }

    public static function getPostInt($param) {
        return intval($_POST[$param]);
    }

    public static function getUploadedFiles() {

    }

    public static function getClientIP($type = 0) {
        static $ip = NULL;

        if ($ip !== NULL) return $ip[$type];

        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $arr = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $pos = array_search('unknown', $arr);
            if (false !== $pos) unset($arr[$pos]);
            $ip = trim($arr[0]);
        } elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (isset($_SERVER['REMOTE_ADDR'])) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }

        // IP地址合法验证
        $long = sprintf("%u", ip2long($ip));
        $ip = $long ? array($ip, $long) : array('0.0.0.0', 0);
        return $ip[$type];
    }


    // 判断是否为手机访问
    public static function isMobile() {
        $user_agent = $_SERVER ['HTTP_USER_AGENT'];
        $mobile_agents = Array(
            "240x320",
            "acer",
            "acoon",
            "acs-",
            "abacho",
            "ahong",
            "airness",
            "alcatel",
            "amoi",
            "android",
            "anywhereyougo.com",
            "applewebkit/525",
            "applewebkit/532",
            "asus",
            "audio",
            "au-mic",
            "avantogo",
            "becker",
            "benq",
            "bilbo",
            "bird",
            "blackberry",
            "blazer",
            "bleu",
            "cdm-",
            "compal",
            "coolpad",
            "danger",
            "dbtel",
            "dopod",
            "elaine",
            "eric",
            "etouch",
            "fly ",
            "fly_",
            "fly-",
            "go.web",
            "goodaccess",
            "gradiente",
            "grundig",
            "haier",
            "hedy",
            "hitachi",
            "htc",
            "huawei",
            "hutchison",
            "inno",
            "ipad",
            "ipaq",
            "ipod",
            "jbrowser",
            "kddi",
            "kgt",
            "kwc",
            "lenovo",
            "lg ",
            "lg2",
            "lg3",
            "lg4",
            "lg5",
            "lg7",
            "lg8",
            "lg9",
            "lg-",
            "lge-",
            "lge9",
            "longcos",
            "maemo",
            "mercator",
            "meridian",
            "micromax",
            "midp",
            "mini",
            "mitsu",
            "mmm",
            "mmp",
            "mobi",
            "mot-",
            "moto",
            "nec-",
            "netfront",
            "newgen",
            "nexian",
            "nf-browser",
            "nintendo",
            "nitro",
            "nokia",
            "nook",
            "novarra",
            "obigo",
            "palm",
            "panasonic",
            "pantech",
            "philips",
            "phone",
            "pg-",
            "playstation",
            "pocket",
            "pt-",
            "qc-",
            "qtek",
            "rover",
            "sagem",
            "sama",
            "samu",
            "sanyo",
            "samsung",
            "sch-",
            "scooter",
            "sec-",
            "sendo",
            "sgh-",
            "sharp",
            "siemens",
            "sie-",
            "softbank",
            "sony",
            "spice",
            "sprint",
            "spv",
            "symbian",
            "tablet",
            "talkabout",
            "tcl-",
            "teleca",
            "telit",
            "tianyu",
            "tim-",
            "toshiba",
            "tsm",
            "up.browser",
            "utec",
            "utstar",
            "verykool",
            "virgin",
            "vk-",
            "voda",
            "voxtel",
            "vx",
            "wap",
            "wellco",
            "wig browser",
            "wii",
            "windows ce",
            "wireless",
            "xda",
            "xde",
            "zte"
        );
        $is_mobile = false;
        foreach ($mobile_agents as $device) {
            if (stristr($user_agent, $device)) {
                $is_mobile = true;
                break;
            }
        }

        return $is_mobile;
    }
} 