<?php

class FDebug {
    public static function log() {

    }

    public static function logSql($sql, $param = null) {
        global $_F;

        $fLogger = new FLogger('sql');

        $sql_new = '';
        if (!count($param)) {
            $sql_new = $sql;
        } else {
            foreach ($param as $item) {
                if (is_string($item) || $item === null) {
                    $item = '\'' . $item . '\'';
                }

                $pos = strpos($sql, '?');
                $sql_new .= substr($sql, 0, $pos) . $item;
                $sql = substr($sql, $pos + 1);
            }

            $sql_new .= $sql;
        }

        $fLogger->append($sql_new);

        $_F['debug_info']['sql'][] = $sql_new;
    }
}