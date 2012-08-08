<?php

/**
 *
 * 作者: 范圣帅(fanshengshuai@gmail.com)
 *
 * 创建: 2012-07-27 21:26:16
 * vim: set expandtab sw=4 ts=4 sts=4 * 
 *
 * $Id: Uploader.php 92 2012-07-31 08:08:22Z fanshengshuai $
 */
class Uploader {

    public function saveAttach($field, $obj) {

        // 处理扩展名
        if (strpos($obj, '.attach')) {
            $file_ext = addslashes(strtolower(substr(strrchr($_FILES[$field]['type'], '/'), 1, 10)));
            //echo $file_ext;exit;
            $obj = str_replace('.attach', '.' . $file_ext, $obj);
        }

        $attach_url = $obj;

        if (strpos($obj, APP_ROOT) === false) {
            $obj = APP_ROOT . "www/attachs/" . $obj;
        }

        $attach_dir = dirname($obj);


        if (!is_dir($attach_dir)) {
            mkdir($attach_dir, 0777, true);
        }


        if ($_FILES[$field]) {
            move_uploaded_file($_FILES[$field]['tmp_name'], $obj);

            $attachDAO = new DAO_Attach;
            $data = array(
                'file_name' => $_FILES[$field]['name'],
                'file_type' => $_FILES[$field]['type'],
                'file_size' => $_FILES[$field]['size'],
                'file_path' => $attach_url,
            );

            return $attachDAO->add($data);
        }

        return false;
    }
}
