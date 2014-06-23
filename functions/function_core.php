<?php
if (!function_exists('redirect')) {
    function redirect($url, $target = '') {
        global $_F;

        if ($target == 301) {
            header('HTTP/1.1 301 Moved Permanently'); // 301头部
            header('Location: ' . $url); // 跳转到新地址
            exit;
        }

        if ($url == 'r') {
            $url = $_SERVER ['HTTP_REFERER'];
        }

        if ($_F ['in_ajax']) {
            $c = new Controller ();
            $c->ajaxRedirect($url);
        } else {

            if ($target) {
                echo "<script> {$target}.location.href = '{$url}'; </script>";
            } else {
                header("location: " . $url);
            }
        }

        exit ();
    }
}

/**
 * 判读是否有 json_encode 方法
 */
if (!function_exists('json_encode')) {
    function json_encode($value) {
        $fJSON = new FJSON ();

        return $fJSON->encode($value);
    }
}

/**
 * 判读是否有 json_decode 方法
 */
if (!function_exists('json_decode')) {
    function json_decode($value) {
        $fJSON = new FJSON ();

        return $fJSON->decode($value);
    }
}

/**
 * 递归方式的对变量中的特殊字符进行转义
 *
 * @access public
 *
 * @param mix $value
 *
 * @return mix
 *
 */
function addslashes_deep($value) {
    if (empty ($value)) {
        return $value;
    } else {
        return is_array($value) ? array_map('addslashes_deep', $value) : addslashes($value);
    }
}

/**
 * 将对象成员变量或者数组的特殊字符进行转义
 *
 * @access public
 *
 * @param mix $obj
 *            对象或者数组
 *
 * @author Xuan Yan
 *
 * @return mix 对象或者数组
 */
function addslashes_deep_obj($obj) {
    if (is_object($obj) == true) {
        foreach ($obj as $key => $val) {
            $obj->$key = addslashes_deep($val);
        }
    } else {
        $obj = addslashes_deep($obj);
    }

    return $obj;
}

// 判断是否为手机访问
function is_mobile() {
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

if (!function_exists('D')) {
    function D($table) {
        return new FDB_Table ($table);
    }
}

function xml2arr($xml) {
    $values = array();
    $index = array();
    $array = array();
    $parser = xml_parser_create();
    xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
    xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
    xml_parse_into_struct($parser, $xml, $values, $index);
    xml_parser_free($parser);
    $i = 0;
    $name = $values [$i] ['tag'];
    $array [$name] = isset ($values [$i] ['attributes']) ? $values [$i] ['attributes'] : '';
    $array [$name] = _xml2arr($values, $i);

    return $array;
}

function _xml2arr($values, &$i) {
    $child = array();
    if (isset ($values [$i] ['value']))
        array_push($child, $values [$i] ['value']);
    while ($i++ < count($values)) {
        switch ($values [$i] ['type']) {
            case 'cdata' :
                array_push($child, $values [$i] ['value']);
                break;
            case 'complete' :
                $name = $values [$i] ['tag'];
                if (!empty ($name)) {
                    $child [$name] = !empty ($values [$i] ['value']) ? ($values [$i] ['value']) : '';
                    if (isset ($values [$i] ['attributes']))
                        $child [$name] = $values [$i] ['attributes'];
                }
                break;
            case 'open' :
                $name = $values [$i] ['tag'];
                $size = isset ($child [$name]) ? sizeof($child [$name]) : 0;
                $child [$name] [$size] = _xml2arr($values, $i);
                break;
            case 'close' :
                return $child;
                break;
        }
    }

    return $child;
}

/**
 * Returns the url query as associative array
 *
 * @param
 *            string query
 *
 * @return array params
 */
function urlQueryToArray($query, $url_encode = true) {
    if ($url_encode) {
        $query = urldecode($query);
    }

    $query = str_replace("&amp;", '&', $query);
    $queryParts = explode('&', $query);
    $params = array();
    foreach ($queryParts as $param) {
        $item = explode('=', $param);
        $params [$item [0]] = $item [1];
    }

    return $params;
}

function escape($str) {
    preg_match_all("/[\xc2-\xdf][\x80-\xbf]+|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}|[\x01-\x7f]+/e", $str, $r);
    // 匹配utf-8字符，
    $str = $r [0];
    $l = count($str);
    for ($i = 0; $i < $l; $i++) {
        $value = ord($str [$i] [0]);
        if ($value < 223) {
            $str [$i] = rawurlencode(utf8_decode($str [$i]));
            // 先将utf8编码转换为ISO-8859-1编码的单字节字符，urlencode单字节字符.
            // utf8_decode()的作用相当于iconv("UTF-8","CP1252",$v)。
        } else {
            // TODO linux 和windows不一样
            $_char_str = strtoupper(bin2hex(iconv("UTF-8", "UCS-2", $str [$i])));
            // echo $_char_str[1]."<br/>";
            if (PATH_SEPARATOR == ':') {
                $char_str = $_char_str [2] . $_char_str [3] . $_char_str [0] . $_char_str [1];
            } else {
                $char_str = $_char_str;
            }
            $str [$i] = "%u" . $char_str;
        }
    }

    return join("", $str);
}

function unescape($str) {
    $ret = '';
    $len = strlen($str);
    for ($i = 0; $i < $len; $i++) {
        if ($str [$i] == '%' && $str [$i + 1] == 'u') {
            $val = hexdec(substr($str, $i + 2, 4));
            if ($val < 0x7f)
                $ret .= chr($val);
            else if ($val < 0x800)
                $ret .= chr(0xc0 | ($val >> 6)) . chr(0x80 | ($val & 0x3f));
            else
                $ret .= chr(0xe0 | ($val >> 12)) . chr(0x80 | (($val >> 6) & 0x3f)) . chr(0x80 | ($val & 0x3f));
            $i += 5;
        } else if ($str [$i] == '%') {
            $ret .= urldecode(substr($str, $i, 3));
            $i += 2;
        } else
            $ret .= $str [$i];
    }

    return $ret;
}

/**
 * 获得视频链接地址
 *
 * @param unknown $v_info
 *
 * @return string
 */
function getVideoInfoUrl($v_info) {
    if ($v_info ['cat_pinyin'] && $v_info ['title_pinyin']) {
        $encode_vid = base64_encode($v_info['vid']);

        return "/{$v_info['cat_pinyin']}/v_{$v_info['title_pinyin']}-{$encode_vid}.html";
    } else {
        return "/view/{$v_info['vid']}.html";
    }
}

/**
 * 判断图片地址是否为远程地址
 *
 * @param unknown $pic_url
 *
 * @return unknown string
 */
function getPicUrl($pic_url) {
    if (strpos($pic_url, 'ttp:') != false) {
        return $pic_url;
    } else {
        return "http://s.fmscg.com/uploads/{$pic_url}";
    }
}