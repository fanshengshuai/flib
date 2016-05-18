<?php

class FView_Orig {
    public function assign($var, $value) {
        global $_F;
        $_F['var'][$var] = $value;
    }

    public function display($tpl) {
        global $_F;
        if (isset($_F['var'])) extract($_F['var']);

        if ($tpl[0] == '/' || $tpl[1] == ':') {
            include($tpl);
            exit;
        } else
            $tplFile = F_APP_ROOT . 'tpl/' . $tpl;

        if (file_exists($tplFile)) include($tplFile);
        elseif (file_exists($tplFile . ".tpl.php")) include($tplFile . ".tpl.php");
        else echo("模版文件不存在!" . $tpl . ".tpl.php");
        exit;
    }
}