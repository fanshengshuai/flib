<?php

/**
 * Class FHttp http 操作
 */
class FHttp {

    /**
     * 下载网络文件
     *
     * @param $http_file 网络文件地址
     * @param $save_path 保存路径
     *
     * @return object
     */
    public function download($http_file, $save_path = null) {
        $http_file_raw = file_get_contents($http_file);

        if ($save_path) {
            file_put_contents($save_path, $http_file_raw);
        } else {
            return $http_file_raw;
        }

        return true;
    }
}