<?php

class FView_Orig {
    public function assign($var, $value) {
        global $_F;
        $_F['var'][$var] = $value;
    }

    public function display($tpl) {
        global $_F;
        extract($_F['var']);
        include(F_APP_ROOT . 'tpl/' . $tpl . ".tpl.php");
    }
}