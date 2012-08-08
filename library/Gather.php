<?php

class Gather {
    public static function getSubStr($contents, $from, $end = '') {

        $from_pos = strpos($contents, $from);
        if ($from_pos) {
            $from_pos += strlen($from);
            $_tmp = substr($contents, $from_pos);
        }

        if (!$end) {
            return $_tmp;
        }

        $end_pos = strpos($_tmp, $end);
        if ($end_pos) {
            $_tmp_1 = substr($_tmp, 0,  $end_pos);
        }

        return $_tmp_1;
    }
}
