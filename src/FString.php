<?php

class FString {
    public static function startWith($str, $needle) {
        return strpos($str, $needle) === 0;
    }

    public static function endWith($haystack, $needle) {

        $length = strlen($haystack);
        if ($length == 0) {
            return false;
        }

        $str = substr($haystack,$length-strlen($needle) );

        return ($str == $needle);
    }
}