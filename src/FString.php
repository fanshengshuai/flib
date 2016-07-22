<?php

class FString {
    public static function startWith($str, $needle) {
        return strpos($str, $needle) === 0;
    }

    public static function endWith($haystack, $needle) {

        $length = strlen($needle);
        if ($length == 0) {
            return false;
        }

        return ($haystack[$length - 1] === $needle);
    }
}