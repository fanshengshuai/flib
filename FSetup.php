<?php
class FSetup {
    public static function getInstance() {
        return new FSetup;
    }

    public function init() {
        if ($_SERVER['REQUEST_URI']) {
            exit("please run_in shell");
        }

        $this->mkdir("modules/front/controllers");
        $this->mkdir("modules/front/tpl");
        $this->mkdir("res/js");
        $this->mkdir("res/img");
        $this->mkdir("res/css");
        $this->mkdir("config");
    }

    public function mkdir($dir) {
        echo "创建目录：" . $dir . "\n";
        FFile:mkdir($dir);
    }
}
