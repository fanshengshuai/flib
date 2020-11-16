<?php

class FSetting {
    public static $cache_file = 'data/cache_setting.php';

    public static function get($key) {
        global $_F;

        if (isset($_F['setting'][$key])) {
            return $_F['setting'][$key];
        }

        $setting_file = WEB_ROOT_DIR . self::$cache_file;
        if (!file_exists($setting_file)) {
            self::updateSystemCache();
        }

        $_F['setting'] = include_once(WEB_ROOT_DIR . self::$cache_file);

        return $_F['setting'][$key];
    }

    public static function updateSystemCache() {
        $setting_file = WEB_ROOT_DIR . self::$cache_file;

        $t = new FTable('setting');
        $settings = $t->select();

        $setting_write = array();
        foreach ($settings as $row) {
            $setting_write[$row['setting_name']] = $row['setting_value'];
        }

        FFile::save($setting_file, "<?php\n" . 'return ' . var_export($setting_write, true) . ';');
    }
}