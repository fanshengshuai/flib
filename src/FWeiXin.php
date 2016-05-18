<?php

class FWeiXin {
    public static function create_native_url($productid) {

        $nativeObj["appid"] = WX_APP_ID;
        $nativeObj["productid"] = urlencode($productid);
        $nativeObj["timestamp"] = time();
        $nativeObj["noncestr"] = self::create_noncestr();
        $nativeObj["sign"] = self::get_biz_sign($nativeObj);
        $bizString = self::formatBizQueryParaMap($nativeObj, false);
        return "weixin://wxpay/bizpayurl?" . $bizString;
    }

    function create_noncestr($length = 16) {
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        $str = "";
        for ($i = 0; $i < $length; $i++) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return $str;
    }

    function create_bh($length = 5) {
        $chars = "0123456789";
        $str = "";
        for ($i = 0; $i < $length; $i++) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return $str;
    }

    function get_biz_sign($bizObj) {
        foreach ($bizObj as $k => $v) {
            $bizParameters[strtolower($k)] = $v;
        }
        if (WX_APP_KEY == "") {
            throw new Exception("APPKEY为空！" . "<br>");
        }
        $bizParameters["appkey"] = WX_APP_KEY;
        ksort($bizParameters);
        $bizString = self::formatBizQueryParaMap($bizParameters, false);
        return sha1($bizString);
    }

    function formatBizQueryParaMap($paraMap, $urlencode) {
        $buff = "";
        ksort($paraMap);
        foreach ($paraMap as $k => $v) {
            //	if (null != $v && "null" != $v && "sign" != $k) {
            if ($urlencode) {
                $v = urlencode($v);
            }
            $buff .= strtolower($k) . "=" . $v . "&";
            //}
        }
        $reqPar = '';
        if (strlen($buff) > 0) {
            $reqPar = substr($buff, 0, strlen($buff) - 1);
        }
        return $reqPar;
    }
}