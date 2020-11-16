<?php

class FPlugin
{
    /**
     * @var $this
     */
    protected static $__instance;

    /**
     * @return $this
     */
    public static function getInstance()
    {
        if (self::$__instance === null) {
            self::$__instance = new self;
        }

        return self::$__instance;
    }

    public function adminModifyFormBefore()
    {

    }

    public function adminModifyFormAfter()
    {
    }

    public function adminListPageTop()
    {
    }


    /**
     * @param String $plugin
     * @return $this
     */
    public static function load($plugin)
    {
        global $_F;
        $plugin_file = F_APP_ROOT . "addons/plugin/{$plugin}Plugin.php";
        if (file_exists($plugin_file)) {
            if (@include_once($plugin_file)) {
                $class = ucfirst($plugin) . 'Plugin';
                if (class_exists($class))
                    return new $class;
            }
        }

        return new self;
    }

    public function loadModelPlugin($modelData)
    {
        global $_F;
        $model = $modelData['table_name'];

        if ($_F['debug']) {
            echo 'model: ' . $model;
        }

        $plugin_file = F_APP_ROOT . "plugin/{$model}.plugin.php";
        if (file_exists($plugin_file)) {
            include_once $plugin_file;

            $class = ucfirst($model) . 'Plugin';
            $_F['plugins'][$model] = new $class;
            if (method_exists($_F['plugins'][$model], 'init')) {
                $_F['plugins'][$model]->init($modelData);
            }
        }
    }

    function getModelPluginTpl($plugin_id, $modelData)
    {
        global $_F;
        $model = $modelData['table_name'];

        if (!$_F['plugins'][$model]) return;

        $func = $plugin_id;
        if ($_F['debug']) {
            echo $plugin_id;
        }

        if (method_exists($_F['plugins'][$model], $func)) {
            $_F['plugins'][$model]->$func();
        }
    }
}