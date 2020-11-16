<?php
/**
 * FLoader
 */
class FLoader  
{
    /**
     * @param string $plugin  插件名字
     * @return ucfirst($plugin).'Plugin'
     */
    public static function loadPlugin($plugin)
    {
        $file = F_APP_ROOT . "plugin/".$plugin.".plugin.php";
        if (file_exists($file)) {

            require_once $file;
            $class = ucfirst($plugin).'Plugin';
            return new $class;
        } else {
            return false;
        }
    }
}
