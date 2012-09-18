<?php

class Service_Setting {

    public static function get($keys) {

        if (is_string($keys)) {
            $single = true;
            return self::_getSingle($keys);
        }

        foreach ($keys as $key) {
            $conditions[] = "'{$key}'";
        }

        $condition_str = join(' , ', $conditions);
        $condition_str = "`k` in ({$condition_str})";

        $settingDAO = new DAO_Setting;
        $settings_data = $settingDAO->findAll($condition_str);

        if ($settings_data) {
            foreach ($settings_data as $item) {
                $settings[$item['k']] = json_decode($item['v'], true);
            }

            return $settings;
        } else {
            return false;
        }
    }

    public static function _getSingle($key) {

        $settingDAO = new DAO_Setting;
        $setting = $settingDAO->find("`k` = '{$key}'");

        if ($setting) {
            return json_decode($setting['v'], true);
        } else {
            return false;
        }
    }

    public static function set($key, $value) {

        $settingDAO = new DAO_Setting;
        $setting = $settingDAO->find("`k` = '{$key}'");

        $value = json_encode($value);

        if ($setting) {
            $settingDAO->update($key, array('v' => $value));
        } else {
            $settingDAO->add(array(
                'k' => $key,
                'v' => $value
            ));
        }

        return true;
    }
}
