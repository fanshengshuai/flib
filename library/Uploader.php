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

    public function setFileType($type) {
    }

    public function getFileType($file_name) {
        $ext_arr = array(
            'pic' => array('gif', 'jpg', 'jpeg', 'png', 'bmp'),
            'flash' => array('swf', 'flv'),
            'media' => array('swf', 'flv', 'mp3', 'wav', 'wma', 'wmv', 'mid', 'avi', 'mpg', 'asf', 'rm', 'rmvb'),
            'file' => array('doc', 'docx', 'xls', 'xlsx', 'ppt', 'htm', 'html', 'txt', 'zip', 'rar', 'gz', 'bz2'),
        );

        $temp_arr = explode(".", $file_name);
        $file_ext = array_pop($temp_arr);
        $file_ext = trim($file_ext);
        $file_ext = strtolower($file_ext);

        if (in_array($file_ext, $ext_arr['pic'])) {
            return 'pic';
        }
    }

    public static function saveFile($field) {
        $uploader = new Uploader;
        $photo_file = "image/" . date('Ymd') . '/' . date('YmdHis') . ".attach";
        $upload_file = $uploader->saveAttach('pic_url', $photo_file);
        return $upload_file['file_path'];
    }

    public function saveAttach($field, $obj=null) {

        if (!$obj) {
        }


        // 处理扩展名
        if (strpos($obj, '.attach')) {
            $file_ext = addslashes(strtolower(substr(strrchr($_FILES[$field]['type'], '/'), 1, 10)));

            if ($file_ext == 'jpeg') $file_ext = 'jpg';
            //echo $file_ext;exit;
            $obj = str_replace('.attach', '.' . $file_ext, $obj);
        }

        //echo $obj;

        $attach_url = $obj;

        if (strpos($obj, APP_ROOT) === false) {
            $obj = APP_ROOT . "public/attachs/" . $obj;
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

            $attachDAO->add($data);

            return $data;
        }

        return false;
    }
}
