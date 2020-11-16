<?php

class FView_Tiny
{
    protected $_tpl_parser;

    public function __construct()
    {
        global $_F;
        $tpl_path = F_APP_ROOT . 'modules/' . $_F['module'] . "/tpl/"; // . ($_F['module'] ? $_F['module'] . '/' : '');

        $this->tpl_parser = new FTemplate(array(
            'tpl_path_root' => $tpl_path,
            'template_c'    => 'data/template_c/',
        )
        );
    }

    public function assign($var, $value)
    {
        $this->tpl_parser->assign($var, $value);
    }

    public function fetch($tpl)
    {
        ob_clean();
        $this->display($tpl);
        echo ob_get_clean();
    }

    public function display($tpl)
    {
        global $_F;

        $this->tpl_parser->display($tpl);

        if ($_F['debug'] && !$_F['in_ajax']) {
            echo FView::getDebugInfo();
        }
    }
}
