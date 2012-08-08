<?php

function redirect($url, $target = '') {
    global $_G;

    if ($_G['in_ajax']) {
        $c = new Controller;
        $c->ajaxRedirect($url);
    } else {

        if ($target) {
            echo "<script> {$target}.location.href = '{$url}'; </script>";
        } else {
            header("location: " . $url);
        }
    }

    exit;
}
